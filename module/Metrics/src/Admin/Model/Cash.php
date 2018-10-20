<?php
namespace MetricsAdmin\Model;

use Aptero\Db\Entity\Entity;
use Zend\Session\Container as SessionContainer;

class Cash extends Entity
{
    const TYPE_CORRECTION = 9;
    const TYPE_EXPENSES   = 7;
    const TYPE_SUPPLIES   = 1;
    const TYPE_CREDIT     = 8;
    const TYPE_FROZEN     = 6;

    static public $flowTypes = [
        self::TYPE_CORRECTION  => 'Поправки',
        self::TYPE_EXPENSES    => 'Расходы',
        self::TYPE_SUPPLIES    => 'Закупки',
        self::TYPE_CREDIT      => 'Кредит',
    ];

    public function __construct()
    {
        $this->setTable('balance_flow');

        $this->addProperties([
            'name'      => [],
            'desc'      => [],
            'price'     => [],
            'type'      => [],
            'status'    => [],
            'date'      => ['default' => date("Y-m-d")],
        ]);
    }
}