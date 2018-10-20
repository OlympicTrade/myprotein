<?php
namespace CatalogAdmin\Service;

use Aptero\Service\Admin\TableService;
use CatalogAdmin\Model\Brands;

class BrandsService extends TableService
{
    public function importImages()
    {
        if(!isset($_FILES['images']['tmp_name']) || !$_FILES['images']['tmp_name']){
            return 'empty';
        }

        $zipFile = $_FILES['images']['tmp_name'];
        $extrDir = DATA_DIR .  '/uploads/images/';

        $zip = new \ZipArchive;
        if ($zip->open($zipFile)) {
            $zip->extractTo(DATA_DIR .  '/uploads/images/');
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
            $name = $pathinfo['filename'];

            $product = new Brands();
            $product->select()->where(array('name' => $name));

            if($product->load()) {
                $product->getPlugin('image')->setImage(array(
                    'filepath'  => DATA_DIR . '/uploads/images/' . $filename
                ))->save();
            }

            @unlink(DATA_DIR . '/uploads/images/' . $filename);
        }

        return 'success';
    }
}