<?php
namespace ApplicationAdmin\Service;

use ApplicationAdmin\Model\MenuItems;
use Aptero\Service\Admin\TableService;

class MenuItemsService extends TableService
{
    public function save($formData, $queryData, $model)
    {
        $model->unserializeArray($formData);

        if(isset($queryData['menu'])) {
            $model->set('menu_id', (int) $queryData['menu']);
        }

        $model->save();

        return true;
    }
}