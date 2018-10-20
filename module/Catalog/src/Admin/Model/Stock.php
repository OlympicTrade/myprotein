<?php
namespace CatalogAdmin\Model;

use Aptero\Db\Entity\Entity;

class Stock extends Entity
{
    public function __construct()
    {
        $this->setTable('products_stock');

        $this->addProperties(array(
            'product_id'   => array(),
            'size_id'      => array(),
            'taste_id'     => array(),
            'count'        => array(),
        ));
    }
}