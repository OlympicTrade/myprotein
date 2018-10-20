<?php

namespace Samples\Service;

use Application\Model\Sitemap;
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
            ->columns(['url', 'time_update'])
            ->where(['sitemap' => 1]);

        foreach($collection as $item) {
            $sitemap->addPage([
                'loc'        => $item['url'],
                'changefreq' => 'monthly', //monthly | weekly | daily
                'priority'   => 0.5,
                'lastmod'    => $item['time_update'],
            ]);
        }
    }
}