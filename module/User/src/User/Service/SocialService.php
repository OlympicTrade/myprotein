<?php

namespace User\Service;

use Aptero\Service\AbstractService;
use SocialAuther\SocialAuther;

class SocialService extends AbstractService
{
    public $adaptersConfig = array(
        'vk' => array(
            'client_id'     => '5477096',
            'client_secret' => 'pv8S0LnX7tQu6gfHzkKL',
            'redirect_uri'  => 'http://myprotein.spb.ru/user/login-social/vk/'
        ),
        'odnoklassniki' => array(
            'client_id'     => '',
            'client_secret' => '',
            'redirect_uri'  => 'http://myprotein.spb.ru/user/login-social/',
            'public_key'    => 'CBADCBMKABABABABA'
        ),
        'mailru' => array(
            'client_id'     => '',
            'client_secret' => '',
            'redirect_uri'  => 'http://myprotein.spb.ru/user/login-social/'
        ),
        'yandex' => array(
            'client_id'     => '0067a41d62aa45a7ab29bbec96c3479d',
            'client_secret' => '0bb1d8f584c145a1b0b6c32531f2d25d',
            'redirect_uri'  => 'http://myprotein.spb.ru/user/login-social/yandex/'
        ),
        'google' => array(
            'client_id'     => '479300270699-7rhic81mbkqkmulqu4rnrhrqkjo1ogdd.apps.googleusercontent.com',
            'client_secret' => '-K1Pje0YV02QzCksptUSwtQD',
            'redirect_uri'  => 'http://myprotein.spb.ru/user/login-social/google/'
        ),
        'facebook' => array(
            'client_id'     => '1613673778927065',
            'client_secret' => '964df98734d687a00c14ddd4c8e9e71e',
            'redirect_uri'  => 'http://myprotein.spb.ru/user/login-social/facebook/'
        )
    );

    /**
     * @var array
     */
    protected $adapters = [];

    /**
     * @param $adapter
     * @return $this|bool
     */
    public function setAdapter($adapter)
    {
        if(!isset($this->adaptersConfig[$adapter])) {
            return false;
        }

        $settings = $this->adaptersConfig[$adapter];
        $class = 'SocialAuther\Adapter\\' . ucfirst($adapter);

        $this->adapters[$adapter] = new $class($settings);
        
        return $this->adapters[$adapter];
    }

    /**
     * @return array
     */
    public function getAdapters()
    {
        if($this->adapters) {
            return $this->adapters;
        }

        foreach ($this->adaptersConfig as $adapter => $settings) {
            if(!empty($this->adapters[$adapter])) {
                continue;
            }
            
            $class = 'SocialAuther\Adapter\\' . ucfirst($adapter);
            $this->adapters[$adapter] = new $class($settings);
        }

        return $this->adapters;
    }

    /**
     * @param $adapter
     * @return SocialAuther
     */
    public function getAuther($adapter)
    {
        return new SocialAuther($adapter);
    }
}
