<?php
namespace ApplicationAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;

use Aptero\Service\Admin\TableService;
use ApplicationAdmin\Service\SpellChecker;

class ServiceController extends AbstractActionController
{
    public function spellcheckerAction()
    {
        $classLoader = new SpellChecker\SplClassLoader('SpellChecker', 'SpellChecker');
        $classLoader->setIncludePathLookup(true);
        $classLoader->register();

        new SpellChecker\Request();
        die();
    }
}