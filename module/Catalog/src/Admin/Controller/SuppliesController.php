<?php
namespace CatalogAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;
use CatalogAdmin\Model\Products;
use CatalogAdmin\Model\Reviews;
use Aptero\Service\Admin\TableService;
use CatalogAdmin\Model\Supplies;
use ReviewsAdmin\Model\Review;
use Sync\Service\SyncService;
use Zend\Json\Json;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class SuppliesController extends AbstractActionController
{
    public function __construct() {
        parent::__construct();

        $classes = array(
            0  => 'red',
            3  => 'yellow',
            5  => 'green',
        );

        $this->setFields(array(
            'name' => array(
                'name'      => 'Номер',
                'type'      => TableService::FIELD_TYPE_LINK,
                'field'     => 'number',
                'width'     => '8',
            ),
            'user_id' => array(
                'name'      => 'ФИО',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'user_id',
                'width'     => '20',
                'filter'    => function($value, $row) {
                    return '<b><a class="popup-form" href="/admin/catalog/supplies/user-info/' . $value . '/">' . Supplies::$users[$value] . '</a></b>';
                },
            ),
            'link' => array(
                'name'      => 'Трекер',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'width'     => '6',
                'filter'    => function($value, $row) {
                    if(!$value) return '';

                    return
                        '<a target="_blank" href="http://www.trackmytrakpak.com/?MyTrakPakNumber=' . $value . '">TP</a> | '.
                        '<a target="_blank" href="https://boxberry.ru/tracking/?id=' . $value . '">BB</a>'
                        ;
                },
            ),
            'status' => array(
                'name'      => 'Статус',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'status',
                'width'     => '6',
                'filter'    => function($value, $row) use ($classes){
                    return '<span class="wrap ' . $classes[$value] . '" style="display: inline-block; width: 20px; height: 20px; border-radius: 50%"></span>';
                },
                'tdStyle'   => array(
                    'text-align' => 'center'
                ),
            ),
            'price' => array(
                'name'      => 'Товары',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'price',
                'filter'    => function($value, $row) use ($classes){
                    return $row->getPrice(0) . ' руб.' /* . ' (' . $row->getPrice() . ' €)'*/;
                },
                'width'     => '8',
            ),
            /*'delivery' => array(
                'name'      => 'Доставка',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'price',
                'filter'    => function($value, $row) use ($classes){
                    return $row->getDelivery(0) . ' (' . $row->getDelivery() . ' €)';
                },
                'width'     => '18',
            ),*/
            'date' => array(
                'name'      => 'Дата',
                'type'      => TableService::FIELD_TYPE_DATE,
                'field'     => 'date',
                'width'     => '52',
            ),
        ));
    }

    public function userInfoAction()
    {
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables([
            'id' => $this->params()->fromRoute('id')
        ]);

        return $view;
    }

    public function syncStocksAction()
    {
        $ids = $this->params()->fromPost('ids');

        $msg = '';
        foreach ($ids as $id) {
            $product = new Products();
            $product->setId($id);
            $errors = Json::decode(
                file_get_contents(SyncService::SITE . '/sync/stock/sync-product/?id=' . $product->getId())
            )->errors;

            $msg .= $product->get('name') . ': ';
            if(!$errors) {
                $msg .= "Успех\n";
            } else {
                $msg .= "\n - " . implode("\n - ", $errors) . "\n";
            }
        }

        return new JsonModel(['msg' => $msg]);
    }

    public function listAction()
    {
        $view = parent::listAction();

        $service = $this->getService();

        $view->setVariable('statistic', [
            'weight'     => $service->getWeightStatistic(),
            'lacked'     => $service->getProductsLack(),
            'requested'  => $service->getProductsRequested(),
        ]);

        return $view;
    }

    public function cartUpdateAction()
    {
        $data = $this->params()->fromPost();
        $resp = [];

        switch ($data['type']) {
            case 'price':
                $resp['price'] = $this->getService()->updateCartPrice($data)['price'];
                break;
            case 'count':
                $resp['stock'] = $this->getService()->updateCartCount($data)['stock'];
                break;
        }

        return new JsonModel($resp);
    }

    public function addProductAction()
    {
        $publicProdService = $this->getServiceLocator()->get('Catalog\Service\ProductsService');

        $this->getService()->addToCart($_POST, $publicProdService);

        return new JsonModel([]);
    }
}