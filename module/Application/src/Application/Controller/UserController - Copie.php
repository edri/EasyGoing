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
	private function getUserTable()
	{
		// If the object is not currencly instanciated, we do it.
		if (!$this->userTable) {
			$sm = $this->getServiceLocator();
			// Instanciate the object with the created model.
			$this->userTable = $sm->get('Application\Model\UserTable');
		}
		return $this->userTable;
	}

	private function hashPassword(String $password)
	{
		return hash ("sha256" , $password, false);
	}

	// Default action of the controller.
	// In normal case, it will be calling when the user access the "easygoing/myController/" page,
	// but here we are in the default controller so the page will be "easygoing/".
	public function indexAction()
	{
		$test = $this->getUserTable()->checkCreditentials("raphaelracine", "d74ff0ee8da3b9806b18c877dbf29bbde50b5bd8e4dad7a3a725000feb82e8f1") ? "OUIIII" : "NON !";
		// For linking the right action's view.
		return new ViewModel(array(
			'test'	=>	$test
		));
	}
	public function registrationAction()
	{
		// For linking the right action's view
		$request = $this->getRequest();

		if ($request->isPost()) {
			$result = "success";
			// POST action's values.
			$password1 = $_POST["password1"];
			$password2 = $_POST["password2"];
			$fname = $_POST["fname"];
			$lname = $_POST["lname"];
			$email = $_POST["email"];
			$username = $_POST["username"];
			$picture = $_POST["picture"];
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
							if(!$this->getUserTable()->checkIfMailExists($email)){
								//then we allow the registration
									  $userId = $this->getUserTable()->addUser($username, $this->hashPassword($password1),
									  $fname, $lname, $email, $picture);
							}
							else
								$result	= 'errorMailAlreadyExist';
						}
						else{
									$result	= 'errorMailInvalid';
							}
					}
					else
						$result	= 'errorPasswordsDontMatch';
				}
				else
					$result	= 'errorFieldEmpty';
			if ($result == "success")
				return new ViewModel(array(
					'result'			=> $result,
				));
			else
				return new ViewModel(array(
					'result' 			=> $result,
					'login' 			=> $login,
					'email'				=> $email,
					'fName'				=> $fname,
					'lName'				=> $lname,
				));
		}
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
		$this->redirect()->toRoute('/registration');
	}
}
