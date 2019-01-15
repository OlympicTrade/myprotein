<?php
namespace Aptero\Db;

use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Adapter\Adapter as DbAdapter;

use Aptero\Cache\CacheAwareInterface;
use Zend\Cache\Storage\Adapter\AbstractAdapter as CacheAdapter;

use Zend\Db\TableGateway\Feature\GlobalAdapterFeature as StaticDbAdapter;
use Aptero\Cache\Feature\GlobalAdapterFeature as StaticCacheAdapter;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Update;

class AbstractDb implements AdapterAwareInterface, CacheAwareInterface
{
    /**
     * @var Sql
     */
    static protected $sql = null;

    /**
     * @var bool
     */
    protected $isInitialized = false;

    /**
     * @var DbAdapter
     */
    protected $adapter = null;

    /**
     * @var string
     */
    protected $table = null;

    /**
     * @var string
     */
    protected $primary = 'id';

    /**
     * @var Select
     */
    protected $select = null;

    /**
     * @var CacheAdapter
     */
    protected $cache = null;

    /**
     * @var bool
     */
    protected $cacheEnabled = false;

    /**
     * @var bool
     */
    protected $transaction = false;

    /**
     * @var bool
     */
    protected $loaded = false;

    public function __construct($options = [])
    {

    }

    /**
     * @param CacheAdapter $cache
     */
    public function setCacheAdapter(CacheAdapter $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return CacheAdapter
     */
    public function getCacheAdapter()
    {
        if (!$this->cache) {
            $this->setCacheAdapter(StaticCacheAdapter::getStaticAdapter('data'));
        }

        return $this->cache;
    }

    public function enableCache()
    {
        $this->cacheEnabled = true;

        return $this;
    }

    public function disableCache()
    {
        $this->cacheEnabled = false;

        return $this;
    }

    /**
     * @return bool
     * @throws Exception\RuntimeException
     */
    public function initialize()
    {
        if($this->isInitialized) {
            return true;
        }

        if (!self::$sql instanceof Sql) {
            self::$sql = new Sql($this->getDbAdapter());
        }

        if(empty($this->table)) {
            $this->table = $this->calcTableName();
        }

        $this->isInitialized = true;

        return true;
    }

    /**
     * @param DbAdapter $adapter
     * @return $this
     */
    public function setDbAdapter(DbAdapter $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * @return null|DbAdapter
     */
    public function getDbAdapter()
    {
        if (!$this->adapter) {
            $this->setDbAdapter(StaticDbAdapter::getStaticAdapter());
        }

        return $this->adapter;
    }

    /**
     * Create table name from class name (Module\Model\ClassName => class_name)
     *
     * @return string
     */
    protected function calcTableName()
    {
        $fullClassName= explode('\\', get_called_class());
        $className  = array_pop($fullClassName);

        $ptn = "/([A-Z][a-z]*)/";

        $table = preg_replace_callback($ptn, function($match) {
            return '_' . strtolower($match[0]);
        }, $className);

        $table = ltrim($table, '_');

        return $table;
    }

    public function table()
    {
        if(empty($this->table)) {
            $this->table = $this->calcTableName();
        }

        return $this->table;
    }

    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    public function transactionEnable()
    {
        $this->transaction = true;
    }

    public function transactionDisable()
    {
        $this->transaction = false;
    }

    public function setSelect(Select $select)
    {
        $this->loaded = false;
        $this->select = $select;

        return $this;
    }

    public function clearSelect()
    {
        if(!$this->select) {
            return $this;
        }

        $this->select->reset(Select::LIMIT);
        $this->select->reset(Select::OFFSET);
        $this->select->reset(Select::ORDER);
        $this->select->reset(Select::WHERE);

        return $this;
    }

    /**
     * @return Sql
     */
    public function getSql()
    {
        return self::$sql;
    }

    /**
     * @return Select
     */
    public function select()
    {
        if(!$this->select) {
            $this->initialize();
            $this->select = $this->getSql()->select()
                ->from(array('t' => $this->table()), array('*'));
        }

        return $this->select;
    }

    public function d($die = true, $echo = true)
    {
        $dump = $this->getSql()->buildSqlString($this->select());
        
        if($echo) {
            echo $dump;
        }

        if($die) {
            die();
        }
        
        return $dump;
    }

    /**
     * @return bool
     */
    public function isLoaded()
    {
        return $this->loaded;
    }

    /**
     * @return Insert
     */
    public function insert()
    {
        $this->initialize();

        return $this->getSql()->insert()->into($this->table());
    }

    /**
     * @return Update
     */
    public function update()
    {
        $this->initialize();

        return $this->getSql()->update()->table($this->table());
    }

    /**
     * @return Delete
     */
    public function delete()
    {
        $this->initialize();

        return $this->getSql()->delete()->from($this->table());
    }

    /**
     * @param $query
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function execute($query)
    {
        $statement = $this->getSql()->prepareStatementForSqlObject($query);
        return $statement->execute();
    }

    /**
     * @param Select $select
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function fetchAll(Select $select)
    {
        $this->initialize();

        $statement = $this->getSql()->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    /**
     * @param Select $select
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function fetchRow(Select $select)
    {
        $this->initialize();

        $select->limit(1);

        $statement = $this->getSql()->prepareStatementForSqlObject($select);
        return $statement->execute()->current();
    }

    public function __sleep()
    {
        $this->adapter = null;
        $this->isInitialized = false;
    }
}