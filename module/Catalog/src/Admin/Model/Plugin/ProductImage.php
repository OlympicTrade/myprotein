<?php
namespace CatalogAdmin\Model\Plugin;

use Aptero\Db\Plugin\Image;
use CatalogAdmin\Model\Size;
use CatalogAdmin\Model\Taste;

class ProductImage extends Image
{
    public function getTaste()
    {
        return (new Taste())->setId($this->get('taste_id'));
    }
    
    public function getSize()
    {
        return (new Size())->setId($this->get('size_id'));
    }
}