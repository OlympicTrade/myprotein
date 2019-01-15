<?php

namespace Aptero\Db\Plugin;

use Aptero\Db\Plugin\PluginAbstract;
use Iterator;

class Properties extends PluginAbstract implements Iterator
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

        foreach($this->properties as $name => $property) {
            switch ($property['action']) {
                case self::ACTION_DELETE:
                    $delete = $this->delete();
                    $delete->where(array(
                        $this->parentFiled => $this->getParentId(),
                        'id' => $property['id'],
                    ));

                    $this->execute($delete);
                    break;

                case self::ACTION_INSERT:
                    $insert = $this->insert();
                    $insert->values(array(
                        'value' => $property['value'],
                        $this->parentFiled => $this->getParentId(),
                    ));

                    $this->execute($insert);
                    break;

                case self::ACTION_UPDATE:
                    $update = $this->update();
                    $update->where(array(
                        $this->parentFiled => $this->getParentId(),
                        'id' => $property['id'],
                    ));

                    $update->set(array(
                        'value' => $property['value'],
                    ));
                    $this->execute($update);
                    break;

                case self::ACTION_NONE:
                default:
                    // do nothing
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

    public function addProperty($value, $id = 0)
    {
        $this->load();

        if(!$value) {
            return false;
        }

        $this->changed = true;

        if($id) {
            foreach($this->properties as $key => $property) {
                if($property['id'] == $id) {
                    $this->properties[$key]['value'] = $value;
                    $this->properties[$key]['action'] = self::ACTION_UPDATE;

                    return true;
                }
            }
        }

        $this->properties[] = array(
            'value'  => $value,
            'action' => self::ACTION_INSERT
        );

        return true;
    }

    public function delProperty($id)
    {
        $this->load();

        foreach($this->properties as $key => $property) {
            if($property['id'] == $id) {
                $action = $this->properties[$key]['action'];

                if($action == self::ACTION_UPDATE) {
                    $this->properties[$key]['action'] = self::ACTION_DELETE;
                    $this->changed = true;
                } elseif($action == self::ACTION_NONE) {
                    $this->properties[$key]['action'] = self::ACTION_DELETE;
                    $this->changed = true;
                } elseif($action == self::ACTION_INSERT) {
                    unset($this->properties[$key]);
                }
            }
        }

        return true;
    }

    public function clearProperties()
    {
        $this->load();

        foreach($this->properties as $key => $property) {
            $action = $this->properties[$key]['action'];

            if($action == self::ACTION_UPDATE) {
                $this->properties[$key]['action'] = self::ACTION_DELETE;
                $this->changed = true;
            } elseif($action == self::ACTION_NONE) {
                $this->properties[$key]['action'] = self::ACTION_DELETE;
                $this->changed = true;
            } elseif($action == self::ACTION_INSERT) {
                unset($this->properties[$key]);
            }
        }

        return true;
    }

    /**
     * @param array $rowset
     * @return $this
     */
    public function fill($rowset)
    {
        foreach($rowset as $row) {
            $this->properties[] = array(
                'value'  => $row['value'],
                'id'     => $row['id'],
                'action' => self::ACTION_NONE
            );
        }

        return $this;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getCacheName($name = '') {
        return 'db-plugin-properties-' . str_replace('_', '-', $this->table()) . ($name ? '-' . $name : '');
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
        if(empty($data) || empty($data['props'])) {
            return true;
        }

        var_dump($data['props']);die();

        foreach($data['props'] as $key => $tag) {
            if($key === 'delete') {
                $id = (int) $tag;
                $this->delProperty($id);
                continue;
            }

            if(strpos($key, 'edit-') !== false) {
                $id = (int) substr($key, 5);
            } else {
                $id = 0;
            }

            $this->addProperty($tag, $id);
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

        return array('id' => $prop['id'], 'value' => $prop['value']);
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