<?php

namespace User\Model\Acl;

use Zend\Permissions\Acl\Acl as ZendAcl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

class AclBuilder extends ZendAcl
{
    protected $serviceLocator;

    public function initialize($sm)
    {
        $aclAdapter = $sm->get('User\Model\AclAdapter');

        $this->buildRoles($aclAdapter->getRoles());
        $this->buildResources($aclAdapter->getResources());
        $this->buildRules($aclAdapter->getRules());

        return $this;
    }

    protected function buildRoles($roles)
    {
        foreach ($roles as $role) {
            if (!$this->hasRole($role['role'])) {
                if (empty($role['parent'])) {
                    $parent = array();
                } else {
                    $parent = explode(',', $role['parent']);
                }

                $this->addRole(new Role($role['role']), $parent);
            }
        }
    }

    protected function buildResources($resources)
    {
        foreach ($resources as $resource) {
            if (!$this->hasResource($resource['resource'])) {
                $this->addResource(new Resource($resource['resource']), $resource['parent']);
            }
        }
    }

    protected function buildRules($rules)
    {
        foreach ($rules as $rule) {
            if($rule['access']) {
                $this->allow($rule['role'], $rule['resource']);
            } else {
                $this->deny($rule['role'], $rule['resource']);
            }
        }
    }
}
