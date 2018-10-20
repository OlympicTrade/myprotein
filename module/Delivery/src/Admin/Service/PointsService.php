<?php
namespace DeliveryAdmin\Service;

use Aptero\Service\Admin\TableService;

class PointsService extends TableService
{

    public function setFilter($collection, $filters)
    {
        $collection = parent::setFilter($collection, $filters);

        if($filters['search']) {
            $collection->select()
                ->join(['c' => 'delivery_cities'], 'c.id = t.city_id' , [])
                ->where
                    ->like('t.name', '%' . $filters['search'] . '%')
                    ->or
                    ->like('t.address', '%' . $filters['search'] . '%')
                    ->or
                    ->like('c.name', '%' . $filters['search'] . '%')
                ;
        }

        return $collection;
    }
}