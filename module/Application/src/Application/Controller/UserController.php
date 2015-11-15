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



// Default controller ; will be calling when the user access the "easygoing/" page.

// Be careful about the class' name, which must be the same as the file's name.
class UserController extends AbstractActionController
{
	// The user's model used to communicate with the database.
	private $userTable;

	// Get the user's table's entity, represented by the created model.
	// Act as a singleton : we only can have one instance of the object.
	private function _getUserTable()
	{
		// If the object is not currencly instanciated, we do it.
		if (!$this->userTable) {
			$sm = $this->getServiceLocator();
			// Instanciate the object with the created model.
			$this->userTable = $sm->get('Application\Model\UserTable');
		}
		return $this->userTable;
	}

	private function _hashPassword($password)
	{
			return hash ( "sha256" , $password, false );
	}

	// Default action of the controller.
	// In normal case, it will be calling when the user access the "easygoing/myController/" page,
	// but here we are in the default controller so the page will be "easygoing/".
	public function indexAction()
	{
		$request = $this->getRequest();
		if ($request->isPost())
		{
			$username = $_POST["username"];
			$password = $_POST["password"];
			$hashPassword = $this->_hashPassword($password);
			//carefull, 2nd parameter has to be hashpassword. It's password for test purpose
			$isAuthenticated = $this->_getUserTable()->checkCreditentials($username,$password);
			if($isAuthenticated)
			{
				//go To projects
				echo "isAuthenticated";
				//$this->redirect()->toRoute('/projects');
			}
			else
			{
				//stay here and display message
				echo "isNotAuthenticated";
			}
		}
		// For linking the right action's view.
		return new ViewModel();
	}
	public function registrationAction()
	{
		$request = $this->getRequest();

		if ($request->isPost()) {


			$result = "success";
			// POST action's values.
			$password1 = (empty($_POST["password1"]) ? "******" : $_POST["password1"]);
			//$password1 = $_POST["password1"];
			$password2 = (empty($_POST["password2"]) ? "******" : $_POST["password2"]);

			$fname = (empty($_POST["fname"]) ? "*****" : $_POST["fname"]);
			$lname= (empty($_POST["lname"]) ? "******" : $_POST["lname"]);
	  		$email =  (empty($_POST["email"]) ? "******" : $_POST["email"]);
			$username= (empty($_POST["username"]) ? "******" : $_POST["username"]);
		  	$picture = (empty($_POST["picture"]) ? "*****" : $_POST["picture"]);

				// Checks the fields.
				if (!empty($username) && !ctype_space($username) && !empty($email) && !empty($password1) && !empty($password2) && !empty($fname) && !empty($lname)&& !empty($picture) )
				{
					
					// The two passwords must match.
					if ($password1 == $password2)

					{
						// The mail address must be valid.
						if (filter_var($email, FILTER_VALIDATE_EMAIL))
						{
							//the email must not already exist
							if(!$this->_getUserTable()->checkIfMailExists($email))
							{
								//then we allow the registration
								try
								{
									//then we allow the registration
										$userId = $this->_getUserTable()->addUser($username, $this->_hashPassword($password1),
									  											  $fname, $lname, $email, $picture);
								}
								catch (\Exception $e)
								{
									$result = 'errorDatabaseAdding';
								}
							}
							else
								$result	= 'errorMailAlreadyExist';
						}
						else{
									$result	= 'errorMailInvalid';
							}
					}
					else
					{
						$result	= 'errorPasswordsDontMatch';
					}

				if ($result == "success")
				{
					$this->redirect()->toRoute('projects');
				}
				else
					return new ViewModel(array(
						'result' 			=> $result,
						'username' 			=> $username,
						'email'				=> $email,
						'password1' 	=>$password1,
					//	'password2' 	=>$password2,
						'fName'				=> $fname,
						'lName'				=> $lname,
					));

			}
			else{
				$result = "errorFieldEmpty";
			}
		}

		return new ViewModel();
	}

	public function logoutAction()
	{
		// For linking the right action's view.
		return new ViewModel();
	}

	public function editAction()
	{
		// For linking the right action's view.
		return new ViewModel();
	}

	public function validationAction()
	{
		$this->redirect()->toRoute('/');

		return new ViewModel();
	}


	public function cancelAction()
	{
			$this->redirect()->toRoute('/');
	}


}
