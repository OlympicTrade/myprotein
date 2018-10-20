<?php
namespace UserAdmin\Service;

use Aptero\Service\Admin\TableService;

class PhonesService extends TableService
{
    /**
     * @param \Aptero\Db\Entity\EntityCollection $collection
     * @param $filters
     * @return \Aptero\Db\Entity\EntityCollection
     */
    public function setFilter($collection, $filters)
    {
        if($filters['search']) {
            $collection->select()->where->like('t.phone', '%' . $filters['search'] . '%');
        }

        return $collection;
    }
}