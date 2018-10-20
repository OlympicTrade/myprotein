<?php

namespace Application\Service;

use Application\Model\Sitemap;
use ApplicationAdmin\Model\Page;
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
        $pages = EntityFactory::collection(new Page());
        $pages->select()
            ->columns(array('url', 'time_update'))
            ->where(array('sitemap' => 1));

        $data = array();

        foreach($pages as $page) {
            $sitemap->addPage(array(
                'loc'        => $page['url'],
                'changefreq' => 'weekly', //monthly | weekly | daily
                'priority'   => 1,
                'lastmod'    => $page['time_update'],
            ));
        }

        return $data;
    }
}