<?php
namespace Catalog\Model;

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
        ]);

        $this->addPlugin('catalog', function($model) {
            $catalog = new Catalog();
            $catalog->setId($model->get('depend'));

            return $catalog;
        });
    }

    public function getUrl()
    {
        return $this->getPlugin('catalog')->getUrl() . $this->get('url') . '/';
    }
}