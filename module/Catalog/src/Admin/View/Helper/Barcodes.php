<?php
namespace CatalogAdmin\View\Helper;

use ApplicationAdmin\Model\Settings;
use Zend\View\Helper\AbstractHelper;
use Zend\Barcode\Barcode;

class Barcodes extends AbstractHelper
{
    protected $bcFolder = '/engine/barcodes/';

    public function __invoke($orders){
        $html = '';

        $files = glob(PUBLIC_DIR . $this->bcFolder . '*');
        foreach($files as $file) {
            if(is_file($file)) {
                unlink($file);
            }
        }

        $settings = Settings::getInstance();

        foreach ($orders as $order) {
            $html .=
                '<div class="code">'
                    .'<div class="row">' . $settings->get('site_name') . '</div>'
                    .'<div class="row">Заказ: ' . $order->getId() . '</div>'
                    .'<div class="row">Цена: ' . $order->getPrice() . '</div>'
                    .'<img src="' . $this->getBarcodeImg($order) . '">'
                .'</div>';
        }

        return $html;
    }

    public function getBarcodeImg($order)
    {
        $code =
             '00626'
            .'0113'
            .str_pad(substr($order->getId(), -4), 4, '0', STR_PAD_LEFT)
            .'2';

        $renderer = Barcode::factory(
            'ean128',
            'image',
            ['text' => $code],
            ['imageType' => 'png'],
            $automaticRenderError = true
        );

        $image = $renderer->draw();

        $fileName = $this->bcFolder . $order->getId() . '.png';
        imagepng($image, PUBLIC_DIR . $fileName, 9);

        return $fileName;
    }
}