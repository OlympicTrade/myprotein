<?php
namespace ManagerAdmin\Model;

use Aptero\Db\Entity\Entity;
use User\Service\AuthService;

class Task extends Entity
{
    const TYPE_ORDER_CONFIRM  = 10;
    const TYPE_ORDER_DELIVERY = 20;
    const TYPE_ORDER_RETURN   = 30;
    const TYPE_ORDER_OTHER    = 40;
    const TYPE_SUPPLY_NEW     = 50;
    const TYPE_SUPPLY_CONFIRM = 60;
    const TYPE_SUPPLY_OTHER   = 70;
    const TYPE_CALLBACK_REQUEST = 80;
    const TYPE_CALLBACK_OTHER = 90;
    const TYPE_PRODUCT_NEW = 100;

    public function __construct()
    {
        $this->setTable('manager_tasks');

        $this->addProperties([
            'user_id'       => [],
            'task_id'       => [],
            'item_id'       => [],
            'name'          => [],
            'desc'          => [],
            'duration'      => [],
            'time'          => [],
        ]);

        $this->getEventManager()->attach([Entity::EVENT_PRE_INSERT], function ($event) {
            $model = $event->getTarget();
            $model->set('user_id', AuthService::getUser()->getId());
        });
    }
}