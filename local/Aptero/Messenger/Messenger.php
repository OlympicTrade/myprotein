<?php
namespace Aptero\Messenger;

use Zend\Json\Json;

class Messenger
{
    const API_URL = 'https://chatapi.viber.com/pa/';

    private $token = "4a6b35baab27d663-5b1a13f69254092f-d6679b3a17725073";

    /*public function send($sender, $text)
    {
        $data['from']   = $from;
        $data['sender'] = $sender;
        $data['type']   = 'text';
        $data['text']   = $text;

        return $this->api('post', $data);
    }*/

    public function webhook($hookUrl)
    {
        return $this->api('set_webhook', [
            'auth_token' => self::API_URL,
            'url'        => $hookUrl,

        ]);
    }

    private function api($method, $data)
    {
        $url = self::API_URL . $method;

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