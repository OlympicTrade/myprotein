<?php
namespace Balance\Controller;

use Aptero\Mvc\Controller\AbstractActionController;

class BalanceController extends AbstractActionController
{
    public function updateBalanceAction()
    {
        $this->getCashService()->updateBalance();

        return $this->send404();
    }

    /**
     * @return \BalanceAdmin\Service\CashService
     */
    public function getCashService()
    {
        return $this->getServiceLocator()->get('BalanceAdmin\Service\CashService');
    }
}