<?php
namespace Contacts\Model;

use Aptero\Db\Entity\Entity;

use Zend\Session\Container as SessionContainer;

use \Zend\Crypt\Password\Bcrypt;

class FeedbackFile extends Entity
{
    public function __construct()
    {        $this->setTable('feedback_files');

        $this->addProperties(array(
            'feedback_id'  => array(),
            'name'         => array(),
            'file'         => array(),
        ));
    }

    public function getPath()
    {
        return DATA_DIR . '/uploads/feedback/' . $this->get('feedback_id') . '/';
    }

    public function getFile()
    {
        return $this->getPath() . $this->get('file');
    }
}