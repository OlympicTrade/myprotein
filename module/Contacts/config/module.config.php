<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Contacts\Controller\Contacts' => 'Contacts\Controller\ContactsController',
            'Admin\Controller\Contacts' => 'ContactsAdmin\Controller\ContactsController',
            'Admin\Controller\Feedback' => 'ContactsAdmin\Controller\FeedbackController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'contacts' => array(
                'type'    => 'segment',
                'priority' => 500,
                'options' => array(
                    'route'    => '[/:locale]/contacts[/:action][/:id]/',
                    'constraints' => array(
                        'locale' => '[a-z]{2}',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z][a-zA-Z0-9_-]+',
                    ),
                    'defaults' => array(
                        'module'     => 'Contacts',
                        'section'    => 'Contacts',
                        'controller' => 'Contacts\Controller\Contacts',
                        'action'     => 'index',
                    ),
                ),
            ),
            'adminContacts' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/contacts/contacts[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module'     => 'Contacts',
                        'section'    => 'Contacts',
                        'controller' => 'Admin\Controller\Contacts',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
            'adminFeedback' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/contacts/feedback[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module'     => 'Contacts',
                        'section'    => 'Feedback',
                        'controller' => 'Admin\Controller\Feedback',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'contacts' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ),
    ),
);