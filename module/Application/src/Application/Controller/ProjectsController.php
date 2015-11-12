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
	// The model representing a project.
	private $projectTable;
	// The model representing the projects-members' mapping entity.
	private $projectsUsersMembersTable;

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

	// Get the projects' entity, represented by the created model.
	private function getProjectTable()
	{
		if (!$this->projectTable) {
			$sm = $this->getServiceLocator();
			$this->projectTable = $sm->get('Application\Model\ProjectTable');
		}
		return $this->projectTable;
	}

	// Get the projects-members' mapping entity, represented by the created model.
	private function getProjectsUsersMembersTable()
	{
		if (!$this->projectsUsersMembersTable) {
			$sm = $this->getServiceLocator();
			$this->projectsUsersMembersTable = $sm->get('Application\Model\ProjectsUsersMembersTable');
		}
		return $this->projectsUsersMembersTable;
	}

	// Function inspired from www.thewebhelp.com.
	// Used for create the images thumbnail.
	private function createSquareImage($original_file, $original_extension, $destination_file = NULL, $square_size = 96) {
		// get width and height of original image
		$imagedata = getimagesize($original_file);
		$original_width = $imagedata[0];
		$original_height = $imagedata[1];
		$new_width = 0;
		$new_height = 0;

		if ($original_width > $original_height)
		{
			$new_height = $square_size;
			$new_width = $new_height*($original_width/$original_height);
		}
		elseif ($original_width < $original_height)
		{
			$new_width = $square_size;
			$new_height = $new_width * ($original_height / $original_width);
		}
		// $original_height == $original_width
 		else
		{
			$new_width = $square_size;
			$new_height = $square_size;
		}

		$new_width = round($new_width);
		$new_height = round($new_height);
		$original_image;

		switch ($original_extension)
		{
			case "png":
			case "PNG":
				$original_image = imagecreatefrompng($original_file);
				break;
			case "jpg":
			case "JPG":
			case "jpeg":
			case "JPEG":
				$original_image = imagecreatefromjpeg($original_file);
				break;
		}

		if (!$original_image)
		{
			throw new \Exception("formatNotSupported");
		}

		$smaller_image = imagecreatetruecolor($new_width, $new_height);
		$square_image = imagecreatetruecolor($square_size, $square_size);
		// Save original image's transparancy.
		imagealphablending($smaller_image, false);
		imagesavealpha($smaller_image, true);
		imagealphablending($original_image, true);

		imagecopyresampled($smaller_image, $original_image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);

		imagealphablending($square_image, false);
		imagesavealpha($square_image, true);
		imagealphablending($smaller_image, true);

		if ($new_width > $new_height)
		{
			$difference = $new_width-$new_height;
			$half_difference =  round($difference/2);
			imagecopyresampled($square_image, $smaller_image, 0-$half_difference+1, 0, 0, 0, $square_size+$difference, $square_size, $new_width, $new_height);
		}

		if ($new_width < $new_height)
		{
			$difference = $new_height-$new_width;
			$half_difference =  round($difference/2);
			imagecopyresampled($square_image, $smaller_image, 0, 0-$half_difference+1, 0, 0, $square_size, $square_size+$difference, $new_width, $new_height);
		}

		if ($new_height == $new_width)
		{
			imagecopyresampled($square_image, $smaller_image, 0, 0, 0, 0, $square_size, $square_size, $new_width, $new_height);
		}

		// If no destination file was given then display a png
		if (!$destination_file)
		{
			imagepng($square_image, NULL, 9);
		}

		// Save the smaller image FILE if destination file given
		if (substr_count(strtolower($destination_file), ".jpg"))
		{
			imagejpeg($square_image, $destination_file, 100);
		}

		if (substr_count(strtolower($destination_file), ".gif"))
		{
			imagegif($square_image, $destination_file);
		}

		if (substr_count(strtolower($destination_file), ".png"))
		{
			imagepng($square_image, $destination_file, 9);
		}

		imagedestroy($original_image);
		imagedestroy($smaller_image);
		imagedestroy($square_image);
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

								//move_uploaded_file($_FILES['logo']['tmp_name'], getcwd() . "/public/img/projects/" . $fileName . "tmp");

								// Reduction of the image's weight and save it.
								//$this->resizeImageWeight($_FILES["logo"]["tmp_name"], getcwd() . "/public/img/projects/" . $fileName, $extension);

								// Create a thumbnail (50px) of the image and save it in the hard drive of the server.
								$this->createSquareImage($_FILES["logo"]["tmp_name"], $extension, getcwd() . "/public/img/projects/" . $fileName, 50);
							}
							catch (Exception $e)
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
						{	try
							{
								$newProject = array(
									'name'			=> $name,
									'description'	=> $description,
									'startDate'		=> $startDate,
									'deadLineDate'	=> $deadline,
									'fileLogo'		=> isset($fileName) ? $fileName : "default.png"
								);

								$project = $this->getProjectTable()->saveProject($newProject);
								$this->getProjectsUsersMembersTable()->addMemberToProject(4, $project, true);
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
