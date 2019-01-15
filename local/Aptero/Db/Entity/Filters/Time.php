<?php
namespace Aptero\Db\Entity\Filters;

use Aptero\Date\Date;

class Time extends AbstractFilter
{
    /**
     * @var \DateTime null
     */
    protected $value = null;

    public function set($value)
    {
        if($value instanceof \DateTime) {
            $this->value = $value;
        }

        $this->value = Date::parseToDt($value, 'time');

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
            return $this->value = new \DateTime();
        }

        $this->value = Date::parseToDt($this->source, 'time');

        return $this->value;
    }

    public function serialize()
    {
        $this->source = $this->value->format('H:i:s');

        return $this->source;
    }
}