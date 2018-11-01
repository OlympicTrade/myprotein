<?php
namespace MetricsAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;
use MetricsAdmin\Form\MetricsForm;

class MetricsController extends AbstractActionController
{
    public function periodAction()
    {
        $form = new MetricsForm();

        $data = $_GET;

        if(!$data) {
            $dt = \DateTime::createFromFormat('Y-m-d', date('Y-m-01'));
            $data['date_from'] = $dt->format('Y-m-01');
            $data['date_to']   = $dt->modify('+1 month')->modify('-1 day')->format('Y-m-d');
            $data['type']      = 'assets';
            $data['interval']  = 'day';
        }

        $form->setData($data)->isValid();

        $this->view->setVariables([
            'balanceStatistic' => $this->getService()->getBalanceStatistic($form->getData()),
            'saleStatistic'    => $this->getService()->getSaleStatistic($form->getData()),
            'adwordsStatistic' => $this->getService()->getAdwordsStatistic($form->getData() + ['group' => true]),
            'form'      => $form,
        ]);

        /*$this->viewHelper('headScript')
            ->appendFile('/engine/js/ckfinder/ckfinder.js')
            ->appendFile('/engine/js/jquery/spellchecker.js')
            ->appendFile('/engine/js/ckeditor/ckeditor.js')
            ->appendFile('/engine/js/form.js');*/

        return $this->view;
    }

    public function indexAction()
    {
        $this->generate();
        $this->view->setVariables([
            'metrics'          => $this->getService()->getMetrics(),
        ]);

        return $this->view;
    }
}