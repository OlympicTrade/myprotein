<?php

namespace Catalog\Service;

use Aptero\Db\Entity\EntityFactory;
use Aptero\Service\AbstractService;
use Aptero\String\Search;
use Catalog\Model\Brands;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class BrandsService extends AbstractService
{
    public function getBrandByUrl($url)
    {
        $brand = new Brands();
        $brand->select()->where(array('url' => $url));

        return $brand;
    }

    public function getBrandForSearch($query)
    {
        $brand = new Brands();
        $brand->select()
            ->columns(array('id', 'name', 'url'))
            ->where(array('name' => $query));

        return $brand;
    }

    public function getBrands($filters)
    {
        $brand = new Brands();

        if($filters['count']) {
            $brand->addProperty('count');
        }

        $brands = EntityFactory::collection($brand);

        $brands->setSelect($this->getBrandsSelect($filters));
        return $brands;
    }

    public function getBrand($filters)
    {
        $brand = new Brands();
        $brand->setSelect($this->getBrandsSelect($filters));
        return $brand;
    }

    public function getBrandsSelect($filters = array())
    {
        $select = $this->getSql()->select()
            ->from(array('t' => 'products_brands'));

        $select
            ->columns(array('id', 'name', 'url'));

        if($filters['count']) {
            $select->join(array('p' => 'products'), 't.id = p.brand_id', array('count' => new Expression('COUNT(*)')))
                ->group('t.id')
                ->order('count DESC')
                ->where->greaterThanOrEqualTo('count', 1);
        }

        if($filters['url']) {
            $select->where(array('url' =>$filters['url'] ));
        }

        if($filters['query']) {
            $queries = Search::prepareQuery($filters['query']);
            $where = new Where();
            foreach($queries as $query) {
                $where->or->like('t.name', '%' . $query . '%');
            }
            $select->where($where);
        }

        return $select;
    }
}