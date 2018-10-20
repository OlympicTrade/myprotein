<?php
namespace User\View\Helper;

use User\Service\AuthService;
use Zend\View\Helper\AbstractHelper;

class WidgetUser extends AbstractHelper
{
    public function __invoke()
    {
        $authService = new AuthService();
        $user = $authService->getIdentity();

        $editUrl = $this->getView()->url('user', array('action' => 'edit'));
        $profileUrl = $this->getView()->url('user');

        $html =
            '<div class="widget user">'
                .'<a href="' . $profileUrl . '" class="pic-box">'
                    .'<img src="' . $user->getPlugin('image')->getImage('m') . '" alt="' . $user->get('login') . '">'
                .'</a>'
                .'<div>'
                    .'<a href="' . $profileUrl . '" class="name">' . $user->get('login') . '</a>'
                .'</div>'
                .'<a href="' . $editUrl . '" class="edit">настроить</a>'
            .'</div>';

        return $html;
    }
}