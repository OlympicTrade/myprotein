<?php
namespace CatalogAdmin\Model;

use Aptero\Db\Entity\Entity;

class Taste extends Entity
{
    public function __construct()
    {
        $this->setTable('products_taste');

        $this->addProperties(array(
            'depend'        => array(),
            'name'          => array(),
            'coefficient'   => array(),
        ));

        $this->getEventManager()->attach(array(Entity::EVENT_PRE_DELETE), function ($event) {
            $model = $event->getTarget();

            $stock = new Stock();
            $stock->select()->where(array('taste_id' => $model->getId()));
            $stock->remove();

            return true;
        });
    }
}