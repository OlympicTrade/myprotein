<?php
namespace CatalogAdmin\Service;

use Aptero\Delivery\Glavpunkt;
use Aptero\Service\Admin\TableService;
use CallcenterAdmin\Model\Call;
use Catalog\Model\Order;
use CatalogAdmin\Model\Cart;
use CatalogAdmin\Model\Orders;
use DeliveryAdmin\Model\Delivery;
use ManagerAdmin\Model\Task;

class OrdersService extends TableService
{
    public function ordersExport($orders, $date)
    {
		foreach($orders as $order) {
			$order
				->getPlugin('attrs')
				->set('export_date', $date)
				->save();
		}
		
        return $this->getDeliveryService()->addOrdersExport($date);
    }
	
    public function clearCart($orderId)
    {
        $this->getPublicOrderService()->cleanOrder($orderId);
    }

    public function changeOrderStatus($id, $status)
    {
        $result = [];
        if(is_array($id)) {
            foreach($id as $sId) {
                $result[$sId] = $this->changeOrderStatus($sId, $status);
            }
            return $result;
        }

        $order = new Orders();
        $order->setId($id);

        if(!$order->load()) {
            return false;
        }

        $error = '';

        switch ($status) {
            case Orders::STATUS_PENDING:
                $order->set('status', Orders::STATUS_PENDING);
                break;

            case Orders::STATUS_COLLECTED:
                /*if(in_array($order->get('status'), [Orders::STATUS_PENDING, Orders::STATUS_DELIVERY])) {
                    $order->set('status', Orders::STATUS_COLLECTED);
                    break;
                }

                if($order->get('status') != Orders::STATUS_PROCESSING) {
                    break;
                }*/

                if($order->get('delivery_company') == Delivery::COMPANY_SHOP_LOGISTIC) {
                    $error = $this->getDeliveryService()->addDelivery($order);
                    if($error) { break; }
                }

                if($order->get('delivery_company') == Delivery::COMPANY_GLAVPUNKT) {
                    $glavpunkt = new Glavpunkt();
                    $result = $glavpunkt->addDelivery($order);

                    if($error = $result['errors']) { break; }
                }

                (new Task())->setVariables([
                    'task_id'       => Task::TYPE_ORDER_DELIVERY,
                    'item_id'       => $order->getId(),
                    'name'          => 'Заказ собран',
                    'duration'      => 5,
                ])->save();

				$order->set('status', Orders::STATUS_COLLECTED);

                break;
				
            case Orders::STATUS_DELIVERY:
                if(in_array($order->get('status'), [Orders::STATUS_PENDING])) {
                    $order->set('status', Orders::STATUS_DELIVERY);
                    break;
                }

                if($order->get('status') != Orders::STATUS_COLLECTED) {
                    break;
                }

                (new Task())->setVariables([
                    'task_id'       => Task::TYPE_ORDER_DELIVERY,
                    'item_id'       => $order->getId(),
                    'name'          => 'Заказ отправлен в доставку',
                    'duration'      => 5,
                ])->save();

                (new Glavpunkt())->delInvoice();
                
                $order->set('status', Orders::STATUS_DELIVERY);

                break;
            case Orders::STATUS_COMPLETE:
                if($order->get('status') != Orders::STATUS_DELIVERY) {
                    break;
                }
                
                $order->set('status', Orders::STATUS_COMPLETE);
                break;
            case Orders::STATUS_PROCESSING:
                if(in_array($order->get('status'), [Orders::STATUS_PENDING, Orders::STATUS_CANCELED])) {
                    $order->set('status', Orders::STATUS_PROCESSING);
                    break;
                }

                break;
            case Orders::STATUS_CANCELED:
                if(in_array($order->get('status'), [Orders::STATUS_PENDING])) {
                    $order->set('status', Orders::STATUS_CANCELED);
                    break;
                }

                if($order->get('status') != Orders::STATUS_PROCESSING) {
                    break;
                }
                
                $order->set('status', Orders::STATUS_CANCELED);
                $this->clearCart($order->getId());

                //$this->getDeliveryService()->delDelivery($order);

                break;
            case Orders::STATUS_RETURN:
                if($order->get('status') != Orders::STATUS_DELIVERY) {
                    break;
                }

                $order->set('status', Orders::STATUS_RETURN);

                (new Task())->setVariables([
                    'task_id'       => Task::TYPE_ORDER_RETURN,
                    'item_id'       => $order->getId(),
                    'name'          => 'Обработка возврата',
                    'duration'      => 15,
                ])->save();

                $this->getCallcenterService()->addCall([
                    'type_id'    => Call::TYPE_RETURN,
                    'item_id'    => $order->getId(),
                    'phone_id'   => $order->get('phone_id'),
                    'name'       => $order->getPlugin('attrs')->get('name'),
                    'theme'      => 'Не забрали заказ',
                    'desc'       => '',
                ]);

                $this->clearCart($order->getId());
                break;
            default:
                $error = 'Неизвестный статус';
        }

        if(!$error) {
            $order->save();
        }
        
        return $error;
    }

    public function save($formData, $model)
    {
        parent::save($formData, $model);

        $order = new Order();
        $order->setId($model->getId());
        $order = $this->getPublicOrderService()->updateOrderPrice($order);
        $order->save();

        return true;
    }

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

        $count = $data['count'];

        $stock = $this->getPublicOrderService()->changeProductCount($product->getId(), $data['tasteId'], $data['sizeId'], -$count);

        $cart = new Cart();
        $cart->setVariables([
            'order_id'   => $data['orderId'],
            'product_id' => $product->getId(),
            'size_id'    => $data['sizeId'],
            'taste_id'   => $data['tasteId'],
            'count'      => $stock['count'],
            'price'      => $product->get('price'),
        ]);

        $cart->save();

        $order = new Order();
        $order->setId($data['orderId']);
        $order = $this->getPublicOrderService()->updateOrderPrice($order);
        $order->save();

        return array(
            'order' => $order,
            'cart'  => $cart,
        );
    }

    public function updateCartCount($data)
    {
        $count = (int) $data['count'];

        $cart = new Cart();
        $cart->setId($data['cartId']);

        if(!$cart->load()) {
            return false;
        }

        $order = new Order();
        $order->setId($data['orderId']);

        $countDiff = $cart->get('count') - $count;
        $stock = $this->getPublicOrderService()->changeProductCount($cart->get('product_id'), $cart->get('taste_id'), $cart->get('size_id'), $countDiff);

        if($stock['count'] > 0) {
            $cart->set('count', $count);
            $cart->save();

            $order = $this->getPublicOrderService()->updateOrderPrice($order);
            $order->save();
        }

        return array(
            'order'  => $order,
            'cart'   => $cart,
            'stock'  => $stock['stock'],
        );
    }

    /**
     * @param \Aptero\Db\Entity\EntityCollection $collection
     * @param $filters
     * @return \Aptero\Db\Entity\EntityCollection
     */
    public function setFilter($collection, $filters)
    {
        if($filters['search']) {
            $collection->select()
                ->join(['uf' => 'users_phones'], 'uf.id = t.phone_id', [])
                ->where
                    ->like('t.id', '%' . $filters['search'] . '%')
                    ->or
                    ->like('uf.phone', '%' . $filters['search'] . '%');
        }

        unset($filters['search']);

        if(isset($_GET['product_id']) && $filters['product_id'] = $_GET['product_id']) {
            $collection->select()
                ->join(['c' => 'orders_cart'], 'c.order_id = t.id', [])
                ->where(['c.product_id' => $filters['product_id']]);
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
     * @return \CallcenterAdmin\Service\CallcenterService
     */
    protected function getCallcenterService()
    {
        return $this->getServiceManager()->get('CallcenterAdmin\Service\CallcenterService');
    }

    /**
     * @return \DeliveryAdmin\Service\DeliveryService
     */
    protected function getDeliveryService()
    {
        return $this->getServiceManager()->get('DeliveryAdmin\Service\DeliveryService');
    }
}