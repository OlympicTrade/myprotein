<?php
namespace Application\Controller;

use Aptero\Messenger\Messenger;
use Aptero\Mvc\Controller\AbstractActionController;

class MessengersController extends AbstractActionController
{
    public function indexAction()
    {
        $type = $this->params()->fromRoute('messenger');

        $msgr = new Messenger();
        $result = $msgr->webhook('https://myprotein.spb.ru/messengers/viber/');
        dd($result);
    }
}
