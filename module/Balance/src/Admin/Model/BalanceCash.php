<?php
namespace BalanceAdmin\Model;

use Aptero\Db\Entity\Entity;
use Zend\Session\Container as SessionContainer;

class BalanceCash extends Entity
{
    public function __construct()
    {
        $this->setTable('balance_cash');

        $this->addProperties(array(
            'cash'      => array(),
        ));
    }
}