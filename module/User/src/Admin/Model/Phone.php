<?php
namespace UserAdmin\Model;

use Aptero\Db\Entity\Entity;

class Phone extends Entity
{
    const STATUS_NOT_CONFIRMED  = 0;
    const STATUS_CONFIRMED      = 1;

    static public $confirmStatuses = array(
        self::STATUS_NOT_CONFIRMED  => 'Не подтвержден',
        self::STATUS_CONFIRMED      => 'Подтвержден',
    );

    public function __construct()
    {
        $this->setTable('users_phones');

        $this->addProperties(array(
            'phone'      => [],
            'confirmed'  => [],
        ));

        $this->addPropertyFilterIn('phone', function($model, $value) {
            return preg_replace('~\D~', '', $value);
        });
    }

    public function getEditUrl()
    {
        return '/admin/user/phones/edit/?id=' . $this->getId();
    }
}