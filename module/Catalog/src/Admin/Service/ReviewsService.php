<?php
namespace CatalogAdmin\Service;

use Aptero\Service\Admin\TableService;

class ReviewsService extends TableService
{

    public function setListOrder($collection, $sort, $direct)
    {
        $sort = $sort ? $sort : 'status';
        $direct = $direct ? $direct : 'down';

        return parent::setListOrder($collection, $sort, $direct);
    }
}