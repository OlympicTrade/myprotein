<?php
namespace BalanceAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;
use BalanceAdmin\Form\StatisticForm;

class StatisticController extends AbstractActionController
{
    public function indexAction()
    {
        $form = new StatisticForm();

        $data = $_GET;

        if(!$data) {
            $dt = \DateTime::createFromFormat('Y-m-d', date('Y-m-01'));
            
            $data['date_from'] = $dt->format('Y-m-01');
            $data['date_to']   = $dt->modify('+1 month')->modify('-1 day')->format('Y-m-d');
            $data['type']      = 'assets';
            $data['interval']  = 'day';
        }

        $form->setData($data)->isValid();
        $this->view->setVariables(array(
            'cashStatistic' => $this->getService()->getCashStatistic($form->getData()),
            'saleStatistic' => $this->getService()->getSaleStatistic($form->getData()),
            'form'      => $form,
        ));

        $this->viewHelper('headScript')
            ->appendFile('/engine/js/ckfinder/ckfinder.js')
            ->appendFile('/engine/js/jquery/spellchecker.js')
            ->appendFile('/engine/js/ckeditor/ckeditor.js')
            ->appendFile('/engine/js/form.js');

        return $this->view;
    }
}