<?php

namespace Aptero\Db\Plugin;

use Aptero\Db\Plugin\PluginAbstract;
use Iterator;

class Attributes extends PluginAbstract implements Iterator
{
    const ACTION_NONE   = 0;
    const ACTION_UPDATE = 1;
    const ACTION_INSERT = 2;
    const ACTION_DELETE = 3;

    public $properties = array();

    public function load()
    {
        $parentId = $this->getParentId();

        if(!$parentId) {
            return $this;
        }

        if($this->loaded) {
            return $this;
        }

        if($this->cacheLoad()) {
            $this->loaded = true;
            return $this;
        }

        $select = clone $this->select();

        $select
            ->where(array('t.' . $this->parentFiled => $parentId));

        $result = $this->fetchAll($select);

        $this->fill($result);

        $this->loaded = true;

        $this->cacheSave();

        return $this;
    }

    public function save($transaction = false)
    {
        if(!$this->changed) {
            return true;
        }
        $this->load();

        foreach($this->properties as $name => $property) {
            switch ($property['action']) {
                case self::ACTION_DELETE:
                    $delete = $this->delete();
                    $delete->where(array(
                        $this->parentFiled => $this->getParentId(),
                        'key' => $name
                    ));

                    $this->execute($delete);
                    break;

                case self::ACTION_INSERT:
                    $insert = $this->insert();
                    $insert->values(array(
                        'key' => $name,
                        'value' => $property['value'],
                        $this->parentFiled => $this->getParentId(),
                    ));

                    $this->execute($insert);
                    break;

                case self::ACTION_UPDATE:
                    $update = $this->update();
                    $update->where(array(
                        $this->parentFiled => $this->getParentId(),
                        'key' => $name
                    ));

                    $update->set(array(
                        'value' => $property['value'],
                        $this->parentFiled => $this->getParentId(),
                    ));
                    $this->execute($update);
                    break;

                case self::ACTION_NONE:
                default:
                    break;
            }

            $this->properties[$name]['action'] = self::ACTION_NONE;
        }

        $this->cacheClear();

        return true;
    }

    public function remove()
    {
        $delete = $this->delete();
        $delete->where(array(
            $this->parentFiled => $this->getParentId(),
        ));

        $this->execute($delete);

        $this->properties = array();

        $this->cacheClear();

        return true;
    }

    public function set($name, $value)
    {
        $this->load();

        if (array_key_exists($name, $this->properties)) {
            if (empty($value)) {
                $this->del($name);
            }
            else if ($this->properties[$name]['value'] != $value) {
                $this->properties[$name]['value'] = $value;
                $this->properties[$name]['action'] = self::ACTION_UPDATE;
            }
        }
        else {
            if(!empty($value)) {
                $this->properties[$name] = array(
                    'value'  => $value,
                    'action' => self::ACTION_INSERT
                );
            }
        }

        $this->changed = true;

        return $this;
    }

    public function get($name)
    {
        $this->load();

        return array_key_exists($name, $this->properties) ? $this->properties[$name]['value'] : '';
    }

    public function del($name)
    {
        $this->load();

        if (!array_key_exists($name, $this->properties)) {
            return true;
        }

        $action = $this->properties[$name]['action'];

        switch ($action)
        {
            case self::ACTION_NONE:
            case self::ACTION_UPDATE:
                $this->properties[$name]['action'] = self::ACTION_DELETE;
                break;

            case self::ACTION_INSERT:
                unset($this->properties[$name]);
                break;

            case self::ACTION_DELETE:
                break;
            default:
                break;
        }

        $this->changed = true;
        return true;
    }

    /**
     * @param array $rowset
     * @return $this
     */
    public function fill($rowset)
    {
        foreach($rowset as $row) {
            $this->properties[$row['key']] = array(
                'value' => $row['value'],
                'action' => self::ACTION_NONE,
                'id'     => $row['id'],
            );
        }

        return $this;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getCacheName($name = '') {
        return 'db-plugin-attributes-' . str_replace('_', '-', $this->table()) . ($name ? '-' . $name : '');
    }

    /**
     * @return bool
     */
    protected function cacheLoad()
    {
        if(!$this->cacheEnabled || !$this->getCacheAdapter()) {
            return false;
        }

        $cacheName = @$this->getCacheName($this->getParentId());

        if($this->properties = $this->getCacheAdapter()->getItem($cacheName)) {
            return true;
        } else {
            $this->properties = array();
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

        $data = $this->properties;

        $cacheName = @$this->getCacheName($this->getParentId());

        $this->getCacheAdapter()->setItem($cacheName, $data);
        $this->getCacheAdapter()->setTags($cacheName, array($this->table()));

        return true;
    }

    /**
     * @return bool
     */
    protected function cacheClear()
    {
        if(!$this->getCacheAdapter()) {
            return false;
        }

        $this->getCacheAdapter()->clearByTags(array($this->table()));
        return true;
    }

    /**
     * @param $result
     * @param string $prefix
     * @return array
     */
    public function serializeArray($result = array(), $prefix = '')
    {
        $this->load();

        foreach($this->properties as $key => $val) {
            $result[$prefix . $key] = $val['value'];
        }

        return $result;
    }

    /**
     * @param $data
     * @return bool
     */
    public function unserializeArray($data)
    {
        if(empty($data)) {
            return true;
        }


        if(!isset($data['attrs'])) {
            foreach($data as $key => $val) {
                $this->set($key, $val);
            }

            return true;
        }


        for($i = 0; $i < count($data['keys']); $i++) {
            $this->set($data['keys'][$i], $data['vals'][$i]);
        }

        return true;
    }

    /* Iterator */
    public function rewind()
    {
        $this->load();

        return reset($this->properties);
    }

    public function current()
    {
        $prop = current($this->properties);

        return $prop['value'];
    }

    public function key()
    {
        return key($this->properties);
    }

    public function next()
    {
        return next($this->properties);
    }

    public function valid()
    {
        $key = key($this->properties);
        return ($key !== null && $key !== false);
    }
}