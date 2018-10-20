<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class Breadcrumbs extends AbstractHelper
    implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    public function __invoke($crumbs)
    {
        $html =
            '<nav class="breadcrumbs">';

        for($i = 0; $i < count($crumbs) - 1; $i++) {
            $html .=
                '<li><a href="' . $crumbs[$i]['url'] . '">' .  $crumbs[$i]['name'] . '</a></li>';
        }

        $html .=
                '<li class="current"><a href="' . $crumbs[$i]['url'] . '">' .  $crumbs[$i]['name'] . '</a></li>';

        $html .=
            '</nav>';

        return $html;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceManager = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->serviceManager;
    }
}