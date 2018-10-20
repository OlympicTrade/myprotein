<?php
namespace TasksAdmin\Service;

use Aptero\Service\Admin\TableService;

class TasksService extends TableService
{
    public function getList($sort = 'status', $direct = 'up', $filters = array(), $parentId = 0)
    {
        $sort = $sort ? $sort : 'status';
        return parent::getList($sort, $direct, $filters, $parentId);
    }
}