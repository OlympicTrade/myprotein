<?php
namespace User\Model;

use Aptero\Db\Entity\Entity;

use Zend\Session\Container as SessionContainer;

use \Zend\Crypt\Password\Bcrypt;

class User extends Entity
{
    const SESSION_NAME = 'user';
    const DEFAULT_ROLE = 'guest';

    const ROLE_GUEST      = 'guest';
    const ROLE_REGISTERED = 'registered';
    const ROLE_EDITOR     = 'editor';
    const ROLE_ADMIN      = 'admin';

    const ADMIN_ID = 1;

    /**
     * @var SessionContainer
     */
    protected $session;

    public function __construct()
    {
        $this->setTable('users');

        $this->addProperties([
            'phone_id'      => [],
            'adwords_id'    => [],
            'name'          => [],
            'soc_id'        => [],
            'soc_prov'      => [],
            'soc_url'       => [],
            'login'         => [],
            'email'         => [],
            'password'      => [],
            'type'          => [],
            'online'        => [],
            'confirm'       => [],
            'active'        => [],
            'time_create'   => ['default' => date('Y-m-d')],
        ]);

        $this->addPlugin('attrs', function() {
            $properties = new \Aptero\Db\Plugin\Attributes();
            $properties->setTable('users_attrs');

            return $properties;
        });

        $this->addPropertyFilterOut('online', function($model, $online) {
            return $online > date('Y-m-d H:i:s');
        });

        $this->addPropertyFilterIn('password', function($model, $password) {
            $bcrypt = new Bcrypt();
            $hash = $bcrypt->create($password);

            //return hash('sha256', $password);
            return $hash;
        });

        $this->addPlugin('phone', function($model) {
            $phone = new Phone();
            $phone->setId($model->get('phone_id'));

            return $phone;
        }, array('independent' => true));

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('users_images');
            $image->setFolder('users');
            $image->addResolutions(array(
                'm' => array(
                    'width'  => 200,
                    'height' => 200,
                    'crop'   => true
                ),
                'hr' => array(
                    'width'  => 1000,
                    'height' => 800,
                ),
            ));

            return $image;
        });

        $this->addPlugin('file', function() {
            $file = new \Aptero\Db\Plugin\File();
            $file->setTable('users_files');
            $file->setFolder('users');

            return $file;
        });
    }

    /**
     * @return string
     */
    public function getRole()
    {
        if($this->id) {
            return $this->get('type');
        } else {
            return self::DEFAULT_ROLE;
        }
    }

    /**
     * @return bool
     */
    public function isGuest()
    {
        return !(bool) $this->id;
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        if($this->isLoaded() && $this->get('type') == self::ROLE_ADMIN) {
            return true;
        }
        return false;
    }

    /**
     * @return SessionContainer
     */
    protected function getSession()
    {
        if(!$this->session) {
            $this->session = new SessionContainer(self::SESSION_NAME);
        }

        return $this->session;
    }
}