<?php

namespace Application\Controller;

// Calling some useful Zend's libraries.
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Application\Model\Tutorial;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;

class TutorialController extends AbstractActionController
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

   public function projectsAction()
   {
		$sessionUser = new container('user');
		// Get projects page's tutorials and count them.
		$gotTutorials = (new Tutorial())->projects();
		$numberOfTuto = count($gotTutorials);
		// Will be used for sending tutorials to the JavaScript file.
		// The user will see the welcome messages only the first time he access the
		// page, because it's irrevelant to show it each time.
		$keptTutorials = array();

		// Check if the user already accessed the projects page.
		if (isset($sessionUser->isProjectsPageAlreadyAccessed) && $sessionUser->isProjectsPageAlreadyAccessed)
		{
			// If yes get tutorials from the third one.
			for ($i = 2; $i < $numberOfTuto; ++$i)
			{
				$keptTutorials[] = $gotTutorials[$i];
			}
		}
		else
		{
			// If not just keep all tutorials and set the session variable, which
			// indicates that the projects page was already accessed once.
			$keptTutorials = $gotTutorials;
			$sessionUser->isProjectsPageAlreadyAccessed = true;
		}

      return new JsonModel($keptTutorials);
   }

   public function projectAction()
   {
      return new JsonModel(
         (new Tutorial())->project()
      );
   }

   public function taskDetailsAction()
   {
      return new JsonModel(
         (new Tutorial())->taskDetails()
      );
   }

    public function addMemberAction()
    {
      return new JsonModel(
         (new Tutorial())->addMember()
      );
    }

	public function disableTutoAction()
	{
      $sessionUser = new container('user');
      $success = true;

      // The user must be connected.
      if ($sessionUser->connected)
      {
         try
         {
            $this->_getUserTable()->disableTutorial($sessionUser->id);
            $sessionUser->wantTutorial = false;
         }
         catch (\Exception $e)
         {
            $success = false;
         }
      }
      else
      {
         $success = false;
      }

      // Send the success message back with JSON.
      return new JsonModel(array(
         'success' => $success
      ));
	}

}
