<?php
namespace ApplicationAdmin\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Mvc\Controller\Plugin\FlashMessenger;

class Messenger extends AbstractHelper
{
    public function __invoke($messages)
    {
        $flashMessenger = new FlashMessenger();

        $messages = $flashMessenger->getMessages();

        $html =
            '<div class="message-box"></div>';

        if(empty($messages)) {
            return $html;
        }

        $html .=
            '<script>'
            .'$(function(){';

        $i = 0;
        foreach($messages as $message) {
            if(is_string($message)) {
                $messageText = $message;
                $messageType = '';
            } else {
                $messageText = $message['text'];
                $messageType = $message['type'];
            }

            $html .=
                'setTimeout('
                    .'function(){
                     var message = new Message();
                     message.setMessage("' . $messageText . '", "' . $messageType . '")}'
                    .',' . ($i * 500)
                .');';

            $i++;
        }
        $html .=
            '})'
            .'</script>';

        return $html;
    }
}
?>