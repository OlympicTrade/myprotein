<?php
namespace Aptero\Messenger;

use Zend\Json\Json;

class Messenger
{
    const API_URL = 'https://chatapi.viber.com/pa/';

    private $token = "4a6b35baab27d663-5b1a13f69254092f-d6679b3a17725073";

    public function send($receiver, $text)
    {
        return $this->api('post', [
            'type'       => 'text',
            'receiver'   => $receiver,
            'text'       => $text,
        ]);
    }

    public function setWebhook($hookUrl)
    {
        return $this->api('set_webhook', [
            'url'        => $hookUrl,
        ]);
    }

    public function receiver($hookUrl)
    {
        $request = file_get_contents("php://input");
        $in   = Json::decode($request);

        switch ($in['event']) {
            case 'webhook':
                die(Json::encode([
                    'status'         => 0,
                    'status_message' => 'ok',
                    'event_types'    => ['delivered', 'seen']
                ]));
                break;
            case 'message':
                $data['text'] = 'The message to send to user ' . $in['sender']['name'];

                return $this->api('send_message', [
                    'type'      => 'text',
                    'receiver'  => $in['sender']['id'],
                    'text'      => $in['message']['id'],
                ]);
                break;
            default: //subscribed, conversation_started,...
        }
    }

    private function api($method, $data)
    {
        $url = self::API_URL . $method;

        $data['auth_token'] = self::API_URL;

        $context  = stream_context_create([
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\nX-Viber-Auth-Token: ". $this->token . "\r\n",
                'method'  => 'POST',
                'content' => Json::encode($data)
            ]
        ]);

        $response = file_get_contents($url, false, $context);

        return Json::decode($response);
    }
}