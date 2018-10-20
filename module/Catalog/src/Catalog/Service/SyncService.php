<?php

namespace Catalog\Service;

use Application\Model\Module;
use Catalog\Model\Order;
use Catalog\Model\Product;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature as StaticDbAdapter;
use Aptero\Service\AbstractService;
use Aptero\String\Translit;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter as DbAdapter;

class SyncService extends AbstractService
{
    protected $tables = array(
        'catalog'   => 'catalog',
        'brands'    => 'products_brands',
        'products'  => 'products',
        'orders'    => 'orders',
        'cart'      => 'orders_cart',
    );

    /**
     * @var DbAdapter
     */
    protected $adapter;

    /**
     * @var Sql
     */
    protected $sql;

    /**
     * @var bool
     */
    protected $isChanges;

    public function getProductStock($productId)
    {
        $data = [
            'size'  => [],
            'price' => [],
            'stock' => [],
        ];

        $product = new Product();
        $product->setId($productId);

        if(!$product->load()) {
            return false;
        }

        foreach ($product->getPlugin('size') as $taste) {
            $data['size'][] = [
                'id'     => $taste->getId(),
                'name'   => $taste->get('name'),
                'price'  => $taste->get('price'),
                'weight' => $taste->get('weight'),
            ];
        }

        foreach ($product->getPlugin('taste') as $taste) {
            $data['price'][] = [
                'id'          => $taste->getId(),
                'name'        => $taste->get('name'),
                'coefficient' => $taste->get('coefficient'),
            ];
        }

        $select = $this->getSql()->select()
            ->from(['t' => 'products_stock'])
            ->columns(['count', 'size_id', 'taste_id'])
            ->join(['s' => 'products_size'],  't.size_id = s.id', ['size' => 'name'])
            ->join(['p' => 'products_taste'], 't.taste_id = p.id', ['price' => 'name'])
            ->where(['product_id' => $product->getId()]);

        foreach ($this->execute($select) as $row) {
            $data['stock'][] = $row;
        }

        return $data;
    }

    static protected $updateTime;
    static public function getUpdateTime()
    {
        if(!self::$updateTime) {
            $module = new Module();
            $settings = $module->setModuleName('Catalog')->setSectionName('Products')->getPlugin('settings');
            self::$updateTime = $settings->get('update_time');
        }

        return self::$updateTime;
    }

    static public function getUpdateTime2()
    {
        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', self::getUpdateTime());
        return $dt->format('d.m.Y H:i');
    }

    public function updateTime()
    {
        $module = new Module();
        $settings = $module->setModuleName('Catalog')->setSectionName('Products')->getPlugin('settings');
        $settings->set('update_time', date("Y-m-d H:i:s"))->save();;
    }

    public function ordersXml()
    {
        $rootXML = new \SimpleXMLElement("<КоммерческаяИнформация></КоммерческаяИнформация>");
        $rootXML->addAttribute('ВерсияСхемы', '2.08');
        $rootXML->addAttribute('ДатаФормирования', date('Y-m-d'));

        $orders = Order::getEntityCollection();

        $orders->select()->where(array('sync' => 0));

        if(!$orders->load()->count()) {
            return $rootXML->asXML();
        }

        $delivery = array(
            'delivery' => 'Доставка',
            'pickup'   => 'Самовывоз',
        );

        $payment = array(
            'office'   => 'Оплата в офисе',
            'online'   => 'Online оплата',
        );

        foreach($orders as $order) {

            $comment = '';
            if($order->get('delivery')) {
                $comment .= '+ доставка ' . $order->get('delivery') . ' руб.';
            }

            $username = str_replace(array('»', '«', '&raquo;', '&laquo;'), '"', $order->getPlugin('attrs')->get('username'));
            $surname  = str_replace(array('»', '«', '&raquo;', '&laquo;'), '"', $order->getPlugin('attrs')->get('surname'));
            $address  = str_replace(array('»', '«', '&raquo;', '&laquo;'), '"', $order->getPlugin('attrs')->get('address'));

            $orderXML = $rootXML->addChild('Документ');
            $orderXML->addChild('Ид', $order->getId());
            $orderXML->addChild('Номер', $order->getId());
            $orderXML->addChild('ХозОперация', 'Заказ товара');
            $orderXML->addChild('Валюта', 'руб');
            $orderXML->addChild('Курс', '1');
            $orderXML->addChild('Сумма', $order->get('price'));

            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $order->get('time_create'));

            $orderXML->addChild('Дата', $date->format('Y-m-d'));
            $orderXML->addChild('Комментарий', $comment);

            $clientXML = $orderXML->addChild('Контрагенты')->addChild('Контрагент');
            $clientXML->addChild('Ид', $order->get('user_id'));
            $clientXML->addChild('Роль', 'Покупатель');
            $clientXML->addChild('Имя', $username);
            $clientXML->addChild('Фамилия', $surname);
            $clientXML->addChild('ПолноеНаименование', $username);
            $clientXML->addChild('Наименование', $username);

            $clientAddressXML = $clientXML->addChild('АдресРегистрации');
            $clientAddressXML->addChild('Представление', $address);

            if($order->get('delivery')) {
                $point = new \CatalogAdmin\Model\DeliveryPoint();
                $point->setId($order->getPlugin('attrs')->get('point'));
                $clientXML->addChild('Город', trim($point->get('name')));
            }

            $clientContactsXML = $clientXML->addChild('Контакты');

            if($order['phone']) {
                $clientContactXML = $clientContactsXML->addChild('Контакт');
                $clientContactXML->addChild('Тип', 'Телефон рабочий');
                $clientContactXML->addChild('Значение', $order->getPlugin('attrs')->get('phone'));
            }

            if($order['email']) {
                $clientContactXML = $clientContactsXML->addChild('Контакт');
                $clientContactXML->addChild('Тип', 'Почта');
                $clientContactXML->addChild('Значение', $order->getPlugin('attrs')->get('email'));
            }

            $addressFieldXML = $clientAddressXML->addChild('АдресноеПоле');
            $addressFieldXML->addChild('Тип', 'Адрес');
            $addressFieldXML->addChild('Значение', $address);

            $clientAgentXML = $clientXML->addChild('Представители')->addChild('Представитель')->addChild('Контрагент');

            $clientAgentXML->addChild('Отношение', 'Контактное лицо');
            $clientAgentXML->addChild('Наименование', $username);
            $clientAgentXML->addChild('Ид', 'b342955a9185c40132d4c1df6b30af2f');

            $orderXML->addChild('Время', $date->format('H:i:s'));

            $productsXML = $orderXML->addChild('Товары');

            foreach($order->getPlugin('cart') as $cartRow) {
                $product = $cartRow->getPlugin('product');
                $productXML = $productsXML->addChild('Товар');

                $productXML->addChild('Ид', $product->get('sync_id'));
                $productXML->addChild('ИдКаталога', $product->get('sync_id'));
                $productXML->addChild('Наименование', $product->get('name'));
                $productXML->addChild('ЦенаЗаЕдиницу', $cartRow->get('price'));
                $productXML->addChild('Количество', $cartRow->get('count'));
                $productXML->addChild('Сумма', $cartRow->get('price') * $cartRow->get('count'));

                $productPropsXML = $productXML->addChild('ЗначенияРеквизитов');
                $productPropertyXML = $productPropsXML->addChild('ЗначениеРеквизита');
                $productPropertyXML->addChild('Наименование', 'ВидНоменклатуры');
                $productPropertyXML->addChild('Значение', 'Товар');

                $productPropertyXML = $productPropsXML->addChild('ЗначениеРеквизита');
                $productPropertyXML->addChild('Наименование', 'ТипНоменклатуры');
                $productPropertyXML->addChild('Значение', 'Товар');
            }

            $orderPropertiesXML = $orderXML->addChild('ЗначенияРеквизитов');
            $propertyXML = $orderPropertiesXML->addChild('ЗначениеРеквизита');
            $propertyXML->addChild('Наименование', 'Метод оплаты');
            $propertyXML->addChild('Значение', $payment[$order->getPlugin('attrs')->get('payment')]);

            $propertyXML = $orderPropertiesXML->addChild('ЗначениеРеквизита');
            $propertyXML->addChild('Наименование', 'Заказ оплачен');
            $propertyXML->addChild('Значение', ($order->get('paid') ? 'true' : 'false'));

            $propertyXML = $orderPropertiesXML->addChild('ЗначениеРеквизита');
            $propertyXML->addChild('Наименование', 'Отменен');
            $propertyXML->addChild('Значение', ($order['status'] == 20 ? 'true' : 'false'));

            $propertyXML = $orderPropertiesXML->addChild('ЗначениеРеквизита');
            $propertyXML->addChild('Наименование', 'Финальный статус');
            $propertyXML->addChild('Значение', ($order['status'] == 15 ? 'true' : 'false'));

            $propertyXML = $orderPropertiesXML->addChild('ЗначениеРеквизита');
            $propertyXML->addChild('Наименование', 'Статус заказа');
            $propertyXML->addChild('Значение', Order::$processStatuses[$order->get('status')]);

            $propertyXML = $orderPropertiesXML->addChild('ЗначениеРеквизита');
            $propertyXML->addChild('Наименование', 'Дата изменения статуса');
            $propertyXML->addChild('Значение', $order->get('time_update'));

            //$order->set('sync', 1)->save();
        }
        //header("Content-type: text/xml; charset=utf-8");die($rootXML->asXML());

        return $rootXML->asXML();
    }

    public function ordersParser($file = '')
    {

    }

    public function importParser($file = '')
    {
        if (!file_exists($file)) {
            return false;
        }

        $sXml = simplexml_load_string(stripslashes(file_get_contents($file)));

        if((string) $sXml->Каталог['СодержитТолькоИзменения'] == 'false') {
            $this->isChanges = false;
        }

        //$this->addClassifier($sXml->Классификатор->Свойства->Свойство);
        $this->generateCatalog($sXml->Классификатор->Группы->Группа);
        $this->generateBrands($sXml->Классификатор->Производители->Производитель);
        $this->generateProducts($sXml->Каталог->Товары->Товар);

        return true;
    }

    protected $properties = array();

    public function addClassifier($properties)
    {
        if(isset($properties)) {
            foreach ($properties as $property) {
                if(!empty($property->ВариантыЗначений)) {
                    foreach($property->ВариантыЗначений->Справочник as $value) {
                        $this->properties[(string) $value->ИдЗначения] = (string) $value->Значение;
                    }
                }
            }
        }
    }

    protected $brands = array();
    public function generateBrands($brands)
    {
        $this->adapter->query('TRUNCATE TABLE ' . $this->tables['brands']);

        if(isset($brands)) {
            foreach ($brands as $brand) {
                $syncId = (string) $brand->Ид;

                //Check exists
                $select = $this->sql->select()
                    ->from(array('t' => $this->tables['brands']))
                    ->columns(array('id'))
                    ->where(array('sync_id' => $syncId));

                if($result = $this->execute($select)->current()) {
                    $brandId = $result['id'];
                } else {
                    $brandId = 0;
                }

                if(!$brandId) {
                    $insert = $this->sql->insert();

                    $url = Translit::url((string) $brand->Наименование, true);

                    $data = array(
                        'name'     => (string) $brand->Наименование,
                        'url'      => $url,
                        'sync_id'  => $syncId,
                    );

                    $insert->into($this->tables['brands'])
                        ->columns(array_keys($data))
                        ->values($data);

                    $this->execute($insert);

                    $brandId = $this->adapter->getDriver()->getLastGeneratedValue();
                }

                $this->brands[$syncId] = array(
                    //'name'  => (string) $brand->Наименование,
                    'id'    => $brandId,
                );
            }
        }
    }

    protected $catalog = array();
    public function generateCatalog($catalog, $parentId = 0, $urlPath = '')
    {
        //$this->adapter->query('TRUNCATE TABLE ' . $this->tables['catalog']);

        foreach ($catalog as $category)
        {
            $syncId = (string) $category->Ид;

            //Check exists
            $select = $this->sql->select()
                ->from(array('c' => $this->tables['catalog']))
                ->columns(array('id'))
                ->where(array('sync_id' => $syncId));

            if($result = $this->execute($select)->current()) {
                $catalogId = $result['id'];
            } else {
                $catalogId = 0;
            }

            $url = Translit::url((string) $category->Наименование, true);

            $cUrlPath = $urlPath . $url;

            $data = array(
                'name'      => trim($category->Наименование),
                'parent'    => $parentId,
            );

            if(!$catalogId) {
                $data = array_merge($data, array(
                    'sync_id'   => $syncId,
                    'url'       => $url,
                    'url_path'  => $cUrlPath,
                    'active'    => 1,
                ));
            }

            if($catalogId) {
                $update = $this->sql->update();

                $update->table($this->tables['catalog'])
                    ->set($data)
                    ->where(array('sync_id' => $syncId));

                $this->execute($update);
            } else {
                $insert = $this->sql->insert();

                $insert->into($this->tables['catalog'])
                    ->columns(array_keys($data))
                    ->values($data);

                $this->execute($insert);

                $catalogId = $this->adapter->getDriver()->getLastGeneratedValue();
            }

            if(isset($category->Группы)) {
                $this->generateCatalog($category->Группы->Группа, $catalogId, $cUrlPath . '/');
            }

            $this->catalog[$syncId] = array(
                //'name'  => (string) $category->Наименование,
                'id'    => $catalogId,
            );
        }
    }

    public function generateProducts($products)
    {
        $this->adapter->query('TRUNCATE TABLE ' . $this->tables['products']);

        foreach ($products as $product)
        {
            $syncId = (string) $product->Ид;

            //Check exists
            $select = $this->sql->select()
                ->from(array('p' => $this->tables['products']))
                ->columns(array('id'))
                ->where(array('sync_id' => $syncId));

            if($result = $this->execute($select)->current()) {
                $productId = $result['id'];
            } else {
                $productId = 0;
            }

            $url = Translit::url((string) $product->Наименование, true);


            $data = array(
                'name'        => trim($product->Наименование),
                'tags'        => trim($product->КлючевыеСлова),
                'article'     => trim($product->Артикул),
                'text'        => nl2br((string) $product->Описание),
            );

            if(isset($this->brands[(string) $product->Производители->Ид])) {
                $data['brand_id'] = $this->brands[(string) $product->Производители->Ид]['id'];
            }

            if(isset($this->catalog[(string) $product->Группы->Ид])) {
                $data['catalog_id'] = $this->catalog[(string) $product->Группы->Ид]['id'];
            }

            if(!$productId) {
                $data = array_merge($data, array(
                    'sync_id'   => $syncId,
                    'url'       => $url,
                    'active'    => 1,
                ));
            }

            if($productId) {
                $update = $this->sql->update();

                $update->table($this->tables['products'])
                    ->set($data)
                    ->where(array('sync_id' => $syncId));

                $this->execute($update);
            } else {
                $insert = $this->sql->insert();

                $insert->into($this->tables['products'])
                    ->columns(array_keys($data))
                    ->values($data);

                $this->execute($insert);

                $productId = $this->adapter->getDriver()->getLastGeneratedValue();
            }
        }
    }

    public function offersParser($file = '')
    {
        if (!file_exists($file)) {
            return false;
        }

        $sXml = simplexml_load_string(stripslashes(file_get_contents($file)));

        if((string) $sXml->Каталог['СодержитТолькоИзменения'] == 'false') {
            $this->isChanges = false;
        }

        $this->generatePrice($sXml->ПакетПредложений->Предложения->Предложение);

        return true;
    }

    public function generatePrice($products)
    {
        $this->adapter->query('TRUNCATE TABLE ' . $this->tables['products']);

        foreach ($products as $product)
        {
            $syncId = (string) $product->Ид;

            //Check exists
            $select = $this->sql->select()
                ->from(array('p' => $this->tables['products']))
                ->columns(array('id'))
                ->where(array('sync_id' => $syncId));

            if($result = $this->execute($select)->current()) {
                $productId = $result['id'];
            } else {
                continue;
            }


            if(!count($product->Цены->Цена)) {
                continue;
            }

            $data = array(
                'count' => $product->Количество
            );

            foreach($product->Цены->Цена as $price) {
                if((string) $price->ИдТипаЦены == '11bd9731-4ebd-11e3-8632-50e549c4019a') {
                    $data['price_opt'] = $price->ЦенаЗаЕдиницу;
                }

                if((string) $price->ИдТипаЦены == 'c2aa2e66-93b7-11e3-8c70-50e549c4019a') {
                    $data['price'] = $price->ЦенаЗаЕдиницу;
                }
            }

            if(empty($data)) {
                continue;
            }

            $update = $this->sql->update();

            $update->table($this->tables['products'])
                ->set($data)
                ->where(array('sync_id' => $syncId));

            $this->execute($update);
        }

        $this->updateTime();
    }
}