<?php
namespace ContactsAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;

use Zend\View\Model\JsonModel;

use Aptero\Service\Admin\TableService;

class ContactsController extends AbstractActionController
{
    protected $fields = array(
        'phone' => array(
            'name'      => 'Телефон',
            'type'      => TableService::FIELD_TYPE_TEXT,
            'field'     => 'phone_1',
            'width'     => '30',
            'tdStyle'   => array(
                'text-align' => 'left'
            ),
            'thStyle'   => array(
                'text-align' => 'left'
            )
        ),
        'email' => array(
            'name'      => 'E-mail',
            'type'      => TableService::FIELD_TYPE_EMAIL,
            'field'     => 'email',
            'width'     => '20',
            'tdStyle'   => array(
                'text-align' => 'left'
            ),
            'thStyle'   => array(
                'text-align' => 'left'
            )
        ),
        'address' => array(
            'name'      => 'Адрес',
            'type'      => TableService::FIELD_TYPE_EMAIL,
            'field'     => 'address',
            'width'     => '50',
            'tdStyle'   => array(
                'text-align' => 'left'
            ),
            'thStyle'   => array(
                'text-align' => 'left'
            )
        ),
    );

    public function indexAction()
    {
        $module = $this->getServiceLocator()->get('Application\Model\Module')->load();

        return $this->redirect()->toRoute('admin', array(
            'module' => $module->get('module'),
            'section' => $module->get('section'),
            'action' => 'edit',
        ), array('query' => array('id' => 1)));
    }
}