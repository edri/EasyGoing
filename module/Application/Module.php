<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Validator\HttpUserAgent;
use Zend\Session\Validator\RemoteAddr;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Application\Model\User;
use Application\Model\UserTable;
use Application\Model\Project;
use Application\Model\ProjectTable;

@ini_set('zend_monitor.enable', 0);
if(@function_exists('output_cache_disable')) {
    @output_cache_disable();
}
if(isset($_GET['debugger_connect']) && $_GET['debugger_connect'] == 1) {
    if(function_exists('debugger_connect'))  {
        debugger_connect();
        exit();
    } else {
        echo "No connector is installed.";
    }
}


class Module
{
    public function onBootstrap(MvcEvent $e)
    {
		$e->getApplication()->getServiceManager()->get('viewhelpermanager')->setFactory('controllerName', function($sm) use ($e) {
			$viewHelper = new View\Helper\ControllerName($e->getRouteMatch());
			return $viewHelper;
		});

		$eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

		// Calls the bootstrap used for the session management.
		$this->initSession(array(
			'remember_me_seconds' => 300,	// The session stay persisting 5 minutes after the browser closing.
			'use_cookies' => true,
			'cookie_httponly' => true,
		));
    }

	// Session management.
	public function initSession($config)
	{
		$sessionConfig = new SessionConfig();
		$sessionConfig->setOptions($config);

		$sessionManager = new SessionManager($sessionConfig);
		$sessionManager->getValidatorChain()
			->attach(
				'session.validate',
				array(new RemoteAddr(), 'isValid')		// Validate the session by the user's IP address, for avoiding hijacking.
			);
		$sessionManager->getValidatorChain()
			->attach(
				'session.validate',
				array(new HttpUserAgent(), 'isValid')	// While the RemoteAddr validator can be spoofed or bypassed by a proxy, we also enable the HttpUserAgent one, which uses the HTTP user agent to validate the request.
			);
		$sessionManager->start();
		Container::setDefaultManager($sessionManager);
	}

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
			'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

	// Load objects related to the database's data.
	public function getServiceConfig()
	{
		return array(
			'factories' => array(
                // Declare the gateway between the database's entity (table,
                //  view, ...) and the exchange's class.
                'UserTableGateway' => function ($sm) { // Change the gateway's name.
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new User()); // Change the instance's class name.
                    return new TableGateway('users', $dbAdapter, null, $resultSetPrototype); // Change the table's name (this IS the table's name in the database).
                },
                // Use the gateway to give the [NameTable]Table's file the
                // exchange's class as parameter.
                'Application\Model\UserTable' =>  function($sm) { // Change the class' name.
                    $tableGateway = $sm->get('UserTableGateway'); // Change the gateway's name.
                    $table = new UserTable($tableGateway); // Change the instance's class name.
                    return $table;
                },
                'ProjectTableGateway' => function ($sm) { // Change the gateway's name.
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Project()); // Change the instance's class name.
                    return new TableGateway('projects', $dbAdapter, null, $resultSetPrototype); // Change the table's name (this IS the table's name in the database).
                },
                'Application\Model\ProjectTable' =>  function($sm) { // Change the class' name.
                    $tableGateway = $sm->get('ProjectTableGateway'); // Change the gateway's name.
                    $table = new ProjectTable($tableGateway); // Change the instance's class name.
                    return $table;
                },
				// Configure the session service.
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
                            // class should be fetched from service manager since it will require constructor arguments
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
}
