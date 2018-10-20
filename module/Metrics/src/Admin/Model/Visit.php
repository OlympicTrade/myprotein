<?php
namespace MetricsAdmin\Model;

use Aptero\Db\Entity\Entity;

class Visit extends Entity
{
    public function __construct()
    {
        $this->setTable('metrics_visits');

        $this->addProperties([
            'clients'   => [],
            'sessions'  => [],
            'views'     => [],
            'platform'  => [],
            'date'      => [],
        ]);
    }
}