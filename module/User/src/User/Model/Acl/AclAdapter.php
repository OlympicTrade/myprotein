<?php
namespace User\Model\Acl;

use Aptero\Db\AbstractDb;
use Zend\Db\Sql\Select;

class AclAdapter extends AbstractDb
{
    const TABLE_ACL_ROLES = 'users_roles';
    const TABLE_ACL_RESOURCES = 'users_resources';
    const TABLE_ACL_RULES = 'users_rules';

    public function getRoles()
    {
        $this->initialize();
        $select = $this->getSql()->select()
            ->reset(Select::TABLE)
            ->from(['t' => self::TABLE_ACL_ROLES])
            ->columns(['role'])
            ->join(['r' => self::TABLE_ACL_ROLES], 'r.id = t.parent', ['parent' => 'role'], 'left')
            ->order('t.parent');

        return $this->fetchAll($select);
    }

    public function getResources()
    {
        $select = $this->getSql()->select()
            ->reset(Select::TABLE)
            ->from(['t' => self::TABLE_ACL_RESOURCES])
            ->columns(['resource'])
            ->join(['r' => self::TABLE_ACL_RESOURCES], 'r.id = t.parent', ['parent' => 'resource'], 'left')
            ->order('t.parent');

        return $this->fetchAll($select);
    }

    public function getRules()
    {
        $select = $this->getSql()->select()
            ->reset(Select::TABLE)
            ->from(['t' => self::TABLE_ACL_RULES])
            ->columns(['access'])
            ->join(['ro' => self::TABLE_ACL_ROLES], 'ro.id = t.role_id', ['role'])
            ->join(['re' => self::TABLE_ACL_RESOURCES], 're.id = t.resource_id', ['resource']);

        return $this->fetchAll($select);
    }
}