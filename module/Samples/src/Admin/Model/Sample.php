<?php
namespace SamplesAdmin\Model;

use Aptero\Db\Entity\Entity;
use Zend\Session\Container as SessionContainer;

class Sample extends Entity
{
    public function __construct()
    {
        $this->setTable('samples');

        $this->addProperties([
            'name'        => [],
            'time_update' => [],
            'sort'        => [],
        ]);
    }
}