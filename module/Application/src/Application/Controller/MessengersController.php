<?php
namespace Application\Controller;

use Aptero\Messenger\Messenger;
use Aptero\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class MessengersController extends AbstractActionController
{
    public function indexAction()
    {
        return new JsonModel(['status' => 1]);
    }

    public function webhookAction()
    {
        $msgr = new Messenger();
        $result = $msgr->webhook('https://myprotein.spb.ru/messengers/?type=viber');
        dd($result);
    }
}
