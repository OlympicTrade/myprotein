<?php
namespace CallcenterAdmin\Service;

use Aptero\Mail\Mail;
use Aptero\Service\Admin\TableService;
use CallcenterAdmin\Model\Call;
use CatalogAdmin\Model\Orders;
use ManagerAdmin\Model\Task;

class CallcenterService extends TableService
{
    public function getListBaseSelect()
    {
        $calls = Call::getEntityCollection();

        return $calls;
    }

    public function setListOrder($collection, $sort, $direct)
    {
        $sort = $sort ? $sort : 'status';
        $direct = $direct ? $direct : 'down';

        $sort .= $direct == 'down' ? ' ASC' : ' DESC';

        $collection->select()->order($sort)->order('type_id');

        return $collection;
    }

    public function addCall($options)
    {
        $call = new Call();

        $call->select()
            ->where([
                'item_id'   => $options['item_id'],
                'type_id'   => $options['type_id'],
            ]);

        if($call->load()) {
            return $call;
        }

        if(isset($options['phone'])) {
            $phone = $this->getUserService()->addPhone($options['phone']);
            $options['phone_id'] = $phone->getId();
        }

        $call = new Call();
        $call->setVariables($options)->save();

        $feedbackModule = new \Application\Model\Module(['name' => 'Contacts', 'section' => 'Feedback']);
        $feedbackModule->load();

        $mail = new Mail();
        $mail->setTemplate(MODULE_DIR . '/Callcenter/view/callcenter/mail/call-admin.phtml')
            ->setHeader('Звонок: ' . $call->get('theme'))
            ->setVariables(['call' => $call])
            ->addTo($feedbackModule->getPlugin('settings')->get('email'));

        $mail->send();

        return $call;
    }

    public function completeCall($id, $status)
    {
        $call = new Call();
        $call->setId($id);

        if(!$id || !$call->load()) {
            return false;
        }

        $call->set('status', $status)->save();

        $type = $call->get('type_id');

        if($type == Call::TYPE_REQUEST) {
            $this->confirmRequest($call, $status);
        } elseif($type == Call::TYPE_ORDER) {
            $this->confirmOrder($call, $status);
        } elseif($type == Call::TYPE_RETURN) {
            (new Task())->setVariables([
                'task_id'       => Task::TYPE_ORDER_RETURN,
                'item_id'       => $call->getId(),
                'name'          => 'Звонок (Не забрали заказ)',
                'duration'      => 8,
            ])->save();
        }

        return true;
    }

    protected function confirmRequest($call)
    {
        $request = $call->getPlugin('item');

        $request->set('status', $call->get('status'))->save();

        (new Task())->setVariables([
            'task_id'       => Task::TYPE_CALLBACK_REQUEST,
            'item_id'       => $call->getId(),
            'name'          => 'Звонок (запрос товара)',
            'duration'      => 8,
        ])->save();
    }

    protected function confirmOrder($call)
    {
        $order = $call->getPlugin('item');

        if($call->get('status') == Call::STATUS_COMPLETE) {
            $order->set('status', Orders::STATUS_PROCESSING);
        } else {
            $order->set('status', Orders::STATUS_CANCELED);
            $this->getOrderService()->cleanOrder($order->getId());
        }

        (new Task())->setVariables([
            'task_id'       => Task::TYPE_ORDER_CONFIRM,
            'item_id'       => $call->getId(),
            'name'          => 'Звонок (Подтверждение заказа)',
            'duration'      => 8,
        ])->save();

        $order->save();
    }

    /**
     * @return \User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->getServiceManager()->get('User\Service\UserService');
    }

    /**
     * @return \Catalog\Service\OrdersService
     */
    protected function getOrderService()
    {
        return $this->getServiceManager()->get('Catalog\Service\OrdersService');
    }
}