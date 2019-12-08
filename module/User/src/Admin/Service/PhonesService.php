<?php
namespace UserAdmin\Service;

use Aptero\Service\Admin\TableService;

class PhonesService extends TableService
{
    /**
     * @param \Aptero\Db\Entity\EntityCollection $list
     * @param $filters
     * @return \Aptero\Db\Entity\EntityCollection
     */
    public function setFilter($list, $filters)
    {
        if($filters['search']) {
            $list->select()->where->like('t.phone', '%' . $filters['search'] . '%');
        }

        return $list;
    }
}