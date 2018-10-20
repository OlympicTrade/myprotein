<?php

namespace Blog\Service;

use Application\Model\Sitemap;
use Aptero\Service\AbstractService;
use BlogAdmin\Model\Article;

class SystemService extends AbstractService
{
    /**
     * @param Sitemap $sitemap
     * @return array
     */
    public function updateSitemap(Sitemap $sitemap)
    {
        $collection = Article::getEntityCollection();
        $collection->select()
            ->columns(array('url', 'time_update'));

        foreach($collection as $item) {
            $sitemap->addPage(array(
                'loc'        => '/blog/' . $item['url'] . '/',
                'changefreq' => 'monthly', //monthly | weekly | daily
                'priority'   => 0.5,
                'lastmod'    => $item['time_create'],
            ));
        }
    }
}