<?php
namespace CatalogAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;

use Zend\View\Model\JsonModel;

use Aptero\Service\Admin\TableService;

class BrandsController extends AbstractActionController
{
    public function __construct() {
        parent::__construct();

        $this->setFields(array(
            'spot' => array(
                'name'      => 'Фото',
                'type'      => TableService::FIELD_TYPE_IMAGE,
                'field'     => 'image',
                'filter'    => function($value, $row){
                    return $row->getPlugin('image')->getImage('a');
                },
                'width'     => '10',
                'sort'      => array(
                    'enabled'   => false
                )
            ),
            'name' => array(
                'name'      => 'Название',
                'type'      => TableService::FIELD_TYPE_EMAIL,
                'field'     => 'name',
                'width'     => '90',
                'hierarchy' => true,
                'tdStyle'   => array(
                    'text-align' => 'left'
                ),
                'thStyle'   => array(
                    'text-align' => 'left'
                )
            ),
        ));
    }

    public function importAction()
    {
        $redirect = '/admin/catalog/brands/list/?';

        $brandsService = $this->getService();

        $result = $brandsService->importImages();
        $redirect .= '&images=' . $result;

        $this->redirect()->toUrl($redirect);
    }

    public function importImages()
    {
        if(!isset($_FILES['images']['tmp_name']) || !$_FILES['images']['tmp_name']){
            return 'empty';
        }

        $zipFile = $_FILES['images']['tmp_name'];
        $extrDir = DATA_DIR .  '/sync/csv/images/';

        $zip = new \ZipArchive;
        if ($zip->open($zipFile)) {
            $zip->extractTo(DATA_DIR .  '/sync/csv/images/');
            $zip->close();
        } else {
            return 'error';
        }

        $files = scandir($extrDir);
        foreach ($files as $oldFilename) {
            $filename = iconv('cp866', 'utf-8', $oldFilename);

            if(in_array($filename, array('.', '..'))) {
                continue;
            }

            @rename($extrDir . $oldFilename, $extrDir . $filename);
            $filename = iconv('cp866', 'utf-8', $oldFilename);

            $pathinfo = pathinfo($filename);
            $article = $pathinfo['filename'];

            $product = new Product();
            $product->select()->where(array('article' => $article));

            if($product->load()) {
                $product->getPlugin('image')->setImage(array(
                    'filepath'  => DATA_DIR . '/sync/csv/images/' . $filename
                ))->save();
            }

            @unlink(DATA_DIR . '/sync/csv/images/' . $filename);
        }

        return 'success';
    }
}