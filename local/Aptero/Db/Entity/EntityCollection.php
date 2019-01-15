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

use Aptero\Db\Entity\Entity;
use Aptero\Db\AbstractDb;

use Aptero\Db\Plugin\Collection;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Paginator\Adapter\AdapterInterface;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Aptero\Db\ResultSet\ResultSet;

use Iterator;

class EntityCollection extends AbstractDb implements Iterator, AdapterInterface
{
    /**
     * @var Entity
     */
    protected $prototype = null;

    /**
     * @var Select
     */
    protected $select = null;

    /**
     * Data base cursor
     * @var \Zend\Db\Adapter\Driver\Pdo\Result
     */
    protected $cursor = null;

    /**
     * @var array
     */
    protected $data = null;

    /**
     * @var int
     */
    protected $rowCount = null;

    /**
     * @return $this
     */
    public function load()
    {
        if($this->loaded) {
            return $this;
        }

        if($this->cacheLoad()) {
            $this->loaded = true;
            return $this;
        }

        $this->cursor = $this->fetchAll($this->getLoadSelect());

        if($this->cursor->count() == 0) {
            return false;
        }

        $this->loaded = true;

        $this->cacheSave();

        return $this;
    }

    public function clear()
    {
        $this->loaded = false;
        $this->data = null;
        $this->cursor = null;
    }

    /**
     * @return Entity
     */
    public function getPrototype()
    {
        return $this->prototype;
    }

    /**
     * @param Entity $prototype
     * @return $this
     */
    public function setPrototype($prototype)
    {
        $this->setTable($prototype->table());
        $this->prototype = $prototype;
        $this->loaded = false;

        return $this;
    }

    public function getLoadSelect()
    {
        return clone $this->select();
    }

    /**
     * @return $this
     */
    public function save()
    {
        foreach($this as $entity) {
            $entity->save();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function remove()
    {
        $this->load();

        foreach($this as $entity) {
            $entity->remove();
        }

        return $this;
    }

    /**
     * @param  int $offset
     * @param  int $itemCountPerPage
     * @return EntityCollection
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $this->select()->limit($offset);
        $this->select()->offset($itemCountPerPage);

        $this->load();

        return $this;
    }

    /**
     * @param Entity|array $entity
     * @return $this
     */
    public function addEntity($entity)
    {
        if(is_array($entity)) {
            $entity = (clone $this->getPrototype())->rFill($entity);
        }
        $this->data[] = $entity;
        return $this;
    }

    /**
     * @param $data
     * @return $this
     */
    public function rFill($data)
    {
        foreach($data as $row) {
            $this->data[] = $row;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function count()
    {
        if($this->rowCount !== null) {
            return $this->rowCount;
        }

        if($this->cacheEnabled && $this->getCacheAdapter()) {
            $cacheName = @$this->getCacheName('count-' . md5($this->select()->getSqlString()));

            if(($this->rowCount = $this->getCacheAdapter()->getItem($cacheName)) !== null) {
                return $this->rowCount;
            }
        }

        $this->initialize();

        $select = $this->getLoadSelect();

        $select->reset(Select::LIMIT);
        $select->reset(Select::OFFSET);
        $select->reset(Select::ORDER);
        $select->reset(Select::HAVING);
        $select->reset(Select::COLUMNS);

        $select->columns(array('id'));

        $countSelect = new Select();
        $countSelect->columns(array('c' => new Expression('COUNT(*)')));
        $countSelect->from(array('entity_select' => $select));

        $statement = $this->getSql()->prepareStatementForSqlObject($countSelect);
        $result    = $statement->execute();
        $row       = $result->current();

        $this->rowCount = $row['c'];

        if($this->cacheEnabled && $this->getCacheAdapter()) {
            $cacheName = @$this->getCacheName('count-' . md5($this->select()->getSqlString()));
            $this->getCacheAdapter()->setItem($cacheName, $this->rowCount);
            $this->getCacheAdapter()->setTags($cacheName, $this->getCacheTags());
        }

        return $this->rowCount;
    }

    /**
     * @param int $page
     * @param int $rows
     * @param array $options
     * @return Paginator
     */
    public function getPaginator($page = 1, $rows = 10, $options = array())
    {
        $resultSet = new ResultSet();
        $resultSet->setPrototype($this->getPrototype());
        $paginatorAdapter = new DbSelect(
            $this->select(),
            $this->getDbAdapter(),
            $resultSet
        );

        $paginator = new Paginator($paginatorAdapter);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($rows);

        return $paginator;
    }

    protected function getCacheName($name = '') {
        return 'db-entity-collection-' . str_replace('_', '-', $this->table()) . ($name ? '-' . $name : '');
    }

    /**
     * @return array
     */
    protected function getCacheTags()
    {
        $tags = array($this->table());
        foreach($this->joins as $join) {
            $tags = array_merge($tags, $join['name']);
        }
        return $tags;
    }

    /**
     * @return bool
     */
    protected function cacheLoad()
    {
        if(!$this->cacheEnabled || !$this->getCacheAdapter()) {
            return false;
        }

        $cacheName = @$this->getCacheName(md5($this->select()->getSqlString()));


        if($data = $this->getCacheAdapter()->getItem($cacheName)) {
            foreach($data as $row) {
                $this->addEntity($row);
            }
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function cacheSave()
    {
        if(!$this->cacheEnabled || !$this->getCacheAdapter()) {
            return false;
        }

        $data = array();
        foreach($this->cursor as $row) {
            $data[] = $row;
            $this->addEntity($row);
        }

        $cacheName = @$this->getCacheName(md5($this->select()->getSqlString()));

        $this->getCacheAdapter()->setItem($cacheName, $data);
        $this->getCacheAdapter()->setTags($cacheName, $this->getCacheTags());

        return true;
    }

    public function serializeArray($result = array(), $prefix = '')
    {
        return $result;
    }

    public function getPlugin()
    {
        $plugin =  new Collection();
        $plugin->setPrototype($this->getPrototype());
        return $plugin;
    }

    public function setSelect(Select $select)
    {
        $this->rowCount = null;
        $this->data = null;

        return parent::setSelect($select);
    }

    /* Iterator */
    /**
     * @var int
     */
    protected $position = 0;

    public function rewind()
    {
        $this->load();
        if(is_array($this->data)) {
            reset($this->data);
            $this->position = 0;
        } elseif($this->cursor) {
            $this->cursor->rewind();
        }

        return $this;
    }

    /**
     * @return Entity
     */
    public function current()
    {
        if(!is_array($this->data) || !isset($this->data[$this->position])) {
            $entity = clone $this->getPrototype();
            $data = $this->cursor->current();
            $this->data[$this->position] = $entity->rFill($data);
        }

        return $this->data[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        if($this->cursor) {
            $this->cursor->next();
        }

        $this->position++;
    }

    public function valid()
    {
        if (is_array($this->data) && isset($this->data[$this->position])) {
            return true;
        }
        if ($this->cursor instanceof Iterator) {
            return $this->cursor->valid();
        } else {
           return false;
        }
    }
}