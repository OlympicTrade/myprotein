<?php
namespace Aptero\Events\Notices;

class AbstractNotice
{
    /**
     * @var array
     */
    protected $variables  = array();

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var array | string
     */
    protected $address = array();

    /**
     * @var string
     */
    protected $message = array();

    /**
     * @param $options array
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param $variables array
     * @return $this
     */
    public function setVariables($variables)
    {
        $this->variables = $variables;

        return $this;
    }

    /**
     * @param $address array|string
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @param $message string
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return bool
     */
    public function send()
    {
        return true;
    }
}