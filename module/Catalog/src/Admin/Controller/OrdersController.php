<?php
namespace CatalogAdmin\Controller;

use Aptero\Delivery\Glavpunkt;
use Aptero\Mvc\Controller\Admin\AbstractActionController;
use Aptero\Service\Admin\TableService;
use Aptero\String\Date;
use CallcenterAdmin\Model\Call;
use Catalog\Model\Order;
use CatalogAdmin\Model\Orders;
use Delivery\Model\Delivery;
use Zend\Json\Json;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\View\View;

class OrdersController extends AbstractActionController
{
    public function __construct() {
        parent::__construct();

        $classes = [
            1  => 'gray',
            3  => 'brown',
            5  => 'blue',
            7  => 'pink',
            10 => 'yellow',
            15 => 'green',
            20 => 'gray',
            30 => 'red',
            35 => 'violet',
            40 => 'cyan',
        ];

        $this->setFields([
        'cb' => [
            'name'      => '',
            'type'      => TableService::CHECKBOX,
            'field'     => 'id',
            'width'     => '3'
        ],
        'id' => [
            'name'      => 'id',
            'type'      => TableService::FIELD_TYPE_TEXT,
            'field'     => 'id',
            'width'     => '6',
            'filter'    => function($value, $row){
                return '<a href="/admin/catalog/orders/order-info/?id=' . $row->getId() . '" class="popup-form">' . $row->getId() . '</a>';
            },
        ],
        'name' => [
            'name'      => 'Имя',
            'type'      => TableService::FIELD_TYPE_TEXT,
            'field'     => 'attrs-name',
            'width'     => '15',
            'sort'      => [
                'enabled'   => false
            ],
        ],
        'adwords' => [
            'name'      => 'Источник',
            'type'      => TableService::FIELD_TYPE_TEXT,
            'field'     => 'adwords_id',
            'filter'    => function($value, $row){
                return $row->getPlugin('adwords')->get('source');
            },
            'width'     => '9',
        ],
        'phone' => array(
            'name'      => 'Телефон',
            'type'      => TableService::FIELD_TYPE_TEXT,
            'filter'    => function($value, $row){
                return $row->getPlugin('phone')->get('phone');
            },
            'width'     => '10',
            'sort'      => [
                'enabled'   => false
            ],
        ),
        'income' => [
            'name'      => 'Стоимость',
            'type'      => TableService::FIELD_TYPE_TEXT,
            'field'     => 'income',
            'filter'    => function($value, $row, $view) {
                $str = $row->getPrice() . ' руб.';

                if($row->isPaid()) {
                    $str .= ' <span class="green"><i class="fas fa-check-circle"></i></span>';
                } elseif($row->get('paid')) {
                    $str .= ' <span class="green"><i class="fas fa-exclamation-triangle"></i></span>';
                }

                return $str;
            },
            'width'     => '8',
        ],
        'status' => [
            'name'      => 'Статус',
            'type'      => TableService::FIELD_TYPE_TEXT,
            'field'     => 'status',
            'width'     => '10',
            'filter'    => function($value, $row) use ($classes){
                if($value == Orders::STATUS_PROBLEM) {
                    $call = new Call();
                    $call->select()->where(['item_id' => $row->getId()]);
                    $call->load();
                    return '<a href="/admin/callcenter/callcenter/edit/?id=' . $call->getId() . '" class="wrap ' . $classes[$value] . '">' . Orders::$processStatuses[$value]. '</a>';
                }

                return '<span class="wrap ' . $classes[$value] . '">' . Orders::$processStatuses[$value]. '</span>';
            },
            'sort'      => [
                'enabled'   => false
            ],
        ],
        'time_create' => [
            'name'      => 'Дата заказа',
            'type'      => TableService::FIELD_TYPE_TEXT,
            'filter'    => function($value, $row) use ($classes){
                return (new Date($value))->toStr(['time' => true, 'months' => 'short']);
            },
            'field'     => 'time_create',
            'width'     => '14',
        ],
        'region_id' => [
            'name'      => 'Регион',
            'type'      => TableService::FIELD_TYPE_TEXT,
            'field'     => 'status',
            'width'     => '11',
            'filter'    => function($value, $row) {
                return $row->getCity()->get('name');
            },
            'sort'      => [
                'enabled'   => false
            ],
        ],
        'delivery' => [
                'name'      => 'Доставка',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'status',
                'width'     => '14',
                'filter'    => function($value, $row, $view) {
                    $attrs = $row->getPlugin('attrs');

                    $delivery = $attrs->get('delivery');

                    if($attrs->get('delivery') == 'courier') {
                        return '<i class="fas fa-truck"></i> ' . $view->date($attrs->get('date'), ['year' => false, 'months' => 'short'], 'd.m.Y') . ' с ' . $attrs->get('time_from') . ' до ' . $attrs->get('time_to');
                    } elseif($delivery == 'pickup') {
                        return '<i class="fas fa-home"></i>';
                    } elseif($delivery == 'post') {
                        return '<i class="fas fa-envelope"></i>';
                    } else {
                        return '<i class="fas fa-question-circle"></i>';
                    }
                },
                'sort'      => [
                    'enabled'   => false
                ],
            ],
        ]);
    }
	
	public function stockAction()
	{
		$orders = Orders::getEntityCollection();
		$orders->select()->where(['status' => Orders::STATUS_PROCESSING]);
		
		$view = new ViewModel();
        $view->setTerminal(true);
		$view->setVariables([
			'orders' => $orders
		]);
		
		return $view;
	}

    public function ordersControlsAction()
	{
		$view = new ViewModel();
        $view->setTerminal(true);

        return $view;
	}
	
    public function ordersExportAction()
    {
        $date = $this->params()->fromPost('date');
		
		$orders = Orders::getEntityCollection();
		$ids = $this->params()->fromPost('ids');
		if(!empty($ids)) {
			$orders->select()->where(['t.id' => $ids]);
		} else {
			$orders->select()->where(['status' => Orders::STATUS_PROCESSING]);
		}
		
		$error = $this->getService()->ordersExport($orders, $date);
		
		return new JsonModel([
			'rows' => [
				'Забор' => $error,
			],
		]);
    }

    public function orderIndexExpressAction()
    {
        $orders = Orders::getEntityCollection();
        $orders->select()
            ->where([
                'delivery_company' => Delivery::COMPANY_INDEX_EXPRESS,
                'status' => Orders::STATUS_PROCESSING
            ]);

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables([
            'orders' => $orders
        ]);

        return $view;
    }

    public function orderGlavpunktAction()
    {
        $orders = Orders::getEntityCollection();
        $orders->select()
            ->where([
                'delivery_company' => Delivery::COMPANY_GLAVPUNKT,
                'status' => Orders::STATUS_PROCESSING
            ]);

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables([
            'orders' => $orders
        ]);

        return $view;
    }

    public function setStatusCollectedAction()
    {
        $type = $this->params()->fromPost('type');

        if(!($ids = $this->params()->fromPost('ids')) && empty($ids)) {
			$ids = [];
			
			$orders = Orders::getEntityCollection();
			$orders->select()
				->columns(['id'])
				->where([
				    'status' => Orders::STATUS_PROCESSING,
                    'delivery_company' => $type
                ]);
			
			foreach($orders as $order) { $ids[] = $order->getId(); }
		}
		
        $errors = $this->getService()->changeOrderStatus($ids, Orders::STATUS_COLLECTED);
		
		return new JsonModel([
			'rows' => $errors,
		]);
    }
	
    public function setStatusDeliveryAction()
    {		
		if(!($ids = $this->params()->fromPost('ids')) && empty($ids)) {
			$ids = [];
			
			$orders = Orders::getEntityCollection();
			$orders->select()
				->columns(['id'])
				->where(['status' => Orders::STATUS_COLLECTED]);
			
			foreach($orders as $order) { $ids[] = $order->getId(); }
		}
		
        $errors = $this->getService()->changeOrderStatus($ids, Orders::STATUS_DELIVERY);
		
		return new JsonModel([
			'rows' => $errors,
		]);
    }
	
    public function orderInfoAction()
    {
        $orderId = $this->params()->fromQuery('id');
        
        $order = new Orders();
        $order->setId($orderId);
        
        if(!$orderId && !$order->load()) {
            return $this->send404();
        }

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(['order' => $order]);
        
        return $view;
    }

    public function getInvoicePdfAction()
    {
        $orders = Order::getEntityCollection();
        $orders->select()->where(['status' => Orders::STATUS_COLLECTED, 'delivery_company' => Delivery::COMPANY_GLAVPUNKT]);

        $glavpunkt = new Glavpunkt();
        $file = $glavpunkt->getInvoicePDF();

        return new JsonModel(['file' => $file]);
    }

    public function getBarcodesAction()
    {
        $orders = Order::getEntityCollection();
        $orders->select()->where(['status' => Orders::STATUS_COLLECTED, 'delivery_company' => Delivery::COMPANY_GLAVPUNKT]);

        $glavpunkt = new Glavpunkt();
        $file = $glavpunkt->getBarcodes($orders);

        return new JsonModel(['file' => $file]);
    }

    public function massUpdateAction()
    {
        $ids = $this->params()->fromPost('ids');
        $action = $this->params()->fromPost('action');
        
        if(!$action) {
            return new JsonModel(['error' => 'Не выбран статус заказа']);
        }

        $this->getService()->changeOrderStatus($ids, $action);

        return new JsonModel(['error' => '']);
    }

    public function changeStatusAction()
    {
        $id = $this->params()->fromPost('id');
        $status = $this->params()->fromPost('status');

        $errors = $this->getService()->changeOrderStatus($id, $status);

        return new JsonModel(['errors' => $errors]);
    }

    public function cartCountUpdateAction()
    {
        $info = $this->getService()->updateCartCount($_POST);
        $order = $info['order'];

        return new JsonModel([
            'order' => [
                'income'    => $info['order']->get('income'),
                'profit'    => $order->get('income') - $order->get('outgo') + $order->get('delivery_income') - $order->get('delivery_outgo'),
                'delivery_income'  => $info['order']->get('delivery_income'),
            ],
            'cart' => [
                'price'     => $info['cart']->get('price'),
                'count'     => $info['cart']->get('count'),
            ],
            'product' => [
                'stock' => $info['stock']
            ],
        ]);
    }

    public function addProductAction()
    {
        $publicProdService = $this->getServiceLocator()->get('Catalog\Service\ProductsService');
        
        $this->getService()->addToCart($_POST, $publicProdService);

        return new JsonModel([]);
    }
}