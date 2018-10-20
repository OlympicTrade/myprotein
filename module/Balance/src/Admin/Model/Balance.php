<?php
namespace BalanceAdmin\Model;

use Aptero\Db\Entity\Entity;
use Zend\Session\Container as SessionContainer;

class Balance extends Entity
{
    public function __construct()
    {
        $this->setTable('balance');

        $this->addProperties(array(
            'products_cash'  => array(),
            'money_cash'     => array(),
            'orders_cash'    => array(),
            'orders_count'   => array(),
            'date'           => array('default' => date("Y-m-d")),
        ));
    }
}