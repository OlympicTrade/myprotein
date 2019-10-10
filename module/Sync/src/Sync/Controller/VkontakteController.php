<?php
namespace Sync\Controller;

use Aptero\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class VkontakteController extends AbstractActionController
{
    public function indexAction()
    {
        $clientId = '7163796';
        $callbackUrl = 'https://myprotein.com.ru/sync/vkontakte/auth';
        $callbackUrl = 'https://oauth.vk.com/blank.html';
        $scope = 'offline,messages';

        $url = 'https://oauth.vk.com/authorize?client_id=' . $clientId . '&display=page&redirect_uri=' . $callbackUrl . '&scope=' . $scope . '&response_type=token&v=5.101';

        die($url);
    }
}