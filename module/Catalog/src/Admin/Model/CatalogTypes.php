<?php
namespace CatalogAdmin\Model;

use Aptero\Db\Entity\Entity;

class CatalogTypes extends Entity
{
    public function __construct()
    {
        $this->setTable('catalog_types');

        $this->addProperties([
            'depend'      => [],
            'name'        => [],
            'short_name'  => [],
            'ya_cat_name' => [],
            'url'         => [],
            'title'       => [],
            'description' => [],
            'sort'        => [],
        ]);
    }
}