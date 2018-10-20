<?php

namespace User\Service;

use Metrics\Model\Adwords;
use Aptero\Service\AbstractService;
use Aptero\Mail\Mail;
use User\Model\Phone;
use Zend\Db\Sql\Where;
use Zend\Paginator\Adapter\DbSelect;
use Aptero\Db\ResultSet\ResultSet;
use User\Model\User;
use Zend\Paginator\Paginator;

class UserService extends AbstractService
{
    const CONFIRM_NONE       = 1;
    const CONFIRM_CONFIRMED  = 2;
    const CONFIRM_SUCCESS    = 3;

    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;

    public function getPaginator($page, $rows = 12, $filters = array())
    {
        $user = new User();

        $collection = $user->getCollection();

        $collection->setCols(array('login'));

        $select = $collection->getSelect();

        if(!empty($filters)) {
            if($filters['login']) {
                $select->where(function (Where $where) use($filters) {
                    $where->like('t.login', '%' . $filters['login'] . '%');
                });
            }
        }

        $authService = new AuthService();
        $userCur = $authService->getIdentity();

        $select->where->notEqualTo('id', $userCur->getId());

        $resultSet = new ResultSet();
        $resultSet->setPrototype(clone $user);
        $paginatorAdapter = new DbSelect(
            $select,
            $user->getDbAdapter(),
            $resultSet
        );

        $paginator = new Paginator($paginatorAdapter);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(12);

        return $paginator;
    }

    public function registerUser($data)
    {
        $user = new User();
        $user->unserializeArray($data);
        $user->setVariables(array(
            'type' => User::ROLE_REGISTERED,
            'active' => 1,
            'confirm' => 0,
        ));

        if($adwords = (new Adwords())->loadFromCookie()) {
            $user->set('adwords_id', $adwords->getId());
        }
        $metrics = '';

        echo $metrics;
        $user->save();

        return $user;
    }

    public function sendRegistrationMail($user, $password)
    {
        $mail = new Mail();

        $mail->setTemplate(MODULE_DIR . '/User/view/user/mail/confirm.phtml')
            ->setHeader('Регистрация на сайте')
            ->addTo($user->get('email'))
            ->setVariables([
                'confirmLink' => $_SERVER['HTTP_HOST'] . '/user/confirm/?login=' . $user->get('email') . '&hash=' . $this->getConfirmHash($user, 'activate'),
                'password'    => $password,
                'user'        => $user,
            ])
            ->send();
    }

    public function getResetUser($login, $hash)
    {
        if(!$login || !$hash) {
            return false;
        }

        $user = new User();
        $user->select()->where(array('email' => $login));
        if(!$user->load()) {
            return false;
        }

        if($this->getConfirmHash($user, 'remind') != $hash) {
            return false;
        }

        return $user;
    }

    public function sendRemindMail($login)
    {
        $user = new User();
        $where = new Where();
        $where->equalTo('login', $login)
            ->or
            ->equalTo('email', $login);

        $user->select()->where($where);

        if(!$user->load()) {
            return false;
        }

        $mail = new Mail();

        $mail->setTemplate(MODULE_DIR . '/User/view/user/mail/remember.phtml')
            ->setHeader('Восстановление пароля')
            ->addTo($user->get('email'))
            ->setVariables([
                'confirmLink' => $_SERVER['HTTP_HOST'] . '/user/reset/?login=' . $user->get('email') . '&hash=' . $this->getConfirmHash($user, 'remind'),
                'user'        => $user,
            ])
            ->send();

        return true;
    }

    public function activateUser($login, $hash)
    {
        $user = new User();
        $user->select()->where(array('email' => $login));
		
        if(!$user->load()) {
            return self::CONFIRM_NONE;
        }

        if($user->get('confirm')) {
            return self::CONFIRM_CONFIRMED;
        }

        if($hash != $this->getConfirmHash($user, 'activate')) {
            return self::CONFIRM_NONE;
        }

        $user->set('confirm', true)->save();

        return self::CONFIRM_SUCCESS;
    }

    public function getConfirmHash($user, $key)
    {
        return md5(crc32($user->get('email') . date('Y-m') . $user->get('password') . $key));
    }

    /**
     * @param $number
     * @return Phone
     */
    public function addPhone($number)
    {
        $number = preg_replace('~\D~', '', $number);
        $number = '7' . substr($number, 1);
       
        $phone = new Phone();
        $phone->select()->where(['phone' => $number]);

        if(!$phone->load()) {
            $phone->set('phone', $number)->save();
        }

        return $phone;
    }

    /**
     * @param $phone
     * @param $message
     * @return Phone
     */
    public function confirmSms($phone, $message)
    {
        $sms = $this->getServiceManager()->get('Sms');
        $sms->send($phone->get('phone'), $message);

        return $phone;
    }

    /**
     * @return \Adwords\Service\AdwordsService
     */
    protected function getAdwordsService()
    {
        return $this->getServiceManager()->get('Adwords\Service\AdwordsService');
    }
}