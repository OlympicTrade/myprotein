<?php
namespace CatalogAdmin\Service;

use Aptero\Mail\Mail;
use Aptero\Service\Admin\TableService;
use CallcenterAdmin\Model\Call;
use Catalog\Service\ProductsService;
use CatalogAdmin\Model\Cart;
use CatalogAdmin\Model\Requests;
use CatalogAdmin\Model\Supplies;
use CatalogAdmin\Model\SuppliesCart;
use ManagerAdmin\Model\Task;
use Zend\Db\Sql\Expression;

class SuppliesService extends TableService
{
    public function getProductsRequested()
    {
        $selectSupplies =
            $this->getSql()->select()
                ->from(['sp' => 'supplies_products'])
                ->columns(['count' => new Expression('SUM(sp.count)')])
                ->join(['s' => 'supplies'], 's.id = sp.supply_id', [])
                ->where([
                    'sp.product_id = or.product_id',
                    'sp.taste_id = or.taste_id',
                    'sp.size_id = or.price_id',
                    's.status' => [Supplies::STATUS_NEW],
                ]);

        $selectStock =
            $this->getSql()->select()
                ->from(['ps' => 'products_stock'])
                ->columns(['count' => new Expression('SUM(ps.count)')])
                ->where([
                    'ps.product_id = or.product_id',
                    'ps.taste_id = or.taste_id',
                    'ps.size_id = or.price_id',
                ]);

        $select = $this->getSql()->select()
            ->from(['or' => 'orders_request'])
            ->columns([
                'product_id',
                'price_id',
                'taste_id',
                'limit' => new Expression('COUNT(or.id)'),
                'products_stock' => $selectStock,
                'supplies_stock' => $selectSupplies,
            ])
            ->where(['or.status' => 0])
            ->group(['or.product_id', 'or.price_id', 'or.taste_id']);

        $requests = new Requests();
        $requests->addProperty('supplies_stock', ['virtual' => true]);
        $requests->addProperty('products_stock', ['virtual' => true]);
        $requests->addProperty('limit', ['virtual' => true]);

        $requests = $requests->getCollection();
        $requests->setSelect($select);
       
        return $requests;
    }

    public function getProductsLack()
    {
        $selectSupplies =
            $this->getSql()->select()
                ->from(['sp' => 'supplies_products'])
                ->columns(['count' => new Expression('SUM(sp.count)')])
                ->join(['s' => 'supplies'], 's.id = sp.supply_id', [])
                ->where([
                    'sp.product_id = psl.product_id',
                    'sp.taste_id = psl.taste_id',
                    'sp.size_id = psl.size_id',
                    's.status' => [Supplies::STATUS_NEW],
                ]);

        $selectStock =
            $this->getSql()->select()
                ->from(['ps' => 'products_stock'])
                ->columns(['count' => new Expression('SUM(ps.count)')])
                ->where([
                    'ps.product_id = psl.product_id',
                    'ps.taste_id = psl.taste_id',
                    'ps.size_id = psl.size_id',
                ]);

        $select = $this->getSql()->select()
            ->from(['psl' => 'products_stock_limit'])
            ->columns([
                'product_id',
                'size_id',
                'taste_id',
                'limit' => new Expression('SUM(psl.count)'),
                'products_stock' => $selectStock,
                'supplies_stock' => $selectSupplies,
            ])
            ->join(['p' => 'products'], 'p.id = psl.product_id', [])
            ->group(['psl.product_id', 'psl.size_id', 'psl.taste_id'])
            ->order('p.sort DESC');

        $cart = new Cart();

        $cart->addProperties([
            'products_stock'  => [],
            'supplies_stock'  => [],
            'stock'           => [],
            'limit'           => [],
        ]);

        $cart->addPropertyFilterOut('stock', function($model, $value) {
            return (int) $model->get('products_stock') + $model->get('supplies_stock');
        });

        if(isset($_GET['margin'])) {
            switch($_GET['margin']) {
                case 'green':
                    $select->where->between('p.margin', 1, 35);
                    break;
                case 'yellow':
                    $select->where->between('p.margin', 35, 45);
                    break;
                case 'red':
                    $select->where->greaterThan('p.margin', 45);
                    break;
                case 'black':
                    $select->where->equalTo('p.margin', 0);
                    break;
            }
        }

        $result = $cart->getCollection()->setSelect($select);

        return $result;
    }

    public function getWeightStatistic()
    {
        $select = $this->getSql()->select()
            ->from(array('t' => 'supplies'))
            ->columns(array('user_id', 'weight' => new Expression('SUM(weight)')))
            ->group('user_id');

        $dt = new \DateTime();

        $select->where
            ->lessThan('date', $dt->format('Y-m') . '-25')
            ->greaterThanOrEqualTo('date', $dt->modify('-1 month')->format('Y-m') . '-19');

        $result = $this->execute($select);

        return $result;
    }

    /**
     * @param $data
     * @param ProductsService $publicProdService
     * @return array|bool
     */
    public function addToCart($data, $publicProdService)
    {
        $product = $publicProdService->getProduct([
            'name'      => $data['product'],
            'size_id'   => $data['sizeId'],
            'taste_id'  => $data['tasteId'],
        ]);

        if(!$product->load()) {
            return false;
        }

        $cart = new SuppliesCart();
        $cart->setVariables([
            'supply_id'  => $data['supplyId'],
            'product_id' => $product->getId(),
            'size_id'    => $data['sizeId'],
            'taste_id'   => $data['tasteId'],
            'count'      => $data['count'],
            'order_count'=> $data['count'],
            'price'      => $data['price'],
        ]);

        $cart->save();

        $this->updateSupplyData($data['supplyId']);

        return array(
            'cart'  => $cart,
        );
    }
    
    public function updateCartPrice($data)
    {
        $cart = new SuppliesCart();
        $cart->setId($data['cartId']);
        $price = (double) $data['price'];

        if(!$cart->load()) {
            return false;
        }

        $cart->set('price', $price)->save();

        $supply = $this->updateSupplyData($cart->get('supply_id'));

        return [
            'price' => $supply->get('price')
        ];
    }
    
    public function updateCartCount($data)
    {
        $count = (int) $data['count'];

        $cart = new SuppliesCart();
        $cart->setId($data['cartId']);

        if(!$cart->load()) {
            return false;
        }

        if($count < 0) {
            $cart->remove();
            return [];
        }

        $countDiff = $cart->get('count') - $count;
        $stock = $this->getPublicOrderService()->changeProductCount($cart->get('product_id'), $cart->get('taste_id'), $cart->get('size_id'), $countDiff);

        $cart->set('count', $count);
        $cart->save();

        $this->updateSupplyData($cart->get('supply_id'));

        return [
            //'cart'   => $cart,
            'stock'  => $stock['stock'],
        ];
    }

    public function getList($sort = 'date', $direct = 'up', $filters = array(), $parentId = 0)
    {
        return parent::getList($sort, $direct, $filters, $parentId);
    }

    public function updateSupplyData($supplyId)
    {
        $supply = new Supplies();
        $supply->setId($supplyId)->load();

        $select = $this->getSql()->select();
        $select->from(['t' => 'supplies_products'])
            ->columns(['price' => new Expression('SUM(price * order_count)'), 'count' =>  new Expression('SUM(count)')])
            ->where->equalTo('supply_id', $supplyId);

        $result = $this->execute($select)->current();

        //update status
        if($result['count']) {
            $supply->set('status', Supplies::STATUS_NEW);
        } else {
            $supply->set('status', Supplies::STATUS_COMPLETE);
        }

        //update price
        $totalPrice = $this->execute($select)->current()['price'];
        $supply->set('price', $totalPrice)->save();

        $supply->save();

        return $supply;
    }
    
    public function updateRequests($product, $count)
    {
        $select = $this->getSql()->select()
            ->from(['or' => 'orders_request'])
            ->columns(['id', 'contact'])
            ->where([
                'or.status'     => '0',
                'or.product_id' => $product->get('product_id'),
                'or.price_id'   => $product->get('size_id'),
                'or.taste_id'   => $product->get('taste_id'),
            ])
            ->limit((int) $count);

        foreach($this->execute($select) as $row) {
            $request = new Requests();
            $request->fill($row);



            if(strpos($request->get('contact'), '@')) {
                $this->sendRequestEmail($product->getPlugin('product'), $request);
            } else {
                $this->getCallcenterService()->addCall([
                    'type_id'    => Call::TYPE_REQUEST,
                    'item_id'    => $request->getId(),
                    'phone'      => $request->get('contact'),
                    'name'       => '',
                    'theme'      => 'Запрос на товар',
                    'desc'       => 'Товар поступил на склад',
                ]);
            }

            $request
                ->set('status', 1)
                ->save();
        };
    }

    public function sendRequestEmail($product, $request)
    {
        $mail = new Mail();
        $mail->setTemplate(MODULE_DIR . '/Catalog/view/catalog/mail/request-arrived.phtml')
            ->setHeader($product->get('name') . ' - доставлен по вашему запросу')
            ->setVariables([
                'product' => $product,
                'request' => $request,
            ])
            ->addTo($request->get('contact'))
            ->send();
    }

    /**
     * @param \Aptero\Db\Entity\EntityCollection $collection
     * @param $filters
     * @return \Aptero\Db\Entity\EntityCollection
     */
    public function setFilter($collection, $filters)
    {
        if($filters['search']) {
            $collection->select()->where->like('t.name', '%' . $filters['search'] . '%');
        }

        unset($filters['search']);

        if(isset($_GET['product_id']) && $filters['product_id'] = $_GET['product_id']) {
            $collection->select()
                ->join(['c' => 'supplies_products'], 'c.supply_id = t.id', [])
                ->where(['c.product_id' => $filters['product_id']]);
        }

        if(isset($_GET['user_id']) && $filters['user_id'] = $_GET['user_id']) {
            $collection->select()
                ->where(['t.user_id' => $filters['user_id']]);
        }

        foreach($filters as $field => $val) {
            if(!empty($val)) {
                $collection->select()->where(array($field => $val));
            }
        }

        $collection->select()->group('t.id');

        return $collection;
    }

    /**
     * @return \Catalog\Service\OrdersService
     */
    protected function getPublicOrderService()
    {
        return $this->getServiceManager()->get('Catalog\Service\OrdersService');
    }

    /**
     * @return \ManagerAdmin\Service\ManagerService
     */
    protected function getManagerService()
    {
        return $this->getServiceManager()->get('ManagerAdmin\Service\ManagerService');
    }

    /**
     * @return \CallcenterAdmin\Service\CallcenterService
     */
    protected function getCallcenterService()
    {
        return $this->getServiceManager()->get('CallcenterAdmin\Service\CallcenterService');
    }
}