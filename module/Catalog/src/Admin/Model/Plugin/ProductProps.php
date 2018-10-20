<?php
namespace CatalogAdmin\Model\Plugin;

use Aptero\Db\Plugin\PluginAbstract;
use Iterator;

use Zend\Db\Sql\Select;

class ProductProps extends PluginAbstract implements Iterator
{
    const ACTION_NONE   = 0;
    const ACTION_UPDATE = 1;
    const ACTION_INSERT = 2;
    const ACTION_DELETE = 3;

    public $properties = array();

    public $catalogId = 0;

    public function setCatalogId($id)
    {
        $this->catalogId = $id;
    }

    public function getCatalogId()
    {
        return $this->catalogId;
    }

    public function load()
    {
        $parentId = $this->getParentId();
        $catalogId = $this->getCatalogId();

        if(!$parentId || !$catalogId) {
            return $this;
        }

        if($this->loaded) {
            return $this;
        }

        $select = clone $this->select();

        $select
            ->from(array('p' => 'products'))
            ->columns(array())
            ->join(array('cu' => 'catalog_properties'), 'p.catalog_id = cu.depend', array('key' => 'value', 'prop_id' => 'id'))
            ->join(array('pp' => 'products_properties'), 'p.id = pp.product_id AND cu.id = pp.property_id', array('value', 'id'), Select::JOIN_LEFT)
            ->where(array('p.id' => $parentId));

        $result = $this->fetchAll($select);

        $this->fill($result);

        $this->loaded = true;

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
                        'product_id'  => $this->getParentId(),
                        'property_id' => $property['pid'],
                    ));

                    $this->execute($delete);
                    break;

                case self::ACTION_INSERT:
                    $insert = $this->insert();
                    $insert->values(array(
                        'product_id'  => $this->getParentId(),
                        'property_id' => $property['pid'],
                        'value' => $property['value'],
                    ));

                    $this->execute($insert);
                    break;

                case self::ACTION_UPDATE:
                    $update = $this->update();
                    $update->where(array(
                        'product_id'  => $this->getParentId(),
                        'property_id' => $property['pid'],
                    ));

                    $update->set(array(
                        'value' => $property['value'],
                    ));
                    $this->execute($update);
                    break;

                case self::ACTION_NONE:
                default:
                    break;
            }

            $this->properties[$name]['action'] = self::ACTION_NONE;
        }

        return true;
    }

    public function remove()
    {
        $delete = $this->delete();
        $delete->where(array(
            'product_id' => $this->getParentId(),
        ));

        $this->execute($delete);

        $this->properties = array();

        return true;
    }

    public function set($key, $value)
    {
        $this->load();

        if (!array_key_exists($key, $this->properties)) {
            return true;
        }

        if ($this->properties[$key]['id']) {
            if (empty($value)) {
                $this->del($key);
            }
            else if ($this->properties[$key]['value'] != $value) {
                $this->properties[$key]['value'] = $value;
                $this->properties[$key]['action'] = self::ACTION_UPDATE;
            }
        } else {
            $this->properties[$key]['value'] = $value;
            $this->properties[$key]['action'] = self::ACTION_INSERT;
        }

        $this->changed = true;

        return true;
    }

    public function get($name)
    {
        $this->load();

        return array_key_exists($name, $this->properties) ? $this->properties[$name]['value'] : '';
    }

    public function del($key)
    {
        $this->load();

        if (!array_key_exists($key, $this->properties)) {
            return true;
        }

        $action = $this->properties[$key]['action'];

        switch ($action)
        {
            case self::ACTION_NONE:
            case self::ACTION_UPDATE:
                $this->properties[$key]['action'] = self::ACTION_DELETE;
                break;

            case self::ACTION_INSERT:
                unset($this->properties[$key]);
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
                'value'  => $row['value'],
                'action' => self::ACTION_NONE,
                'id'     => $row['id'],
                'pid'    => $row['prop_id'],
            );
        }

        return $this;
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

    public function unserializeArray($data)
    {
        if(!is_array($data) || empty($data['props'])) {
            return true;
        }

        foreach($data['props'] as $key => $val) {
            $this->set($key, $val);
        }
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
        $key  = key($this->properties);

        return array('pid' => $prop['pid'], 'key' => $key, 'value' => $prop['value']);
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