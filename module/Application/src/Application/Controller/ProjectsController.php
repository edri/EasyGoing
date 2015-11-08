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

// Projects controller ; will be calling when the user access the "easygoing/projects" page.
// Be careful about the class' name, which must be the same as the file's name.
class ProjectsController extends AbstractActionController
{
	// The model of the mapping view between projects and users ; used to communicate with the database.
	private $viewProjectMinTable;
	// Get the projects' view's entity, represented by the created model.
	// Act as a singleton : we only can have one instance of the object.
	private function getViewProjectMinTable()
	{
		// If the object is not currencly instanciated, we do it.
		if (!$this->viewProjectMinTable) {
			$sm = $this->getServiceLocator();
			// Instanciate the object with the created model.
			$this->viewProjectMinTable = $sm->get('Application\Model\viewProjectMinTable');
		}
		return $this->viewProjectMinTable;
	}

	// Default action of the controller.
	public function indexAction()
	{
		$userProjects = $this->getViewProjectMinTable()->getUserProjects(4);

		// For linking the right action's view.
		return new ViewModel(array(
			'userProjects'	=> $userProjects
		));
	}

	public function addAction()
	{
		return new ViewModel();
	}
}
