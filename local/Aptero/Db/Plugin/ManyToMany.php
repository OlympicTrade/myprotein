<?php
namespace Aptero\Db\Plugin;

use Aptero\Db\Plugin\PluginAbstract;
use Iterator;

use Zend\Db\Sql\Select;

class ManyToMany extends PluginAbstract implements Iterator
{
    const ACTION_NONE   = 0;
    const ACTION_UPDATE = 1;
    const ACTION_INSERT = 2;
    const ACTION_DELETE = 3;

    public $properties = array();

    protected $table1Id = 0;
    protected $table1Name  = '';
    protected $table1Field = ''; //Default = table1Name + _id
    protected $table1Join = true;

    protected $table2Id = 0;
    protected $table2Name  = '';
    protected $table2Field = '';
    protected $table2Join = true;

    public function setTable1Id($id)
    {
        $this->table1Id = $id;

        return $this;
    }

    public function setTable1($table)
    {
        $this->table1Name = $table;
        $this->table1Field = $table . '_id';

        return $this;
    }

    public function setTable1Join($isJoin)
    {
        $this->table1Join = $isJoin;

        return $this;
    }

    public function setTable2Id($id)
    {
        $this->table1Id = $id;

        return $this;
    }

    public function setTable2($table)
    {
        $this->table2Name = $table;
        $this->table2Field = $table . '_id';

        return $this;
    }

    public function setTable2Join($isJoin)
    {
        $this->table2Join = $isJoin;

        return $this;
    }

    public function load()
    {
        $parentId = $this->getParentId();

        if(!$parentId) {
            return $this;
        }

        if($this->loaded) {
            return $this;
        }

        if(!$this->table1Id || !$this->table2Id) {
            new \Exception('Table ID is not specified');
        }

        $select = $this->getSelect()
            ->columns(array('id', $this->table1Field, $this->table2Field));

        if($this->table1Join) {
            $select->join(array('t1' => $this->table1Name), 't1.id = t.' . $this->table1Field, array('key' => 'name'), Select::JOIN_LEFT);
        }

        if($this->table2Join) {
            $select->join(array('t2' => $this->table2Name), 't2.id = t.' . $this->table2Field, array('value' => 'name'), Select::JOIN_LEFT);
        }

        /*if($this->table1Id) {
            $select->where(array('t.' . $this->table1Field => $this->table1Id));
        }

        if($this->table2Id) {
            $select->where(array('t.' . $this->table2Field => $this->table2Id));
        }*/

        $select->where(array($this->table1Field => $parentId));

       /* var_dump($parentId);die($select->getSqlString());
        die('asdasd');*/

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

        foreach($this->properties as $key => $property) {
            switch ($property['action']) {
                case self::ACTION_DELETE:
                    $delete = $this->delete();
                    $delete->where(array(
                        'id'  => $property['id'],
                    ));

                    $this->execute($delete);
                    break;

                case self::ACTION_INSERT:
                    $insert = $this->insert();
                    $insert->values(array(
                        $this->table1Field  => $property['t1'],
                        $this->table2Field  => $property['t2'],
                    ));

                    $this->execute($insert);
                    break;

                case self::ACTION_UPDATE:
                    $update = $this->update();
                    $update->where(array(
                        'id'  => $property['id'],
                    ));

                    $update->set(array(
                        $this->table1Field  => $property['t1'],
                        $this->table2Field  => $property['t2'],
                    ));
                    $this->execute($update);
                    break;

                case self::ACTION_NONE:
                default:
                    break;
            }

            $this->properties[$key]['action'] = self::ACTION_NONE;
        }

        return true;
    }
/*
    public function remove()
    {
        $delete = $this->delete();
        $delete->where(array(
            $this->parentFiled => $this->getParentId(),
        ));

        $this->execute($delete);

        $this->properties = array();

        return true;
    }
*/
    public function set($value)
    {
        $this->load();

        if(is_array($value)) {
            foreach($value as $val) {
                $this->set($val);
            }
            return $this;
        }

        if($this->table1Id) {
            $t1 = $this->table1Id;
            $t2 = (int) $value;
        } else {
            $t1 = (int) $value;
            $t2 = $this->table2Id;
        }

        $key = $t1 . $t2;

        if ($this->properties[$key]['id']) {
            if (empty($value)) {
                $this->properties[$key]['action'] = self::ACTION_DELETE;
            } else {
                $this->properties[$key]['t1'] = $t1;
                $this->properties[$key]['t2'] = $t2;
                $this->properties[$key]['action'] = self::ACTION_UPDATE;
            }
        } else {
            if (!empty($value)) {
                $this->properties[$key]['t1'] = $t1;
                $this->properties[$key]['t2'] = $t2;
                $this->properties[$key]['action'] = self::ACTION_INSERT;
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

    public function clear()
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

        return $this;
    }

    /**
     * @param array $rowset
     * @return $this
     */
    public function fill($rowset)
    {
        foreach($rowset as $row) {
            $key = $row[$this->table1Field] . $row[$this->table2Field];

            $this->properties[$key] = array(
                'action' => self::ACTION_NONE,
                'id'     => $row['id'],
                't1'     => $row[$this->table1Field],
                't2'     => $row[$this->table2Field],
            );

            if($this->table1Join) {
                $this->properties[$key]['key'] = $row['key'];
            }

            if($this->table2Join) {
                $this->properties[$key]['value'] = $row['value'];
            }
        }

        return $this;
    }

    /**
     * @param $result
     * @param string $prefix
     * @return array
     */
    public function serializeArray($result, $prefix = '')
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
        if(empty($data) || empty($data['val'])) {
            return true;
        }

        foreach($data['val'] as $val) {
            $this->set($val);
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

        $result = array(
            $this->table1Field => $prop['t1'],
            $this->table2Field => $prop['t2'],
        );

        if($this->table1Join) {
            $result['key'] = $prop['key'];
        }

        if($this->table2Join) {
            $result['value'] = $prop['value'];
        }

        return $result;
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