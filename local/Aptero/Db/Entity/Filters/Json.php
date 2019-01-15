<?php
namespace Aptero\Db\Entity\Filters;

use \Zend\Json\Json as ZJson;

class Json extends AbstractFilter
{
    /**
     * @var \stdClass null
     */
    protected $value = null;

    public function set($value)
    {
        if($value instanceof \StdClass) {
            $this->value = $value;
        } else {
            $this->setSource($value);
        }

        $this->isChanged(true);
        return $this;
    }

    public function get()
    {
        $this->isChanged(true);
        if($this->value) return $this->value;
        return $this->unserialize();
    }

    public function unserialize()
    {
        if(!$this->source) {
            return $this->value = new \stdClass();
        }

        try {
            $this->value = ZJson::decode($this->source);
        } catch (\Exception $e) {
            $this->value = new \stdClass();
        }

        return $this->value;
    }

    public function serialize()
    {
        $this->source = ZJson::encode($this->value);

        return $this->source;
    }
}