<?php

namespace Aptero\Yandex;

use Application\Model\Settings;
use Yandex\OAuth\OAuthClient;
use Zend\Json\Json;
use Zend\Session\Container;

class Client
{
    static protected $instance = null;

    static public function getInstance()
    {
        if(!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    protected $redirectUrl = 'https://olympic-trade.ru/admin/catalog/orders/list/';
    protected $clientKey   = '7c2f66657dc14800948067d93b9f64ac';
    protected $clientPass  = '5832900ece9c40d3ba8c7a12bda35405';
    protected $token = null;

    public function getToken()
    {
        return $this->token;
    }

    public function getClientId()
    {
        return $this->clientKey;
    }

    public function getClientPass()
    {
        return $this->clientPass;
    }

    public function auth($options = [])
    {
        if ($this->token) {
            return $this;
        }

        $credentialsPath = DATA_DIR . '/keys/yandex/credentials.json';

        if(file_exists($credentialsPath)) {
            $credentialsData = Json::decode(file_get_contents($credentialsPath));
            if($credentialsData->expires > date('Y-m-d H:i:s')) {
                $this->token = $credentialsData->token;
                return $this;
            }
        }

        if(!$code = $_GET['code']) {
            $redirect = 'https://olympic-trade.ru/redirect/';
            $authUrl = 'https://oauth.yandex.ru/authorize?response_type=code&client_id=' . $this->clientKey . '&redirect_uri=' . $redirect;

            $session = new Container();
            $settings = Settings::getInstance()->get('domain');
            $session->redirect = $settings . $options['redirect'];

            header("Location: " . $authUrl);
            die();
        }

        $oaClient = new OAuthClient($this->clientKey, $this->clientPass);
        $this->token = $oaClient->requestAccessToken($code)->getAccessToken();

        $credentialsData = [
            'token'   => $this->token,
            'expires' => (new \DateTime())->modify('+365 days')->format('Y-m-d H:i:s'),
        ];

        file_put_contents($credentialsPath, Json::encode($credentialsData));

        return $this;
    }
}