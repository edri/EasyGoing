<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

$display_exceptions = ($_SERVER['APPLICATION_ENV'] == 'development' ? true : false);

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\User',
                        'action'     => 'index',
                    ),
                ),
            ),
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'application' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/application',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'User',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
			// Add new routes thereafter.
            'user' => array(
				'type'    => 'segment',
				'options' => array(
					'route'    => '/[:action]',					// Creating the route, identified by the controller's name.
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',			// Regular expression for the action's name ; should not be modified.
					),
					'defaults' => array(
						'controller' => 'Application\Controller\User',	// Controller's name.
						'action'     => 'index',						// Default action ; should not be modified.
					),
				),
			),
            'project' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/project[/:id][/][:action][/:id]',                  // Creating the route, identified by the controller's name.
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+'           // Regular expression for the action's name ; should not be modified.
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Project',  // Controller's name.
                        'action'     => 'index',                        // Default action ; should not be modified.
                    ),
                ),
            ),
            'projects' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/projects[/][:action]',                  // Creating the route, identified by the controller's name.
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+'           // Regular expression for the action's name ; should not be modified.
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Projects',  // Controller's name.
                        'action'     => 'index',                        // Default action ; should not be modified.
                    ),
                ),
            ),
            'about' => array(
				'type'    => 'segment',
				'options' => array(
					'route'    => '/about[/][:action]',					// Creating the route, identified by the controller's name.
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',			// Regular expression for the action's name ; should not be modified.
					),
					'defaults' => array(
						'controller' => 'Application\Controller\About',	// Controller's name.
						'action'     => 'index',						// Default action ; should not be modified.
					),
				),
			),
            'tutorial' => array(
				'type'    => 'segment',
				'options' => array(
					'route'    => '/tutorial[/][:action]',					// Creating the route, identified by the controller's name.
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',			// Regular expression for the action's name ; should not be modified.
					),
					'defaults' => array(
						'controller' => 'Application\Controller\Tutorial',	// Controller's name.
						'action'     => 'index',						// Default action ; should not be modified.
					),
				),
			),
		),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
	// Add new controllers here.
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\User' => 'Application\Controller\UserController',
            'Application\Controller\Project' => 'Application\Controller\ProjectController',
            'Application\Controller\Projects' => 'Application\Controller\ProjectsController',
            'Application\Controller\About'   => 'Application\Controller\AboutController',
            'Application\Controller\Tutorial' => 'Application\Controller\TutorialController'
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => $display_exceptions,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
		'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
	// Configure the session manager.
	'session' => array(
        'config' => array(
            'class' => 'Zend\Session\Config\SessionConfig',
            'options' => array(
                'name' => 'easygoing',
            ),
        ),
        'storage' => 'Zend\Session\Storage\SessionArrayStorage',
        'validators' => array(
            array(
                'Zend\Session\Validator\RemoteAddr',
                'Zend\Session\Validator\HttpUserAgent',
            ),
        ),
    ),
);
