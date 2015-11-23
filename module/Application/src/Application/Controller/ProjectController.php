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
use Zend\View\Model\JsonModel;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;

// Project controller ; will be calling when the user access the "easygoing/project" page.
// Be careful about the class' name, which must be the same as the file's name.
class ProjectController extends AbstractActionController
{
   private $_taskTable;
   private $_projectTable;
   private $_userTable;
   private $_viewUsersProjectsTable;
   private $_projectsUsersMembersTable;
   private $_viewProjectDetailsTable;

   // Get the task's table's entity, represented by the created model.
   // Act as a singleton : we only can have one instance of the object.
   private function _getTaskTable()
   {
      // If the object is not currencly instanciated, we do it.
      if (!$this->_taskTable) {
         $sm = $this->getServiceLocator();
         // Instanciate the object with the created model.
         $this->_taskTable = $sm->get('Application\Model\TaskTable');
      }
      return $this->_taskTable;
   }

   // Get the project's table's entity, represented by the created model.
   // Act as a singleton : we only can have one instance of the object.
   private function _getProjectTable()
   {
      // If the object is not currencly instanciated, we do it.
      if (!$this->_projectTable) {
         $sm = $this->getServiceLocator();
         // Instanciate the object with the created model.
         $this->_projectTable = $sm->get('Application\Model\ProjectTable');
      }
      return $this->_projectTable;
   }

   // Get the user's table's entity, represented by the created model.
   // Act as a singleton : we only can have one instance of the object.
   private function _getUserTable()
   {
      // If the object is not currencly instanciated, we do it.
      if (!$this->_userTable) {
         $sm = $this->getServiceLocator();
         // Instanciate the object with the created model.
         $this->_userTable = $sm->get('Application\Model\UserTable');
      }
      return $this->_userTable;
   }

   // Get the viewUsersProjects's table's entity, represented by the created model.
   // Act as a singleton : we only can have one instance of the object.
   private function _getViewUsersProjectsTable()
   {
      // If the object is not currencly instanciated, we do it.
      if (!$this->_viewUsersProjectsTable) {
         $sm = $this->getServiceLocator();
         // Instanciate the object with the created model.
         $this->_viewUsersProjectsTable = $sm->get('Application\Model\ViewUsersProjectsTable');
      }
      return $this->_viewUsersProjectsTable;
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

   // Get projects' details and users' mapping entity, which contains all important project's data.
   private function _getViewProjectDetailsTable()
   {
       if (!$this->_viewProjectDetailsTable) {
           $sm = $this->getServiceLocator();
           $this->_viewProjectDetailsTable = $sm->get('Application\Model\ViewProjectDetailsTable');
       }
       return $this->_viewProjectDetailsTable;
   }

   public function indexAction()
   {
      $project = $this->_getProjectTable()->getProject($this->params('id'));
      $tasks = $this->_getTaskTable()->getAllTasksInProject($this->params('id'));
      $members = $this->_getViewUsersProjectsTable()->getUsersInProject($this->params('id'));

      if(empty($project))
         $this->redirect()->toRoute('projects');

      return new ViewModel(array(
         'project' => $project,
         'tasks'   => $tasks,
         'members' => $members
      ));
   }

   public function taskAction()
   {
      return new ViewModel(array(
         'id' => $this->params('id')
      ));
   }

   public function addTaskAction()
   {
      $request = $this->getRequest();

      if($request->isPost())
      {
         $projectId = $this->params('id');
         $name = $_POST["name"];
         $description = $_POST["description"];
         $priority = $_POST["priority"];
         $startDate = $_POST["startDate"];
         $deadlineDate = $_POST["deadlineDate"];

         $this->_getTaskTable()->addTask($name, $description, $deadlineDate, 10, $priority, $projectId);
      }
   }

   public function editTaskAction()
   {

   }

   public function deleteTaskAction()
   {

   }

   public function addMemberAction()
   {
      $request = $this->getRequest();

      if($request->isPost())
      {
         foreach ($_POST as $value)
         {
            $this->_getProjectsUsersMembersTable()->addMemberToProject($value, $this->params('id'));
         }
      }
      $usersNotMemberOfProject = $this->_getUsersNotMemberOfProject($this->params('id'));

      //$usersNotMemberOfProject = $this->_getUserTable()->getUsersNotMembersOfProject($this->params('id'));

      return new ViewModel(array(
         'users' => $usersNotMemberOfProject
      ));
   }

   public function removeMemberAction()
   {

   }

   public function loadEventAction()
   {

   }

   public function detailsAction()
   {
        $id = (int)$this->params('id');
        $projectDetails = $this->_getViewProjectDetailsTable()->getProjectDetails($id, 4);

        // Send the success message back with JSON.
        $result = new JsonModel(array(
            'success' => true,
            'projectDetails' => $projectDetails,
        ));

        return $result;
  }

  private function _getUsersNotMemberOfProject($projectId)
  {
   /*
      SELECT * FROM users
      WHERE id NOT IN (
         SELECT id FROM users
          INNER JOIN projectsUsersMembers ON projectsUsersMembers.user = users.id
          WHERE projectsUsersMembers.project = 2
      )
   */
      $members = $this->_getViewUsersProjectsTable()->getUsersInProject($projectId)->buffer();
      $users = $this->_getUserTable()->getAllUsers()->buffer();

      $notMembersArray = array();
      foreach($users as $user)
      {
         $mustAdd = true;
         
         foreach($members as $member)
         {
            if($user->id == $member->id)
               $mustAdd = false;
         }

         if($mustAdd)
            array_push($notMembersArray, $user);
      }

      return $notMembersArray;
  }
}


?>
