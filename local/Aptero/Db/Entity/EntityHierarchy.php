<?php
namespace Aptero\Db\Entity;

use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityCollectionHierarchy;

class EntityHierarchy extends Entity
{
    /**
     * @var string
     */
    protected $parentField = 'parent';

    /**
     * @var int
     */
    protected $parentId = null;

    public function clear()
    {
        $this->parentId = null;

        return parent::clear();
    }

    /**
     * @param bool $clear
     * @return EntityCollectionHierarchy
     */
    public function getCollection($clear = false)
    {
        $collection = new EntityCollectionHierarchy();
        $collection->setPrototype(clone $this);

        if(!$clear) {
            $collection->setSelect(clone $this->select());
            $collection->setParentId($this->getParentId());
        }

        return $collection;
    }

    /**
     * @param $data
     * @return $this
     */
    public function fill($data)
    {
        parent::fill($data);

        $this->setParentId($this->get($this->parentField));

        return $this;
    }

    /**
     * @param bool $transaction
     * @return bool
     */
    public function remove($transaction = true)
    {
        if(!$this->id) {
            return false;
        }

        if($transaction) {
            $this->getDbAdapter()->getDriver()->getConnection()->beginTransaction();
        }

        $children = $this->getChildren();

        foreach($children as $child) {
            $commit = $child->remove(false);

            if(!$commit) {
                if($transaction) {
                    $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                    return false;
                }
            }
        }

        $commit = parent::remove(false);

        if ($transaction) {
            if ($commit) {
                $this->getDbAdapter()->getDriver()->getConnection()->commit();
            } else {
                $this->getDbAdapter()->getDriver()->getConnection()->rollback();
            }
        }

        return $commit;
    }

    /**
     * @return EntityCollectionHierarchy
     */
    public function getChildren()
    {
        $entityCollection = $this->getCollection();
        $entityCollection->setParentId($this->getId());

        return $entityCollection;
    }

    /**
     * @return $this|null
     */
    public function getParent()
    {
        if($this->getParentId() == 0) {
            return null;
        }

        $entity = clone $this;
        $entity->clearSelect();

        $entity->setId($this->getParentId());

        return $entity;
    }

    /**
     * @param int $parentId
     * @return $this
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }
}