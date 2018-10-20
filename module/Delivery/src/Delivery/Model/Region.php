<?php
namespace Delivery\Model;

use Aptero\Db\Entity\Entity;

class Region extends Entity
{
    public function __construct()
    {
        $this->setTable('delivery_regions');

        $this->addProperties([
            'name'     => [],
            'priority' => [],
        ]);

        $this->addPlugin('cities', function($model) {
            $points = City::getEntityCollection();
            $points->select()->where(['region_id' => $model->getId()]);

            return $points;
        });
    }
}