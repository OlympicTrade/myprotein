<?php

namespace Catalog\Service;

use Aptero\Db\Entity\EntityFactory;
use Aptero\Service\AbstractService;
use Aptero\String\Numbers;
use Aptero\String\Search;
use Aptero\String\Translit;
use Catalog\Model\Brands;
use Catalog\Model\Catalog;
use Catalog\Model\CatalogTypes;
use Catalog\Model\Product;
use Catalog\Model\Products;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class CatalogService extends AbstractService
{
    public function getCatalogIds(Catalog $category)
    {
        $ids = array($category->getId());

        $children = $category->getChildren();

        if($children->count()) {
            foreach($children as $child) {
                $ids = array_merge($ids, $this->getCatalogIds($child));
            }
        }

        return $ids;
    }

    public function getAutoComplete($query)
    {
        $result = [];
        $maxRows = 4;

        //Каталог
        $catalog = $this->getCatalog(['query' => $query]);
        $catalog->load();

        if(count($catalog)) {
            //$result[] = array('label' => 'Каталог', 'type' => 'title');
            $i = 0;
            foreach ($catalog as $category) {
                $i++;
                if($i > 12) {
                    break;
                }
                $result[] = array(
                    'label' => $category['name'],
                    'url' => '/catalog/' . $category['url_path'] . '/',
                    'type' => 'category',
                    'hide'  => ($i > $maxRows ? true : false)
                );
            }
            /*if($i > $maxRows) {
                $result[] = array('label' => 'Показать все', 'type' => 'show-all', 'target' => 'category');
            }*/
            $result[] = ['type' => 'hr'];
        }

        //Товары
        $products = $this->getServiceManager()->get('Catalog\Service\ProductsService')->getProducts(
            array(
                'limit' => 3,
                'query' => $query,
                'join'  => array('reviews')
            ),
            array('reviews', 'stars'));

        if(count($products)) {
            //$result[] = array('label' => 'Товары', 'type' => 'title');
            foreach($products as $product) {
                $result[] = array(
                    'type'     => 'product',
                    'label'    => $product->get('name'),
                    'id'       => $product->get('id'),
                    'url'      => '/goods/' . $product->get('url') . '/',
                    'price'    => $product->get('price'),
                    'stars'    => $product->get('stars'),
                    'reviews'  => ($product->get('reviews') ? $product->get('reviews') . ' ' . Numbers::declension($product->get('reviews'), array('отзыв', 'отзыва', 'отзывов')) : 'Нет отзывов'),
                    'img'      => $product->getPlugin('image')->getImage('s'),
                );
            }
            $result[] = array('type' => 'clear');
        }

        /*if($result) {
            $result[] = array('type' => 'show-all');
        }*/

        return $result;
    }

    public function getCategoryCrumbs($category)
    {
        $crumbs = [];
        $parent = $category;

        do {
            $crumbs[] = array(
                'name'  => $parent->get('name'),
                'url'   => $parent->getUrl(),
            );
        } while ($parent = $parent->getParent());

        return array_reverse($crumbs);
    }

    public function getCategoryByName($categoryName)
    {
        $category = new Catalog();
        $category->select()
            ->columns(array('id', 'name', 'url_path'))
            ->where(array('name' => $categoryName));

        return $category;
    }

    public function getCatalog($filter = [])
    {
        $catalog = EntityFactory::collection(new Catalog());
        $catalog->setSelect($this->getCatalogSelect($filter));

        return $catalog;
    }

    public function getCategory($filter = [])
    {
        $catalog = new Catalog();
        $catalog->setSelect($this->getCatalogSelect($filter));

        return $catalog->load();
    }

    public function getTypeByUrl($categoryId, $subUrlTag)
    {
        $type = new CatalogTypes();
        $type->select()
            ->where([
                'depend' => $categoryId,
                'url'    => $subUrlTag,
            ]);

        return $type->load();
    }

    public function getCatalogSelect($filter = [])
    {
        $select = $this->getSql()->select()
            ->from(array('t' => 'catalog'), array('id', 'name', 'url_path', 'text'));

        $select->where(array('active' => 1));

        if($filter['url']) {
            $select->where(array('url_path' => $filter['url']));
        }

        if($filter['query']) {
            $queries = Search::prepareQuery($filter['query']);

            $where = '';
            foreach($queries as $query) {
                $where .= ($where ? ') OR (' : '((') . 't.name LIKE "' . ('%' . $query . '%') . '"';
            }
            $where .= '))';
            $select->where($where);
        }

        //die($select->getSqlString());

        return $select;
    }
}