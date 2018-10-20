<?php

namespace User\Model\Auth;

use Metrics\Model\Adwords;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Sql\Where;

use User\Model\User;

class AuthAdapter implements AdapterInterface
{
    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    public function setCredentials($username = null, $password = null)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function secAuthenticate($data)
    {
        if(!$data) {
            return new AuthUser(Result::FAILURE_CREDENTIAL_INVALID, null, array('Authorisation Error'));
        }

        $user = new User();

        $user->select()->where
            ->equalTo('soc_id', $data['social_id'])
            ->equalTo('confirm', 1)
            ->equalTo('active', 1);

        if(!$user->load()) {
            $user->clear();

            $user->setVariables([
                'confirm'    => 1,
                'active'     => 1,
                'type'       => User::ROLE_REGISTERED,
            ]);

            $user->save();
        }
        
        if($adwords = (new Adwords())->loadFromCookie()) {
            $user->set('adwords_id', $adwords->getId());
        }

        $this->updateSocProfile($user, $data)->save();

        return new AuthUser(Result::SUCCESS, $user, []);
    }

    public function updateSocProfile($user, $data)
    {
        $user->setVariables([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'soc_id'     => $data['social_id'],
            'soc_prev'   => $data['provider'],
            'soc_url'    => $data['url'],
        ]);

        $user->getPlugin('attrs')->set('sex', $data['sex']);

        if($data['image'] && !in_array($data['image'], [
                'https://vk.com/images/camera_200.png', 'http://vk.com/images/camera_200.png'
            ])) {

            $user->getPlugin('image')->setImage([
                'filepath' => $data['image']
            ]);
        }

        return $user;
    }

    public function authenticate()
    {
        if(!$this->username || !$this->password) {
            return new AuthUser(Result::FAILURE_CREDENTIAL_INVALID, null, array('Enter your username and password'));
        }

        $user = new User();

        $user->select()->where
            ->nest()
                ->equalTo('login', $this->username)
                ->or
                ->equalTo('email', $this->username)
            ->unnest()
            ->equalTo('confirm', 1)
            ->equalTo('active', 1);

        if(!$user->load()) {
            return new AuthUser(Result::FAILURE_IDENTITY_NOT_FOUND, null, array('Invalid username or password'));
        }

        $bCrypt = new Bcrypt();

        if(!$bCrypt->verify($this->password, $user->get('password'))) {
            return new AuthUser(Result::FAILURE, null, array('Invalid username or password'));
        }

        return new AuthUser(Result::SUCCESS, $user, array());
    }

    /**
     * @return \Adwords\Service\AdwordsService
     */
    protected function getAdwordsService()
    {
        return $this->getServiceManager()->get('Adwords\Service\AdwordsService');
    }
}