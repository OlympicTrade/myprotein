<?php
namespace Aptero\Db\Plugin;

use Aptero\Db\AbstractDb;
use Aptero\Db\Entity\Entity;

class PluginAbstract extends AbstractDb implements PluginInterface
{
    /**
     * @var Entity
     */
    protected $parent = null;

    /**
     * @var string
     */
    protected $parentFiled = 'depend';

    /**
     * @var bool
     */
    protected $loaded = false;

    /**
     * @var bool
     */
    protected $changed = false;

    /**
     * @return bool
     */
    public function load(){
        return true;
    }

    /**
     * @return bool
     */
    public function save(){
        return true;
    }

    /**
     * @return bool
     */
    public function remove(){
        return true;
    }

    /**
     * @param array $data
     * @return PluginAbstract
     */
    public function rFill($data)
    {
        return $this->fill($data);
    }

    /**
     * @param array $data
     * @return PluginAbstract
     */
    public function fill($data)
    {
        return $this;
    }

    /**
     * @param \Aptero\Db\Entity\Entity $parent
     * @return Comments
     */
    public function setParent(Entity $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return \Aptero\Db\Entity\Entity
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parent->getId();
    }

    /**
     * @return string
     */
    public function getParentFiled()
    {
        return $this->parentFiled;
    }

    /**
     * @param $parentFiled
     * @return $this
     */
    public function setParentFiled($parentFiled)
    {
        $this->parentFiled = $parentFiled;
        return $this;
    }

    /**
     * @param $result
     * @param string $prefix
     * @return array
     */
    public function serializeArray($result = [], $prefix = '')
    {
        return array();
    }

    /**
     * @param array $data
     * @return bool
     */
    public function unserializeArray($data)
    {
        return true;
    }
}