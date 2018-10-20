<?php
$develop = getenv('APPLICATION_ENV') == 'development';

$dbParams = array(
    'driver'   => 'Pdo_Mysql',
    'hostname' => 'localhost',
    'database' => 'inatr',
    'username' => 'root',
    'password' => '',
    'charset'  => 'utf8',
);

return array(
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => function ($sm) use ($dbParams) {
                $adapter = new Aptero\Db\Profiler\ProfilingAdapter($dbParams);

                $adapter->setProfiler(new Aptero\Db\Profiler\Profiler);
                $adapter->injectProfilingStatementPrototype();
                return $adapter;
            },
            'Zend\Log\Logger' => function($sm){
                $logger = new Zend\Log\Logger;
                $writer = new Zend\Log\Writer\Stream('./data/logs/'.date('Y-m-d').'-error.log');

                $logger->addWriter($writer);

                return $logger;
            },
            'Mail' => function($sm) use ($develop){
                $mail = new \Aptero\Mail\Mail();

                if(!$develop) {
                    $settings = $sm->get('Settings');

                    $mail->setSender($settings->get('mail_email'), $settings->get('mail_sender'));
                    $mail->setOptions(array(
                        'name' => $settings->get('mail_smtp'),
                        'host' => $settings->get('mail_smtp'),
                        'port' => 465,
                        'connection_class' => 'login',
                        'connection_config' => array(
                            'username' => $settings->get('mail_email'),
                            'password' => $settings->get('mail_password'),
                            'ssl' => 'ssl'
                        ),
                    ));
                } else {
                    $mail->setSender('info@aptero.ru', 'Aptero.CMS');
                    $mail->setOptions(array(
                        'name'              => 'smtp.gmail.com',
                        'host'              => 'smtp.gmail.com',
                        'port'              => 465,
                        'connection_class'  => 'login',
                        'connection_config' => array(
                            'username' => 'info@aptero.ru',
                            'password' => 'Uriel1Uriel',
                            'ssl' => 'ssl'
                        ),
                    ));
                }

                return $mail;
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
                    'cache_dir' => DATA_DIR . '/cache',
                ));

                return $cache;
            },
            'SystemCache' => function($sm){
                $cache = Zend\Cache\StorageFactory::factory(array(
                    'adapter' => array(
                        'name'    => 'filesystem',
                        'options' => array(
                            'namespace' => 'system',
                            'ttl'       => 3600
                        ),
                    ),
                    'plugins' => array(
                        'serializer'
                    ),
                ));

                $cache->setOptions(array(
                    'cache_dir' => DATA_DIR . '/cache',
                ));

                return $cache;
            },
        ),
    ),
);