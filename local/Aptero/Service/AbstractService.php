<?php
namespace Aptero\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Cache\Storage\Adapter\AbstractAdapter as CacheAdapter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature as StaticDbAdapter;
use Zend\Db\Sql\Sql;

class AbstractService implements ServiceManagerAwareInterface
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;

    protected function getSql()
    {
        return new Sql(StaticDbAdapter::getStaticAdapter());
    }

    protected function execute($sql)
    {
        return $this->getSql()->prepareStatementForSqlObject($sql)->execute();
    }

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * @return CacheAdapter
     */
    public function getCache()
    {
        return $this->getServiceManager()->get('SystemCache');
    }
}