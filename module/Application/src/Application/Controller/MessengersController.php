<?php
namespace Application\Controller;

use Aptero\Messenger\Messenger;
use Aptero\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class MessengersController extends AbstractActionController
{
    public function indexAction()
    {
        return new JsonModel([
            'status'         => 0,
            'status_message' => 'ok',
            'event_types'    => ['delivered', 'seen']
        ]);
    }

    public function webhookAction()
    {
        $msgr = new Messenger();
        $result = $msgr->webhook('https://myprotein.spb.ru/messengers/?type=viber');
        dd($result);
    }

    public function sendAction()
    {
        $msgr = new Messenger();
        $result = $msgr->send('89522872998', 'my first test message');
        dd($result);
    }
}