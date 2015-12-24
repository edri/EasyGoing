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
<<<<<<< HEAD
	// Will contain the Utility class.
	private $_utilities;
	// The model of the mapping view between projects and users ; used to communicate with the database.
	private $_viewProjectMinTable;
	// The model representing a project.
	private $_projectTable;
	// The model representing the projects-members' mapping entity.
	private $_projectsUsersMembersTable;

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

	// Get the projects' view's entity, represented by the created model.
	// Act as a singleton : we only can have one instance of the object.
	private function _getViewProjectMinTable()
	{
		// If the object is not currencly instanciated, we do it.
		if (!$this->_viewProjectMinTable) {
			$sm = $this->getServiceLocator();
			// Instanciate the object with the created model.
			$this->_viewProjectMinTable = $sm->get('Application\Model\viewProjectMinTable');
		}
		return $this->_viewProjectMinTable;
	}

	// Get the projects' entity, represented by the created model.
	private function _getProjectTable()
	{
		if (!$this->_projectTable) {
			$sm = $this->getServiceLocator();
			$this->_projectTable = $sm->get('Application\Model\ProjectTable');
		}
		return $this->_projectTable;
	}

	// Get the projects-members' mapping entity, represented by the created model.
		private function _getProjectsUsersMembersTable()
	{
		if (!$this->_projectsUsersMembersTable) {
			$sm = $this->getServiceLocator();
			$this->_projectsUsersMembersTable = $sm->get('Application\Model\ProjectsUsersMembersTable');
		}
		return $this->_projectsUsersMembersTable;
	}

	// Default action of the controller.
	public function indexAction()
	{
		$userProjects = $this->_getViewProjectMinTable()->getUserProjects(4);

		// For linking the right action's view.
		return new ViewModel(array(
			'userProjects'	=> $userProjects
		));
	}

	public function addAction()
	{
		define("SUCCESS_MESSAGE", "ok");

		$request = $this->getRequest();
		if ($request->isPost())
		{
			// Operation's result.
			$result = SUCCESS_MESSAGE;
			// Posted values.
			$name = $_POST["name"];
			$description = (empty($_POST["description"]) ? "-" : $_POST["description"]);
			$startDate = date_parse($_POST["startDate"]);
			$deadline = date_parse($_POST["deadline"]);
			$fileName;

			// Checks that the mandatory fields aren't empty.
			if (!empty($name) && !empty($startDate) && !empty($deadline))
			{
				// The dates must be valid dates and the deadline must be greater
				// than the start date.
				if ($startDate["error_count"] == 0 && checkdate($startDate["month"], $startDate["day"], $startDate["year"]) &&
					$deadline["error_count"] == 0 && checkdate($deadline["month"], $deadline["day"], $deadline["year"]) &&
					$startDate <= $deadline)
				{
					// Indicate if the prospective project's logo is valid or not.
					$fileValidated = true;

					// If the user mentioned a logo, validate it.
					if (!empty($_FILES["logo"]["name"]))
					{
						// Allowed file's extensions.
						$allowedExts = array("jpeg", "JPEG", "jpg", "JPG", "png", "PNG");

						// Get the file's extension.
						$temp = explode(".", $_FILES["logo"]["name"]);
						$extension = end($temp);

						// Validates the file's size.
						if ($_FILES["logo"]["size"] > 5 * 1024 * 1024 || !$_FILES["logo"]["size"])
						{
							$result = "errorLogoSize";
							$fileValidated = false;
						}
						// Validates the file's type.
						else if (($_FILES["logo"]["type"] != "image/jpeg") &&
								 ($_FILES["logo"]["type"] != "image/jpg") &&
								 ($_FILES["logo"]["type"] != "image/pjpeg") &&
								 ($_FILES["logo"]["type"] != "image/x-png") &&
								 ($_FILES["logo"]["type"] != "image/png"))
						{
							$result = "errorLogoType";
							$fileValidated = false;
						}
						// Validates the file's extension.
						else if (!in_array($extension, $allowedExts))
						{
							$result = "errorLogoExtension";
							$fileValidated = false;
						}
						// Check that there is no error in the file.
						else if ($_FILES["logo"]["error"] > 0)
						{
							$result = "errorLogo";
							$fileValidated = false;
						}
						// If the file is valid, upload the picture.
						else
						{
							try
							{
								// Generate a time-based unique ID, and check that this file's name doesn't exist yet.
								do
								{
									$fileName = uniqid() . ".png";
								}
								while (file_exists(getcwd() . "/public/img/projects/" . $fileName));

								// Create a thumbnail (50px) of the image and save it in the hard drive of the server.
								$this->_getUtilities()->createSquareImage($_FILES["logo"]["tmp_name"], $extension, getcwd() . "/public/img/projects/" . $fileName, 50);
=======
   // Will contain the Utility class.
   private $_utilities;

   // Get utilities functions.
   // Act as a singleton : we only can have one instance of the object.
   private function _getUtilities()
   {
      if (!$this->_utilities)
      {
         $sm = $this->getServiceLocator();
         $this->_utilities = $sm->get('Application\Utility\Utilities');
      }
      return $this->_utilities;
   }

   // Get the given table's entity, represented by the created model.
   private function _getTable($tableName)
   {
      $sm = $this->getServiceLocator();
      // Instanciate the object with the created model.
      $table = $sm->get('Application\Model\\'.$tableName);

      return $table;
   }

   // Acts like a filter : every request go through the dispatcher, in which we
   // can do some stuff.
   // In this case, we just prevent unconnected users to access this controller.
   public function onDispatch( \Zend\Mvc\MvcEvent $e )
   {
      $sessionUser = new container('user');

      if(!$sessionUser->connected)
         $this->redirect()->toRoute('home');

      return parent::onDispatch( $e );
   }

   // Default action of the controller.
   public function indexAction()
   {
      $sessionUser = new container('user');

      $userProjects = $this->_getTable('ViewProjectMinTable')->getUserProjects($sessionUser->id);

      // For linking the right action's view.
      return new ViewModel(array(
         'userProjects'	=> $userProjects
      ));
   }

   public function addAction()
   {
      define("SUCCESS_MESSAGE", "ok");
      $sessionUser = new container('user');

      $request = $this->getRequest();
      if ($request->isPost())
      {
         // Operation's result.
         $result = SUCCESS_MESSAGE;
         // Posted values.
         $name = $_POST["name"];
         $description = (empty($_POST["description"]) ? "-" : $_POST["description"]);
         $startDate = date_parse($_POST["startDate"]);
         $deadline = date_parse($_POST["deadline"]);
         $fileName;

         // Checks that the mandatory fields aren't empty.
         if (!empty($name) && !empty($startDate) && !empty($deadline))
         {
            // The dates must be valid dates and the deadline must be greater
            // than the start date.
            if ($startDate["error_count"] == 0 && checkdate($startDate["month"], $startDate["day"], $startDate["year"]) &&
               $deadline["error_count"] == 0 && checkdate($deadline["month"], $deadline["day"], $deadline["year"]) &&
               $startDate <= $deadline)
            {
               // Indicate if the prospective project's logo is valid or not.
               $fileValidated = true;

               // If the user mentioned a logo, validate it.
               if (!empty($_FILES["logo"]["name"]))
               {
                  // Allowed file's extensions.
                  $allowedExts = array("jpeg", "JPEG", "jpg", "JPG", "png", "PNG");

                  // Get the file's extension.
                  $temp = explode(".", $_FILES["logo"]["name"]);
                  $extension = end($temp);

                  // Validates the file's size.
                  if ($_FILES["logo"]["size"] > 5 * 1024 * 1024 || !$_FILES["logo"]["size"])
                  {
                     $result = "errorLogoSize";
                     $fileValidated = false;
                  }
                  // Validates the file's type.
                  else if (($_FILES["logo"]["type"] != "image/jpeg") &&
                     ($_FILES["logo"]["type"] != "image/jpg") &&
                     ($_FILES["logo"]["type"] != "image/pjpeg") &&
                     ($_FILES["logo"]["type"] != "image/x-png") &&
                     ($_FILES["logo"]["type"] != "image/png"))
                  {
                     $result = "errorLogoType";
                     $fileValidated = false;
                  }
                  // Validates the file's extension.
                  else if (!in_array($extension, $allowedExts))
                  {
                     $result = "errorLogoExtension";
                     $fileValidated = false;
                  }
                  // Check that there is no error in the file.
                  else if ($_FILES["logo"]["error"] > 0)
                  {
                     $result = "errorLogo";
                     $fileValidated = false;
                  }
                  // If the file is valid, upload the picture.
                  else
                  {
                     try
                     {
                        // Generate a time-based unique ID, and check that this file's name doesn't exist yet.
                        do
                        {
                           $fileName = uniqid() . ".png";
                        }
                        while (file_exists(getcwd() . "/public/img/projects/" . $fileName));

                        move_uploaded_file($_FILES['logo']['tmp_name'], getcwd() . "/public/img/projects/tmp/" . $_FILES["logo"]["name"]);

                        // Create a thumbnail (50px) of the image and save it in the hard drive of the server.
                        $this->_getUtilities()->createSquareImage(getcwd() . "/public/img/projects/tmp/" . $_FILES["logo"]["name"], $extension, getcwd() . "/public/img/projects/" . $fileName, 50);
                     }
                     catch (\Exception $e)
                     {
                        $result = "errorFilesUpload";
                     }
                  }
               }

               // If there is no file or the file is valid, we can add the new
               // project in the database.
               if ($fileValidated)
               {
                  // Adds the new project in the database.
                  if ($result == SUCCESS_MESSAGE)
                  {
                     try
                     {
                        $newProject = array(
                           'name'			=> $name,
                           'description'	=> $description,
                           'startDate'		=> $_POST["startDate"],
                           'deadLineDate'	=> $_POST["deadline"],
                           'fileLogo'		=> isset($fileName) ? $fileName : "default.png"
                        );

                        $projectId = $this->_getTable("ProjectTable")->saveProject($newProject);
                        $this->_getTable("ProjectsUsersMembersTable")->addMemberToProject($sessionUser->id, $projectId, true);
                        // If project was successfully added, add a project's creation event.
                        // First of all, get right event type.
                        $typeId = $this->_getTable("EventTypeTable")->getTypeByName("Project")->id;
                        // Then add the new creation event in the database.
                        $message = "<u>" . $sessionUser->username . "</u> created the project.";
                        $eventId = $this->_getTable('EventTable')->addEvent(date("Y-m-d"), $message, $typeId);
                        // Link the new event to the new project.
                        $this->_getTable("EventOnProjectsTable")->add($eventId, $projectId);
                        // Finaly link the new event to the user who created it.
                        $this->_getTable("EventUserTable")->add($sessionUser->id, $eventId);

                        // We also have to add a "join" event to be coherent.
                        // First of all, get right event type.
                        $typeId = $this->_getTable("EventTypeTable")->getTypeByName("Users")->id;
                        // Then add the new creation event in the database.
                        $message = "<u>" . $sessionUser->username . "</u> joined the project.";
                        $eventId = $this->_getTable('EventTable')->addEvent(date("Y-m-d"), $message, $typeId);
                        // Link the new event to the new project.
                        $this->_getTable("EventOnProjectsTable")->add($eventId, $projectId);
                        // Finaly link the new event to the user who created it.
                        $this->_getTable("EventUserTable")->add($sessionUser->id, $eventId);
>>>>>>> fbcdf56a5015a9c0f5e419d39a4d067112dcfedc
							}
                     catch (\Exception $e)
                     {
                        $result = 'errorDatabaseAdding';
                     }
                  }
               }
            }
            else
            {
               $result = "errorDate";
            }
         }
         else
         {
            $result = "errorFieldEmpty";
         }

         // Deletes the uploaded file if there was an error.
         // If not, redirect the user.
         if ($result == SUCCESS_MESSAGE)
         {
            $this->redirect()->toRoute('projects');
         }
         else
         {
            if (isset($fileName) && file_exists(getcwd() . "/public/img/projects/" . $fileName))
               unlink(getcwd() . "/public/img/projects/" . $fileName);

            return new ViewModel(array(
               'error' => $result,
               'name' => $name,
               'description' => $description,
               'startDate' => $_POST["startDate"],
               'deadline' => $_POST["deadline"]
            ));
         }
      }

      return new ViewModel();
   }
}
