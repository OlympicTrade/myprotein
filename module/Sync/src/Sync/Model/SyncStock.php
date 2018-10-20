<?php
namespace Sync\Model;

use Aptero\Db\Entity\Entity;

class SyncStock extends Entity
{
    public function __construct()
    {
        $this->setTable('sync_stock_diff');

        $this->addProperties([
            'product_id'    => [],
            'size_id'       => [],
            'taste_id'      => [],
            'diff'          => [],
            'time_create'   => [],
        ]);
    }
}