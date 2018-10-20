<?php
namespace Catalog\Model;

use Aptero\Db\Entity\Entity;

class Taste extends Entity
{
    public function __construct()
    {
        $this->setTable('products_taste');

        $this->addProperties(array(
            'depend'        => array(),
            'name'          => array(),
            'coefficient'   => array(),
            'stock'         => array('virtual' => true),
        ));
    }
}