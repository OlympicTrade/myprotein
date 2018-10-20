<?php
namespace Delivery\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Delivery\Model\Delivery;

class PickupPoints extends AbstractHelper
{
    public function __invoke()
    {
        $delivery = Delivery::getInstance();

        $pointsJs = '';
        $center  = array('lat' => 0, 'lon' => 0);
        $points = $delivery->getPlugin('points');
        
        foreach($points as $point) {
            $html =
                '<div class="point-desc">'
                .($point->get('metro') ? '<div class="row"><i class="fas fa-train"></i> м. ' .  $point->get('metro') . '</div>' : '')
                .($point->get('phone') ? '<div class="row"><i class="fas fa-phone"></i>' .  $point->get('phone') . '</div>' : '')
                .'<div class="row"><i class="far fa-clock"></i>' .  $point->get('work_time')
                .($point->get('weekend') ? ' | <span class="weekend">' . str_replace(["\n", "\r"], ['', ' | '], $point->get('weekend')) . '</span>' : '')
                . '</div>'
                .'<div class="row"><i class="fas fa-map-marker-alt"></i>' .  $point->get('address') . '</div>'
                .'<div class="row route">' .  $point->get('route') . '</div>'
                .'<div class="row"><span class="btn s chose-point" data-id="' . $point->getId() . '">Выбрать точку самовывоза</span></div>'
                .'</div>';
        
            $html = str_replace(["\n", "\r"], '', $html);
        
            $pointsJs .=
                '{'
                .'lat: ' . $point->get('latitude') . ','
                .'lon: ' . $point->get('longitude') . ','
                .'desc: \'' . $html . '\','
                .'},';
        
            $center['lat'] += $point->get('latitude');
            $center['lon'] += $point->get('longitude');
        }

        $center['lat'] /= $points->count();
        $center['lon'] /= $points->count();

        return [
            'points'    => $pointsJs,
            'center'    => $center,
        ];
    }
}