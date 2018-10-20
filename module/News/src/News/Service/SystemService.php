<?php

namespace News\Service;

use News\Model\News;
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
        $collection = EntityFactory::collection(new News());
        $collection->select()
            ->columns(array('url', 'time_update'));

        foreach($collection as $item) {
			$url = '/news/' . $item->get('url') . '/';
			
            $sitemap->addPage(array(
                'loc'        => $url,
                'changefreq' => 'monthly', //monthly | weekly | daily
                'priority'   => 0.5,
                'lastmod'    => $item['time_update'],
            ));
        }
    }
}