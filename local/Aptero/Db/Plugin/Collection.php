<?php
namespace Aptero\Db\Plugin;

use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityCollection;

class Collection extends EntityCollection implements PluginInterface
{
    /**
     * @var Entity
     */
    protected $parent = null;

    /**
     * @var string
     */
    protected $parentFiled = 'depend';

    protected $props = array();

    public function unserializeArray($data)
    {
        $data = $data['collection'];

        $add = $data['add'];
        if(empty($add)) {
            $this->props = array();
            return true;
        }

        $count = (count(current($add)));

        for($i = 0; $i < $count; $i++) {
            $row = array();
            foreach($add as $key => $val) {
                $row[$key] = $val[$i];
            }
            $this->props[] = $row;
        }

        return true;
    }

    public function save()
    {
        $ids = array();

        foreach($this->props as $row) {
            $entity = clone $this->getPrototype();

            $row[$this->getParentFiled()] = $this->getParent()->getId();
            $entity->setId($row['id'])->load();
            $entity->setVariables($row)->save();

            $ids[] = $entity->getId();
        }

        /*$delete = $this->delete();
        $delete->where(array($this->getParentFiled() => $this->getParent()->getId()));

        if($ids) {
            $delete->where->notIn('id', $ids);
        }

        $this->execute($delete);*/

        $collectionToDel = $this->getPrototype()->getCollection();
        $collectionToDel->select()->where(array($this->getParentFiled() => $this->getParent()->getId()));

        if($ids) {
            $collectionToDel->select()->where->notIn('id', $ids);
        }

        $collectionToDel->remove();


        return $this;
    }

    public function rFill($data)
    {

    }

    public function getParentFiled()
    {
        return $this->parentFiled;
    }

    public function setParentFiled($parentFiled)
    {
        $this->parentFiled = $parentFiled;
        return $this;
    }

    public function setParentId($id)
    {
        $this->select()->where(array($this->parentFiled => $id));
        return $this;
    }

    public function setParent(Entity $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }
}