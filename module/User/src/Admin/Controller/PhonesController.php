<?php
namespace UserAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;
use ContactsAdmin\Model\Subscribe;
use UserAdmin\Form\LoginForm;
use UserAdmin\Model\Phone;
use Zend\View\Model\JsonModel;
use Aptero\Service\Admin\TableService;
use User\Service\AuthService;

class PhonesController extends AbstractActionController
{
    public function __construct()
    {
        parent::__construct();

        $classes = array(
            0  => 'red',
            1 => 'green',
        );

        $this->setFields(array(
            'phone' => array(
                'name'      => 'Телефон',
                'type'      => TableService::FIELD_TYPE_LINK,
                'field'     => 'phone',
                'width'     => '15',
            ),
            'confirmed' => array(
                'name'      => 'Подтверждение',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'confirmed',
                'filter'    => function($value, $row) use ($classes){
                    return '<span class="wrap ' . $classes[$value] . '">' . Phone::$confirmStatuses[$value]. '</span>';
                },
                'width'     => '85',
            ),
        ));
    }
}