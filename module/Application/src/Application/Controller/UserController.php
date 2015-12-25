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
	// Will contain the Utility class.
	private $_utilities;

	// Get utilities functions.
	// Act as a singleton : we only can have one instance of the object.
	private function _getUtilities()
	{

		if (!$this->_utilities) {
			$sm = $this->getServiceLocator();
			$this->_utilities = $sm->get('Application\Utility\Utilities');
		}
		return $this->_utilities;
	}

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
		$sessionUser = new container('user');
		// Checks if the user isn't already connected.
		if ($sessionUser && $sessionUser->connected)
		{
			// Redirect the user if it is already connected.
			$this->redirect()->toRoute();
		}
		else
		{
			$request = $this->getRequest();
			if ($request->isPost())
			{
				$username = $_POST["username"];
				$password = $_POST["password"];
				$hashPassword = $this->_hashPassword($password);

				//Check if creditentials are correct

				$user = $this->_getUserTable()->checkCreditentials($username,$hashPassword);
				//If so, user is not null
				if(!$user == null)
				{
					//add session attributes
					$sessionUser->connected = true;
					$sessionUser->id = $user->id;
					$sessionUser->username = $user->username;

					//go To projects
					$this->redirect()->toRoute();

					//Check if the user has ticked "Remember Me" button
					//If so, create a cookie
					if (isset($_POST['checkbox'])) {
						//Set a secured cookieValue with username, password and random salt
						$salt = rand();
						$cookieValue = $this->_hashPassword($username . $password . $salt);
						// Set expiration time to 30 days
						$expirationTime = 60*60*24*30 ;
						setcookie('loginCookie', $cookieValue, time() + $expirationTime);
						// We can now retrieve this cookie using : $this->getRequest()->getCookie('loginCookie');
					}
					//go To projects
					$this->redirect()->toRoute('projects');
				}
				else
				{
					// stay here and display log in error
					$error = "loginFailed";
					return new ViewModel(array(
						'error' => $error
					));
				}
			}

		}
		return new ViewModel();
	}

	public function registrationAction()
	{
		$request = $this->getRequest();

		if ($request->isPost()) {


			$result = "success";
			// POST action's values.
			$password1 = (empty($_POST["password1"]) ? "-" :$_POST["password1"]);
			//$password1 = $_POST["password1"];
			$password2 =  (empty($_POST["password2"]) ? "-" :$_POST["password2"]);

			$fname = (empty($_POST["fname"]) ? "-" : $_POST["fname"]);
			$lname= (empty($_POST["lname"]) ? "-" : $_POST["lname"]);
	  	$email = (empty($_POST["email"]) ? "-" :$_POST["email"]);
			$username= (empty($_POST["username"]) ? "-" :$_POST["username"]);
		  $fileName ="default.png";

				// Checks the fields.

					// The two passwords must match.
					if ($password1 == $password2)

					{
						// The mail address must be valid.
						if (filter_var($email, FILTER_VALIDATE_EMAIL))
						{
							//the email must not already exist
							if(!$this->_getUserTable()->checkIfMailExists($email))
							{
								// Indicate if the prospective project's logo is valid or not.
								$fileValidated = true;
								// the picture must match some size and have particular extensions
								if (!empty($_FILES["picture"]["name"])){
									// Allowed file's extensions.
									$allowedExts = array("jpeg", "JPEG", "jpg", "JPG", "png", "PNG");
									// Get the file's extension.
									$temp = explode(".", $fileName);
									$extension = end($temp);
									// Validates the file's size.
									if ($_FILES["picture"]["size"] > 5 * 1024 * 1024 || !$_FILES["picture"]["size"])
									{
										$result = "errorPictureSize";
										$fileValidated = false;
									}
									else if (($_FILES["picture"]["type"] != "image/jpeg") &&
											 ($_FILES["picture"]["type"] != "image/jpg") &&
											 ($_FILES["picture"]["type"] != "image/pjpeg") &&
											 ($_FILES["picture"]["type"] != "image/x-png") &&
											 ($_FILES["picture"]["type"] != "image/png"))
									{
										$result = "errorPictureType";
										$fileValidated = false;
									}
									// Validates the file's extension.
									else if (!in_array($extension, $allowedExts))
									{
										$result = "errorPictureExtension";
										$fileValidated = false;
									}
									// Check that there is no error in the file.
									else if ($_FILES["picture"]["error"] > 0)
									{
										$result = "errorPicture";
										$fileValidated = false;
									}
									else
									{
										try
										{
											// Generate a time-based unique ID, and check that this file's name doesn't exist yet.
											do
											{
												$fileName = uniqid() . ".png";
											}
											while (file_exists(getcwd() . "/public/img/users/" . $fileName));

											move_uploaded_file($_FILES['picture']['tmp_name'], $_FILES['picture']['tmp_name']);

											// Create a thumbnail (50px) of the image and save it in the hard drive of the server.
											$this->_getUtilities()->createSquareImage($_FILES["picture"]["tmp_name"], $extension, getcwd() . "/public/img/users/" . $fileName, 50);
										}
										catch (Exception $e)
										{
											$result = "errorFilesUpload";
										}
									}
								}
								//then we allow the registration
								try
								{
									//then we allow the registration
										$userId = $this->_getUserTable()->addUser($username, $this->_hashPassword($password1),
									  											  $fname, $lname, $email, $fileName);
								}
								catch (\Exception $e)
								{
									$result = 'errorDatabaseAdding';
								}



							}
							else
								$result	 = 'errorMailAlreadyExist';
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
					$this->redirect()->toRoute();
				}
				else
					return new ViewModel(array(
						'result' 			=> $result,
						'username' 		=> $username,
						'email'				=> $email,
						'password1' 	=>$password1,
						'fName'				=> $fname,
						'lName'				=> $lname,
						'picture'			=> isset($fileName) ? $fileName : "default.png"
					));
		}

		return new ViewModel();
	}

	public function logoutAction()
	{
		$sessionUser = new container('user');

		$sessionUser->offsetUnset("connected");
		$sessionUser->offsetUnset("id");
		$sessionUser->offsetUnset("username");
		$this->redirect()->toRoute('user');
		return new ViewModel();
	}

	public function editAction()
	{
		// For linking the right action's view.
		return new ViewModel();
	}

	public function validationAction()
	{
		$this->redirect()->toRoute();

		return new ViewModel();
	}
}
