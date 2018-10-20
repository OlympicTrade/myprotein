<?php

namespace Application\Service;

use Application\Model\Module;
use Application\Model\Sitemap;
use Aptero\Db\Entity\EntityFactory;
use Aptero\Service\AbstractService;

class SitemapService extends AbstractService
{
    public function generateSitemap()
    {
        $modules = EntityFactory::collection(new Module());
        $modules->select()
            ->columns(array('module', 'section'))
            ->where(array('sitemap' => 1));

        $sitemap = new Sitemap();

        foreach($modules as $module) {
            $this->getService($module)->updateSitemap($sitemap);
        }

        $sitemapXml = $sitemap->getSitemap();

        return $sitemapXml;
    }

    /**
     * @return AbstractService
     */
    protected function getService($module)
    {
        $serviceClassName = ucfirst($module->get('module')) . '\Service\SystemService';
        $service = $this->getServiceManager()->get($serviceClassName);
        return $service;
    }
}