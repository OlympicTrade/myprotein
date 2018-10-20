<?php
$develop = getenv('APPLICATION_ENV') == 'development';

$dbParams = array(
    'driver'   => 'Pdo_Mysql',
    'hostname' => 'localhost',
    'database' => 'myprotein',
    'username' => 'root',
    'password' => '',
    'charset'  => 'utf8',
);

return array(
    'phpSettings'   => array(
        'display_startup_errors'        => true,
        'display_errors'                => true,
        'max_execution_time'            => 60,
        'date.timezone'                 => 'Europe/Moscow',
        'mbstring.internal_encoding'    => 'UTF-8',
    ),
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => function ($sm) use ($dbParams) {
                /*$adapter = new Aptero\Db\Profiler\ProfilingAdapter($dbParams);

                $adapter->setProfiler(new Aptero\Db\Profiler\Profiler);
                $adapter->injectProfilingStatementPrototype();
                return $adapter;*/

                return new Zend\Db\Adapter\Adapter($dbParams);
            },
            'Zend\Log\Logger' => function($sm){
                $logger = new Zend\Log\Logger;
                $writer = new Zend\Log\Writer\Stream('./data/logs/'.date('Y-m-d').'-error.log');

                $logger->addWriter($writer);

                return $logger;
            },
            'Sms' => function($sm) use ($develop){
                $sms = new \Aptero\Sms\Sms();

                $sms->setOptions([
                    'login'     => 'myprotein',
                    'password'  => 'Uriel1Uriel',
                    'key'       => '2F254C72-6978-3E42-0ACD-C5D57C7FF11F',
                    'sender'    => '79522872998',
                    'flash'     => false,
                    'viber'     => false,
                ]);

                return $sms;
            },
            'DataCache' => function($sm){
                $cache = Zend\Cache\StorageFactory::factory(array(
                    'adapter' => array(
                        'name'    => 'filesystem',
                        'options' => array(
                            'namespace' => 'entities'
                        ),
                    ),
                    'plugins' => array(
                        'serializer'
                    ),
                ));

                $cache->setOptions(array(
                    'cache_dir' => DATA_DIR . '/cache/data',
                ));

                return $cache;
            },
            'Session' => function($sm){
                $cache = Zend\Cache\StorageFactory::factory(array(
                    'adapter' => array(
                        'name'    => 'filesystem',
                        'options' => array(
                            'namespace' => 'entities'
                        ),
                    ),
                    'plugins' => array(
                        'serializer'
                    ),
                ));

                $cache->setOptions(array(
                    'cache_dir' => DATA_DIR . '/cache/data',
                ));

                return $cache;
            },
            'HtmlCache' => function($sm){
                $cache = Zend\Cache\StorageFactory::factory(array(
                    'adapter' => array(
                        'name'    => 'filesystem',
                        'options' => array(
                            'namespace' => 'html'
                        ),
                    ),
                    'plugins' => array(
                        'serializer'
                    ),
                ));

                $cache->setOptions(array(
                    'cache_dir' => DATA_DIR . '/cache/html',
                ));

                return $cache;
            },
        ),
    ),
);