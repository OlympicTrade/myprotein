<?php
namespace Contacts\Model;

use Aptero\Db\Entity\Entity;

class Feedback extends Entity
{
    const STATUS_NEW       = 0;
    const STATUS_VERIFIED  = 1;

    public function __construct()
    {
        $this->setTable('feedback');

        $this->addProperties(array(
            'name'        => array(),
            'user_id'     => array(),
            'phone'       => array(),
            'email'       => array(),
            'status'      => array(),
            'question'    => array(),
            'answer'      => array(),
            'time_create' => array(),
        ));

        $this->addPlugin('files', function($model) {
            $files = FeedbackFile::getEntityCollection();
            $files->select()->where(array('feedback_id' => $model->get('id')));

            return $files;
        });
    }
}