<?php
namespace Aptero\Db\Entity\Filters;

use Aptero\Date\Date as ADate;

class Date extends AbstractFilter
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

        $this->value = ADate::parseToDt($value, 'date');

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

        $this->value = ADate::parseToDt($this->source, 'date');

        return $this->value;
    }

    public function serialize()
    {
        $this->source = $this->value->format('Y-m-d');

        return $this->source;
    }
}