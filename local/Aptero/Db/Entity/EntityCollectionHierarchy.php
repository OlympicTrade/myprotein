<?php
/**
 * Example
 * //Create entity collection ans set prototype
 * $collection = new EntityCollection();
 * $collection->getPrototype(new Entity());
 *
 * $collection->setSelect(select object);
 *
 * foreach($collection as $entity) {
 *    echo $entity['name'] . '<br>';
 * }
 */

namespace Aptero\Db\Entity;

use Aptero\Db\Entity\EntityCollection;
use Aptero\Db\Entity\EntityHierarchy;
use Aptero\Db\AbstractDb;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Log\Writer\ChromePhp;
use Zend\Paginator\Adapter\AdapterInterface;

class EntityCollectionHierarchy extends EntityCollection
{
    /**
     * @var int
     */
    protected $parentId = null;

    /**
     * @var string
     */
    protected $parentFiled = 'parent';

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
     * @var EntityHierarchy
     */
    protected $prototype = null;

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param EntityHierarchy $prototype
     * @return $this
     */
    public function setPrototype($prototype)
    {
        $this->setParentId($prototype->getParentId());

        parent::setPrototype($prototype);

        return $this;
    }

    public function getLoadSelect()
    {
        $select = clone $this->select();

        if($this->parentId !== null) {
            $select->where(array($this->parentFiled => $this->parentId));
        }

        return $select;
    }

    /* Iterator */
    public function current()
    {
        $entity = parent::current();
        $entity->setParentId($this->getParentId());


        return $entity;
    }
}