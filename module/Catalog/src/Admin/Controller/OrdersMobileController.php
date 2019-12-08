<?php
namespace CatalogAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractMobileActionController;
use Aptero\String\Date;
use CatalogAdmin\Model\Orders;
use Zend\View\Model\ViewModel;

class OrdersMobileController extends AbstractMobileActionController
{
    protected $statusColors = [
        1  => 'gray',
        3  => 'brown',
        5  => 'blue',
        7  => 'pink',
        10 => 'yellow',
        15 => 'green',
        20 => 'gray',
        30 => 'red',
        35 => 'violet',
        40 => 'cyan',
    ];

    public function __construct() {
        $statusColors = $this->statusColors;

        $this->setFields([
            'id' => [
                'width'     => '25',
            ],
            'status' => [
                'width'     => '40',
                'filter'    => function($value, $row) use ($statusColors){
                    return '<div class="dot ' . $statusColors[$value] . '"></div>' . Orders::$processStatuses[$value];
                },
                'sort'      => [
                    'enabled'   => false
                ],
            ],
            'time_create' => [
                'filter'    => function($value, $row) use ($statusColors){
                    return (new Date($value))->toStr(['months' => 'short', 'year' => false]);
                },
                'width'     => '22',
            ],
            'income' => [
                'filter'    => function($value, $row, $view) {
                    $str = $row->getPrice() . ' <i class="fal fa-ruble-sign"></i>';

                    if($row->isPaid()) {
                        $str .= ' <i class="fal fa-check-circle"></i>';
                    } elseif($row->get('paid')) {
                        $str .= ' <i class="fal fa-exclamation-triangle"></i>';
                    }

                    return $str;
                },
                'width'     => '15',
            ],
        ]);
    }
}