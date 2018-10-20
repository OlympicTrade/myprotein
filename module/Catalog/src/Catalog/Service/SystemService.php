<?php

namespace Catalog\Service;

use Application\Model\Sitemap;
use ApplicationAdmin\Model\Page;
use Aptero\Db\Entity\EntityFactory;
use Aptero\Service\AbstractService;
use Catalog\Model\Brands;
use Catalog\Model\Catalog;
use Catalog\Model\Product;

class SystemService extends AbstractService
{
    /**
     * @param Sitemap $sitemap
     * @return array
     */
    public function updateSitemap(Sitemap $sitemap)
    {
        $this->catalogSitemap($sitemap);
        $this->productsSitemap($sitemap);
        //$this->brandsSitemap($sitemap);
    }

    public function brandsSitemap(Sitemap $sitemap)
    {
        $brands = EntityFactory::collection(new Brands());

        foreach($brands as $brand) {
            $url = '/brands/' .  $brand->get('url') . '/';

            $sitemap->addPage(array(
                'loc'        => $url,
                'changefreq' => 'weekly', //monthly | weekly | daily
                'priority'   => 0.7,
            ));
        }
    }

    public function catalogSitemap(Sitemap $sitemap)
    {
        $catalog = EntityFactory::collection(new Catalog());
        $catalog->select()
            ->columns(array('id', 'parent', 'url_path'))
            ->where(array(
                't.active'  => 1
            ));

        foreach($catalog as $category) {
            $url = $category->getUrl();

            $sitemap->addPage(array(
                'loc'        => $url,
                'changefreq' => 'weekly',
                'priority'   => 0.7,
            ));
        }
    }

    public function productsSitemap(Sitemap $sitemap)
    {
        $products = EntityFactory::collection(new Product());
        $products->select()
            ->columns(array('id', 'url', 'video', 'tab1_url', 'tab1_text', 'tab2_url', 'tab2_text', 'tab3_url', 'tab3_text'))
            ->join(array('c' => 'catalog'), 't.catalog_id = c.id', array())
            ->join(array('pa' => 'products_articles'), 'pa.depend = t.id', array('articles' => 'id'), 'left')
            ->group('t.id');

        $products = $this->execute($products->select());

        foreach($products as $product) {
            $url = '/goods/' .  $product['url'] . '/';

            $sitemap->addPage(array(
                'loc'        => $url,
                'changefreq' => 'weekly',
                'priority'   => 0.7,
            ));

            //reviews
            $sitemap->addPage(array(
                'loc'        => $url . 'reviews/',
                'changefreq' => 'monthly',
                'priority'   => 0.6,
            ));

            //video
            if($product['video']) {
                $sitemap->addPage(array(
                    'loc' => $url . 'video/',
                    'changefreq' => 'monthly',
                    'priority' => 0.6,
                ));
            }

            //articles
            if($product['articles']) {
                $sitemap->addPage(array(
                    'loc' => $url . 'articles/',
                    'changefreq' => 'monthly',
                    'priority' => 0.6,
                ));
            }

            //tabs
            for($i = 1; $i <= 3; $i ++) {
                $tab = 'tab' . $i;
                if($product[$tab . '_text']) {
                    $sitemap->addPage(array(
                        'loc' => $url . $product[$tab . '_url'] . '/',
                        'changefreq' => 'monthly',
                        'priority' => 0.6,
                    ));
                }
            }
        }
    }
}