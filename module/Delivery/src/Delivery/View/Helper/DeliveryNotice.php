<?php
namespace Delivery\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Delivery\Model\Delivery;

class DeliveryNotice extends AbstractHelper
{
    public function __invoke($price)
    {
        $city = Delivery::getInstance()->getCity();

        $html =
            '<div class="delivery-notice">'
                .'<div class="icon"></div>';

		$freePrice = $city->getFreeDeliveryPrice();
				
        if($freePrice <= $price) {
            $html .=
                '<div class="text">Теперь Вам доступна бесплатная доставка!</div>';
        } else {
            $html .=
                '<div class="text">Для бесплатной доставки добавьте товаров на <span class="nbr">' . $this->getView()->price($freePrice - $price) . '</span> руб.</div>';
        }

        $html .=
            '</div>';

        return $html;
    }
}