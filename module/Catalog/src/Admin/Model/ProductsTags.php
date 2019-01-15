<?php
namespace CatalogAdmin\Model;

use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityHierarchy;

class ProductsTags extends EntityHierarchy
{
    public function __construct()
    {
        $this->setTable('products_tags');

        $this->addProperties([
            'depend'  => [],
            'name'    => [],
        ]);
    }
}