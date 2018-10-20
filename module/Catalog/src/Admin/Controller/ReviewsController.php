<?php
namespace CatalogAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;

use CatalogAdmin\Model\Reviews;
use Aptero\Service\Admin\TableService;
use ReviewsAdmin\Model\Review;

class ReviewsController extends AbstractActionController
{
    public function __construct() {
        parent::__construct();

        $classes = array(
            0  => 'red',
            1  => 'green',
            2  => 'gray',
        );

        $this->setFields(array(
            'name' => array(
                'name'      => 'Имя',
                'type'      => TableService::FIELD_TYPE_LINK,
                'field'     => 'name',
                'width'     => '10',
            ),
            'review' => array(
                'name'      => 'Отзыв',
                'type'      => TableService::FIELD_TYPE_LINK,
                'field'     => 'review',
                'width'     => '60',
            ),
            'product_id' => array(
                'name'      => 'Товар',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'product_id',
                'filter'    => function($value, $row, $view){
                    $product = $row->getPlugin('product');
                    $url = $view->url('adminProducts', array('action' => 'edit')) . '?id=' . $product->get('id');

                    return '<a href="' . $url . '">' . $product->get('name') . '</a>';
                },
                'width'     => '20',
            ),
            'status' => array(
                'name'      => 'Статус',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'status',
                'width'     => '10',
                'filter'    => function($value, $row) use ($classes){
                    return '<span class="wrap ' . $classes[$value] . '">' . Review::$statuses[$value]. '</span>';
                },
            ),
        ));
    }
}