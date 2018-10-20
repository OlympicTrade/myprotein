<?php
namespace CatalogAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;

use CatalogAdmin\Model\Products;
use CatalogAdmin\Model\Stock;
use Zend\View\Model\JsonModel;

use Aptero\Service\Admin\TableService;

class ProductsController extends AbstractActionController
{
    public function __construct() {
        parent::__construct();

        $this->setFields(array(
            'id' => array(
                'name'      => 'ID',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'id',
                'width'     => '8'
            ),
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
                'type'      => TableService::FIELD_TYPE_LINK,
                'field'     => 'name',
                'width'     => '32',
                'hierarchy' => true,
            ),
            'discount' => array(
                'name'      => 'Скидка',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'discount',
                'width'     => '15',
            ),
            'sort' => array(
                'name'      => 'Рейтинг',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'sort',
                'width'     => '35',
            ),
        ));
    }

    public function editAction()
    {
        $view = parent::editAction();
        $view->setVariable('stock', $this->getService()->getStock($this->params()->fromQuery('id')));
        $view->setVariable('stockLimit', $this->getService()->getStockLimit($this->params()->fromQuery('id')));

        return $view;
    }

    public function statisticAction()
    {
        $this->generate();

        $productId = $this->params()->fromQuery('id');

        $filters = $this->params()->fromQuery('filters', []);

        $result = [
            'all'   =>  $this->getService()->getStatistic($productId, $filters),
            'half'  =>  $this->getService()->getStatistic($productId, $filters + ['period' => 'half']),
            'month' =>  $this->getService()->getStatistic($productId, $filters + ['period' => 'month']),
        ];

       return [
           'result'    => $result,
           'filters'   => $filters,
           'productId' => $productId,
       ];
    }

    public function stockUpdateAction()
    {
        $this->getService()->updateStock($_POST);

        return new JsonModel();
    }

    public function stockLimitUpdateAction()
    {
        $this->getService()->updateStockLimit($_POST);

        return new JsonModel();
    }

    public function getSizeTasteAction()
    {
        $productId = $this->params()->fromPost('productId');

        $product = new Products();
        $product->setId($productId)->load();

        $resp = [
            'size'  => [],
            'taste' => [],
        ];

        $tasteList = $product->getPlugin('taste');
        $tasteList->select()->order('name');

        foreach ($tasteList as $taste) {
            $resp['taste'][] = [
                'id'   => $taste->getId(),
                'name' => $taste->get('name'),
            ];
        }

        $sizeList = $product->getPlugin('size');
        $sizeList->select()->order('name');

        foreach ($sizeList as $size) {
            $resp['size'][] = [
                'id'   => $size->getId(),
                'name' => $size->get('name'),
            ];
        }

        return new JsonModel($resp);
    }
}