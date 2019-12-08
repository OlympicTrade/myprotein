<?php
namespace DeliveryAdmin\Service;

use Aptero\Service\Admin\TableService;

class PointsService extends TableService
{

    public function setFilter($list, $filters)
    {
        $list = parent::setFilter($list, $filters);

        if($filters['search']) {
            $list->select()
                ->join(['c' => 'delivery_cities'], 'c.id = t.city_id' , [])
                ->where
                    ->like('t.name', '%' . $filters['search'] . '%')
                    ->or
                    ->like('t.address', '%' . $filters['search'] . '%')
                    ->or
                    ->like('c.name', '%' . $filters['search'] . '%')
                ;
        }

        return $list;
    }
}