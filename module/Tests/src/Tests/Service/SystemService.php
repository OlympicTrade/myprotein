<?php

namespace Tests\Service;

use Application\Model\Sitemap;
use ApplicationAdmin\Model\Page;
use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityFactory;
use Aptero\Service\AbstractService;

class SystemService extends AbstractService
{
    /**
     * @param Sitemap $sitemap
     * @return array
     */
    public function updateSitemap(Sitemap $sitemap)
    {
        $collection = EntityFactory::collection(new Entity());
        $collection->select()
            ->columns(array('url', 'time_update'))
            ->where(array('sitemap' => 1));

        foreach($collection as $item) {
            $sitemap->addPage(array(
                'loc'        => $item['url'],
                'changefreq' => 'monthly', //monthly | weekly | daily
                'priority'   => 0.5,
                'lastmod'    => $item['time_update'],
            ));
        }
    }
}