<?php
namespace ApplicationAdmin\Controller;

use ApplicationAdmin\Form\MenuItemEditForm;
use ApplicationAdmin\Model\MenuItems;
use Aptero\Mvc\Controller\Admin\AbstractActionController;
use Aptero\Service\Admin\TableService;
use ApplicationAdmin\Model\Menu;

class ContentController extends AbstractActionController
{
    /**
     * @return TableService
     */
    protected function getService()
    {
        $service = $this->getServiceLocator()->get('ApplicationAdmin\Service\ContentService')
            ->setModuleName('application')
            ->setSectionName('content');

        return $service;
    }
}