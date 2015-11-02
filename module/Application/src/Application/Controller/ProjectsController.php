<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

// The namespace is important. It avoids us from being forced to call the Zend's methods with
// "Application\Controller" before.
namespace Application\Controller;

// Calling some useful Zend's libraries.
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;

// Default controller ; will be calling when the user access the "mySite.com/" page.
// Be careful about the class' name, which must be the same as the file's name.
class ProjectsController extends AbstractActionController
{
	// Default action of the controller.
	// In normal case, it will be calling when the user access the "mySite.com/myController/" page,
	// but here we are in the default controller so the page will be "mySite.com/".
	public function indexAction()
	{
		// For linking the right action's view.
		return new ViewModel();
	}

	public function addAction()
	{
		return new ViewModel();
	}
}
