<?php
namespace Aptero\Db\ResultSet;

use Zend\Db\ResultSet\AbstractResultSet;
use Aptero\Db\Entity\Entity;

class ResultSet extends AbstractResultSet
{
    /**
     * @var Entity
     */
    protected $prototype = null;

    /**
     * @return array|\ArrayObject|null
     */
    public function current()
    {
        $entity = clone $this->prototype;
        $entity->rFill(parent::current());

        return $entity;
    }

    public function setPrototype(Entity $prototype)
    {
        $this->prototype = $prototype;
    }

    public function getPrototype()
    {
        return $this->prototype;
    }
}