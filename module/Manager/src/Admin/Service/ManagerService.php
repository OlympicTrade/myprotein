<?php
namespace ManagerAdmin\Service;

use Aptero\Service\AbstractService;
use ManagerAdmin\Model\Task;
use User\Service\AuthService;

class ManagerService extends AbstractService
{
    public function getTasks($options)
    {
        $sql = $this->getSql();

        $select = $sql->select();
        $select->from(['t' => 'manager_tasks'])
            ->columns(['id', 'task_id', 'name', 'desc', 'duration', 'datetime'])
            ->join(['u' => 'users'], 'u.id = t.user_id', ['user_id' => 'sync_id']);

        if(!empty($options['datetime'])) {
            $select->where->greaterThanOrEqualTo('t.datetime', $options['datetime']);
        }

        $result = [];
        foreach($this->execute($select) as $row) {
            $result[] = $row;
        }

        return $result;
    }
}