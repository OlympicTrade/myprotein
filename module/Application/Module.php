<?php
namespace Application;

use Aptero\Compressor\Compressor;
use Aptero\Mail\Mail;
use Zend\Mvc\MvcEvent;

use Zend\Mvc\I18n\Translator;
use Zend\Validator\AbstractValidator;

use Zend\Session\SessionManager;
use Zend\Session\Container;

use Zend\Db\TableGateway\Feature\GlobalAdapterFeature as StaticDbAdapter;
use Aptero\Cache\Feature\GlobalAdapterFeature as StaticCacheAdapter;

class Module
{
    public function onBootstrap(MvcEvent $mvcEvent)
    {
        $application   = $mvcEvent->getApplication();
        $sm = $application->getServiceManager();
        $eventManager = $mvcEvent->getApplication()->getEventManager();
        $sharedManager = $application->getEventManager()->getSharedManager();

        $side = substr($_SERVER['REQUEST_URI'], 0, 7) == '/admin/' ? 'admin' : 'public';

        //Errors log
        if(MODE == 'public') {
            $sharedManager->attach('Zend\Mvc\Application', 'dispatch.error', function ($e) use ($sm) {
                $mail = new Mail();
                $mail->setTemplate(
                    MODULE_DIR . '/Application/view/error/error-mail.phtml',
                    MODULE_DIR . '/Application/view/mail/error.phtml')
                    ->setHeader('Ошибка')
                    ->setVariables(['exception' => $e->getParam('exception')])
                    ->addTo('info@aptero.ru');
                    //->send();
            });
        }

        //Errors handler
        if ($side == 'admin') {
            $eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'errorDispatcherAdmin'), 100);
            $eventManager->attach(MvcEvent::EVENT_RENDER, array($this, 'onRenderAdmin'), 100);
        } else {
            $eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'errorDispatcher'), 100);
            $eventManager->attach(MvcEvent::EVENT_RENDER, array($this, 'onRenderPublic'), 100);
        }

        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'mvcPreDispatch'), 100);
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'initMail'), 100);
        $eventManager->attach(MvcEvent::EVENT_ROUTE, array($this, 'initTranslate'));

        //Default Db Adapter
        StaticDbAdapter::setStaticAdapter($sm->get('Zend\Db\Adapter\Adapter'));
        StaticCacheAdapter::setStaticAdapter($sm->get('DataCache'), 'data');
        StaticCacheAdapter::setStaticAdapter($sm->get('HtmlCache'), 'html');
    }

    public function onRenderAdmin(MvcEvent $mvcEvent)
    {
        $module  = $mvcEvent->getRouteMatch()->getParam('module');
        $section = $mvcEvent->getRouteMatch()->getParam('section');

        $view = $mvcEvent->getViewModel();
        $view->setVariables([
            'engine'    => [
                'module'    => $module,
                'section'   => $section,
            ]
        ]);
    }

    public function onRenderPublic(MvcEvent $mvcEvent)
    {
        $this->compressCssJs();
    }

	public function compressCssJs()
    {
        $compressor = new Compressor();

        $compressor->compress([
            PUBLIC_DIR . '/fonts/fonts.css',
            PUBLIC_DIR . '/css/libs/reset.css',
            PUBLIC_DIR . '/css/libs/owlcarousel.css',
            PUBLIC_DIR . '/css/libs/fancybox/fancybox.css',
            PUBLIC_DIR . '/css/libs/lightgallery.css',
            PUBLIC_DIR . '/css/libs/grid.css',
            PUBLIC_DIR . '/css/elements.scss',
            PUBLIC_DIR . '/css/main.scss',
        ], 'css');

        $jsDesktop = [
            0  => PUBLIC_DIR . '/js/config.js',
            5  => PUBLIC_DIR . '/js/libs/vkopenapi.js',
            15 => PUBLIC_DIR . '/js/chat.js',
            17 => PUBLIC_DIR . '/js/libs/youtube-bg.js',
            20 => PUBLIC_DIR . '/js/libs/fancybox/fancybox.js',
            21 => PUBLIC_DIR . '/js/libs/fancybox/thumbs.js',
            23 => PUBLIC_DIR . '/js/libs/lightgallery/lightgallery.js',
            24 => PUBLIC_DIR . '/js/libs/lightgallery/thumbnail.js',
            25 => PUBLIC_DIR . '/js/libs/history.js',
            30 => PUBLIC_DIR . '/js/libs/inputmask.js',
            35 => PUBLIC_DIR . '/js/libs/aptero.js',
            40 => PUBLIC_DIR . '/js/libs/cookie.js',
            45 => PUBLIC_DIR . '/js/libs/products-list.js',
            50 => PUBLIC_DIR . '/js/libs/cart.js',
            55 => PUBLIC_DIR . '/js/libs/form-validator.js',
            60 => PUBLIC_DIR . '/js/libs/owlcarousel.js',
            65 => PUBLIC_DIR . '/js/libs/counter.js',
            70 => PUBLIC_DIR . '/js/main.js',
            75 => PUBLIC_DIR . '/js/catalog.js',
        ];

        if(MODE == 'dev') {
            unset($jsDesktop[5]);
            unset($jsDesktop[15]);
        }

        $compressor->compress($jsDesktop, 'js');

        //Mobile
        $compressor->compress([
            PUBLIC_DIR . '/mobile/css/libs/reset.css',
            PUBLIC_DIR . '/mobile/css/libs/owlslider.css',
            PUBLIC_DIR . '/css/libs/fancybox/fancybox.css',
            PUBLIC_DIR . '/css/libs/lightgallery.css',
            PUBLIC_DIR . '/mobile/css/libs/grid.css',
            PUBLIC_DIR . '/mobile/css/elements.scss',
            PUBLIC_DIR . '/mobile/css/main.scss',
        ], 'css', 'mobile');

        $jsMobile = [
            0  => PUBLIC_DIR . '/mobile/js/config.js',
            5  => PUBLIC_DIR . '/js/libs/vkopenapi.js',
            15 => PUBLIC_DIR . '/js/chat.js',
            20 => PUBLIC_DIR . '/js/libs/fancybox/fancybox.js',
            21 => PUBLIC_DIR . '/js/libs/fancybox/thumbs.js',
            23 => PUBLIC_DIR . '/js/libs/lightgallery/lightgallery.js',
            24 => PUBLIC_DIR . '/js/libs/lightgallery/thumbnail.js',
            25 => PUBLIC_DIR . '/js/libs/history.js',
            30 => PUBLIC_DIR . '/js/libs/inputmask.js',
            35 => PUBLIC_DIR . '/js/libs/aptero.js',
            40 => PUBLIC_DIR . '/js/libs/cookie.js',
            45 => PUBLIC_DIR . '/js/libs/products-list.js',
            50 => PUBLIC_DIR . '/js/libs/cart.js',
            55 => PUBLIC_DIR . '/js/libs/form-validator.js',
            65 => PUBLIC_DIR . '/js/libs/counter.js',
            67 => PUBLIC_DIR . '/mobile/js/libs/touchwipe.js',
            70 => PUBLIC_DIR . '/mobile/js/main.js',
            75 => PUBLIC_DIR . '/mobile/js/catalog.js',
        ];

        if(MODE == 'dev') {
            unset($jsMobile[5]);
            unset($jsMobile[15]);
        }

        $compressor->compress($jsMobile, 'js', 'mobile');
    }

    public function mvcPreDispatch(MvcEvent $mvcEvent)
    {
        $module  = $mvcEvent->getRouteMatch()->getParam('module');
        $section = $mvcEvent->getRouteMatch()->getParam('section');

        $mvcEvent->getApplication()->getServiceManager()->get('Application\Model\Module')
            ->setModuleName($module)
            ->setSectionName($section)
            ->load();
    }

    public function errorDispatcherAdmin(MvcEvent $mvcEvent)
    {
        /** @var \Zend\Mvc\View\Http\ViewManager $viewManager */
        $viewManager = $mvcEvent->getApplication()->getServiceManager()->get('HttpViewManager');


        /*$notFoundStrategy = $viewManager->getRouteNotFoundStrategy();
        $notFoundStrategy->setNotFoundTemplate('error/admin/not-found');

        $exceptionStrategy = $viewManager->getExceptionStrategy();
        $exceptionStrategy->setExceptionTemplate('error/admin/exception');*/
    }

    public function errorDispatcher(MvcEvent $mvcEvent)
    {
        /*$viewManager = $mvcEvent->getApplication()->getServiceManager()->get('ViewManager');

        $mvcEvent->getViewModel()->setTemplate('layout/error-layout');

        $notFoundStrategy = $viewManager->getRouteNotFoundStrategy();
        $notFoundStrategy->setNotFoundTemplate('error/not-found');

        $exceptionStrategy = $viewManager->getExceptionStrategy();
        $exceptionStrategy->setExceptionTemplate('error/exception');*/
    }

    public function initMail(MvcEvent $mvcEvent)
    {
        $settings = $mvcEvent->getApplication()->getServiceManager()->get('Settings');
        Mail::setOptions([
            'sender'    => [
                'email' => $settings->get('mail_email'),
                'name'  => $settings->get('mail_sender'),
            ],
            'connection' => [
                'name' => $settings->get('mail_smtp'),
                'host' => $settings->get('mail_smtp'),
                'port' => 465,
                'connection_class' => 'login',
                'connection_config' => array(
                    'username' => $settings->get('mail_email'),
                    'password' => $settings->get('mail_password'),
                    'ssl' => 'ssl'
                ),
            ]
        ]);
    }
    
    public function initTranslate(MvcEvent $mvcEvent)
    {
        $aliases = array(
            'ru' => 'ru_RU',
            'en' => 'en_US',
        );
        $locale = $mvcEvent->getRouteMatch()->getParam('locale');

        if($locale && isset($aliases[$locale])) {
            \Locale::setDefault($aliases[$locale]);
        } else {
            \Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            \Locale::setDefault('ru_RU');
        }

        $translator = $mvcEvent->getApplication()->getServiceManager()->get('translator')->setLocale(\Locale::getDefault());
        $mvcEvent->getApplication()->getServiceManager()->get('ViewHelperManager')->get('translate')->setTranslator($translator);

        $formTranslator = new Translator($translator);

        AbstractValidator::setDefaultTranslator($formTranslator, 'Forms');
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getViewHelperConfig() {
        return array(
            'invokables' => array(
                'IsMobile'              => 'Aptero\View\Helper\IsMobile',
                'Header'                => 'Application\View\Helper\Header',
                'Breadcrumbs'           => 'Application\View\Helper\Breadcrumbs',
                'BtnSwitcher'           => 'Aptero\View\Helper\BtnSwitcher',
                'FormRow'               => 'Aptero\Form\View\Helper\FormRow',
                'FormErrors'            => 'Aptero\Form\View\Helper\FormErrors',
                'Fieldset'              => 'Aptero\Form\View\Helper\Fieldset',
                'FormElement'           => 'Aptero\Form\View\Helper\FormElement',
                'FormImage'             => 'Aptero\Form\View\Helper\FormImage',
                'AdminFormFileManager'  => 'Aptero\Form\View\Helper\Admin\FormFileManager',
                'AdminFormTtreeSelect'  => 'Aptero\Form\View\Helper\FormTreeSelect',
                'AdminFormImage'        => 'Aptero\Form\View\Helper\Admin\Image',
                'AdminFormProps'        => 'Aptero\Form\View\Helper\Admin\Props',
                'AdminFormAttrs'        => 'Aptero\Form\View\Helper\Admin\Attrs',
                'AdminFormImages'       => 'Aptero\Form\View\Helper\Admin\Images',
                'AdminFormFile'         => 'Aptero\Form\View\Helper\Admin\File',
                'AdminFormRow'          => 'Aptero\Form\View\Helper\Admin\FormRow',
                'AdminMessenger'        => 'ApplicationAdmin\View\Helper\Messenger',
                'AdminPrice'            => 'Aptero\View\Helper\Admin\Price',
                'AdminTableList'        => 'Aptero\View\Helper\Admin\TableList',
                'AdminMenuWidget'       => 'ApplicationAdmin\View\Helper\MenuWidget',
                'AdminFormCollection'   => 'Aptero\Form\View\Helper\Admin\Collection',
                'AdminFormProductImages'=> 'Aptero\Form\View\Helper\Admin\ProductImages',
                'AdminFormTreeSelect'   => 'Aptero\Form\View\Helper\FormTreeSelect',

                'ContentRender'         => 'Application\View\Helper\ContentRender',
                'GenerateMeta'          => 'Application\View\Helper\GenerateMeta',
                'WidgetNav'             => 'Application\View\Helper\WidgetNav',
                'TextBlock'             => 'Application\View\Helper\TextBlock',
                'HtmlBlocks'            => 'Application\View\Helper\HtmlBlocks',
                'Price'                 => 'Aptero\View\Helper\Price',
                'SubStr'                => 'Aptero\View\Helper\SubStr',
                'Escape'                => 'Aptero\View\Helper\Escape',
                'Date'                  => 'Aptero\View\Helper\Date',
                'NotEmpty'              => 'Aptero\View\Helper\NotEmpty',
                'Link'                  => 'Aptero\View\Helper\Link',
                'Video'                 => 'Aptero\View\Helper\Video',
                'Stars'                 => 'Aptero\View\Helper\Stars',
            ),
            'initializers' => array(
                function ($instance, $helperPluginManager) {
                    $sm = $helperPluginManager->getServiceLocator();

                    if ($instance instanceof \Zend\ServiceManager\ServiceLocatorAwareInterface) {
                        $instance->setServiceLocator($sm);
                    }
                }
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'Application\Model\Module'          => 'Application\Model\Module',
                'ApplicationAdmin\Model\Page'       => 'ApplicationAdmin\Model\Page',
            ),
            'factories' => array(
                'ApplicationAdmin\Service\PageService' => function ($sm) {
                    return new \ApplicationAdmin\Service\PageService($sm->get('ApplicationAdmin\Model\Page'));
                },
                'ApplicationAdmin\Service\RegionsService' => function ($sm) {
                    return new \ApplicationAdmin\Service\RegionsService($sm->get('ApplicationAdmin\Model\Region'));
                },
                'Settings' => function ($sm) {
                    $settings = new \Application\Model\Settings();
                    $settings->setId(1);
                    return $settings;
                },
                'Zend\Session\SessionManager' => function ($sm) {
                    $config = $sm->get('config');
                    if (isset($config['session'])) {
                        $session = $config['session'];

                        $sessionConfig = null;
                        if (isset($session['config'])) {
                            $class = isset($session['config']['class'])  ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
                            $options = isset($session['config']['options']) ? $session['config']['options'] : array();
                            $sessionConfig = new $class();
                            $sessionConfig->setOptions($options);
                        }

                        $sessionStorage = null;
                        if (isset($session['storage'])) {
                            $class = $session['storage'];
                            $sessionStorage = new $class();
                        }

                        $sessionSaveHandler = null;
                        if (isset($session['save_handler'])) {
                            $sessionSaveHandler = $sm->get($session['save_handler']);
                        }

                        $sessionManager = new SessionManager($sessionConfig, $sessionStorage, $sessionSaveHandler);

                        if (isset($session['validator'])) {
                            $chain = $sessionManager->getValidatorChain();
                            foreach ($session['validator'] as $validator) {
                                $validator = new $validator();
                                $chain->attach('session.validate', array($validator, 'isValid'));

                            }
                        }
                    } else {
                        $sessionManager = new SessionManager();
                    }
                    Container::setDefaultManager($sessionManager);
                    return $sessionManager;
                },
            ),
        );
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__   => __DIR__ . '/src/' . __NAMESPACE__,
                    __NAMESPACE__.'Admin'   => __DIR__ . '/src/Admin',
                ),
            ),
        );
    }
}
