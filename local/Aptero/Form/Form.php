<?php
namespace Aptero\Form;

use Zend\Form\Form as ZendForm;
use Aptero\Db\Entity\Entity;

class Form extends ZendForm {

    /**
     * @var Entity
     */
    protected $model = null;

    /**
     * @param Entity $model
     */
    public function setModel($model) {
        $this->model = $model;
    }

    /**
     * @return Entity
     */
    public function getModel()
    {
        return $this->model;
    }
}