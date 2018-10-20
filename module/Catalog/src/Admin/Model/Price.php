<?php
namespace CatalogAdmin\Model;

use Aptero\Db\Entity\Entity;

class Price extends Entity
{
    public function __construct()
    {
        $this->setTable('products_size');

        $this->addProperties(array(
            'depend'      => array(),
            'name'        => array(),
            'price'       => array(),
        ));
    }
}