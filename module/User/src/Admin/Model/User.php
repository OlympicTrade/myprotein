<?php
namespace UserAdmin\Model;

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

    public function __construct()
    {
        $this->setTable('users');

        $this->addProperties(array(
            'phone_id'    => array(),
            'name'        => array(),
            'soc_id'      => array(),
            'soc_prov'    => array(),
            'soc_url'     => array(),
            'login'       => array(),
            'email'       => array(),
            'password'    => array(),
            'type'        => array(),
            'active'      => array(),
            'confirm'     => array(),
            'time_update' => array(),
            'online'      => array(),
            'sort'        => array(),
        ));

        $this->addPropertyFilterIn('password', function($model, $password) {
            if(!empty($password)) {
                $bcrypt = new Bcrypt();
                $password = $bcrypt->create($password);
            } else {
                $password = $model->get('password');
            }

            return $password;
        });

        $this->addPlugin('phone', function($model) {
            $phone = new Phone();
            $phone->setId($model->get('phone_id'));

            return $phone;
        }, array('independent' => true));

        $this->addPlugin('props', function() {
            $properties = new \Aptero\Db\Plugin\Properties();
            $properties->setTable('users_attrs');

            return $properties;
        });

        $this->addPlugin('attrs', function() {
            $properties = new \Aptero\Db\Plugin\Attributes();
            $properties->setTable('users_attrs');

            return $properties;
        });

        $this->addPlugin('file', function() {
            $file = new \Aptero\Db\Plugin\File();
            $file->setTable('users_files');
            $file->setFolder('users');

            return $file;
        });

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('users_images');
            $image->setFolder('users');
            $image->addResolutions(array(
                'a' => array(
                    'width'  => 162,
                    'height' => 162,
                ),
                'hr' => array(
                    'width'  => 1000,
                    'height' => 800,
                )
            ));

            return $image;
        });
    }
}