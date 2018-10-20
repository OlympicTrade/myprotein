<?php
namespace Catalog\Model;

use Aptero\Db\Entity\Entity;

class Size extends Entity
{
    public function __construct()
    {
        $this->setTable('products_size');

        $this->addProperties(array(
            'depend'      => [],
            'name'        => [],
            'price'       => [],
            'weight'      => [],
        ));
    }
}