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
   private $_viewUsersTasksTable;
   private $_viewProjectDetailsTable;
   private $_viewProjectsMembersSpecializationsTable;
   private $_usersTasksAffectationsTable;

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

   // Get the users-tasks' mapping entity, represented by the created model.
   private function _getViewUsersTasksTable()
   {
      if (!$this->_viewUsersTasksTable) {
         $sm = $this->getServiceLocator();
         $this->_viewUsersTasksTable = $sm->get('Application\Model\ViewUsersTasksTable');
      }
      return $this->_viewUsersTasksTable;
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

   // Get the project's members' entity.
   private function _getViewProjectsMembersSpecializationsTable()
   {
       if (!$this->_viewProjectsMembersSpecializationsTable) {
           $sm = $this->getServiceLocator();
           $this->_viewProjectsMembersSpecializationsTable = $sm->get('Application\Model\ViewProjectsMembersSpecializationsTable');
       }
       return $this->_viewProjectsMembersSpecializationsTable;
   }

   public function _getUsersTasksAffectationsTable()
   {
       if (!$this->_usersTasksAffectationsTable) {
           $sm = $this->getServiceLocator();
           $this->_usersTasksAffectationsTable = $sm->get('Application\Model\UsersTasksAffectationsTable');
       }
       return $this->_usersTasksAffectationsTable;
   }

   public function indexAction()
   {
      $project = $this->_getProjectTable()->getProject($this->params('id'));

      if(empty($project))
         $this->redirect()->toRoute('projects');

      $tasks = $this->_getTaskTable()->getAllTasksInProject($this->params('id'));
      $members = $this->_getViewUsersProjectsTable()->getUsersInProject($this->params('id'));

      return new ViewModel(array(
         'project'           => $project,
         'tasks'             => $tasks,
         'members'           => $members
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
         $sessionUser = new container('user');

         $affectation = $this->_getTaskTable()->addTask($name, $description, $deadlineDate, 10, $priority, $projectId);

         // TODO : Mettre $sessionUser->id à la place de 3
         $this->_getUsersTasksAffectationsTable()->addAffectation(4, $affectation);

         $this->redirect()->toRoute('project', array(
             'controller' => 'project',
             'action' =>  'index',
             'projectId' =>'1'
         ));
      }
   }

   public function boardViewMembersAction()
   {
      // Get members of a project
      $members = $this->_getViewUsersProjectsTable()->getUsersInProject($this->params('id'));

      // Get tasks in a project for each member
      $arrayTasksForMember = array();
      foreach($members as $member)
      {
         $arrayTasksForMember[$member->id] = array();
         $tasksForMember = $this->_getViewUsersTasksTable()->getTasksForMemberInProject($this->params('id'), $member->id);
         foreach($tasksForMember as $task)
            array_push($arrayTasksForMember[$member->id], $task);
      }

      $result = new ViewModel(array(
         'members'           => $members,
         'tasksForMember'    => $arrayTasksForMember
      ));
      $result->setTerminal(true);

      return $result;
   }

   public function boardViewTasksAction()
   {
      // Get tasks in a project
      $tasks = $this->_getTaskTable()->getAllTasksInProject($this->params('id'));

      // Get user(s) doing a task
      $arrayMembersForTask = array();
      foreach($tasks as $task)
      {

      }



      $result = new ViewModel(array(
         'tasks'             => $tasks,
         'membersForTask'    => $arrayMembersForTask
      ));
      $result->setTerminal(true);

      return $result;
   }

   public function editTaskAction()
   {

   }

   public function moveTaskAction() {
      $data = $this->getRequest()->getPost();
      //echo json_encode(array('id' => $data['id'], 'details' => $data['details']));

      $this->_getTaskTable()->updateStateOfTask($data['taskId'], $data['targetSection']);
      $this->_getUsersTasksAffectationsTable()->updateTaskAffectation(5, 4);

      return $this->getResponse()->setContent(json_encode(array(
         'taskId' => $data['taskId'],
         'targetMemberId' => $data['targetMemberId'],
         'targetSection' => $data['targetSection']
      )));
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
        $tempMembers = $this->_getViewProjectsMembersSpecializationsTable()->getProjectMembers($id);
        $members = array();
        $i = 0;

        // Struct the members array.
        foreach ($tempMembers as $tmpM)
        {
            // Indicate whether the current member already exists in the members
            // list or not.
            // If yes, we just have to add the object's specialization to the
            // existing specializations of the user.
            $alreadyExisting = false;
            $nbCurrentMembers = count($members);

            // Check if the current member already exists.
            for ($j = 0; $j < $nbCurrentMembers; ++$j)
            {
                // Add the specialization to the specializations list.
                if ($tmpM->username == $members[$j]["username"])
                {
                    $alreadyExisting = true;
                    $members[$j]["specializations"][] = (empty($tmpM->specialization) ? "-" : $tmpM->specialization);
                    break;
                }
            }

            // If the current member is not already existing in the members list,
            // add it.
            if (!$alreadyExisting)
            {
                $members[$i]["username"] = $tmpM->username;
                $members[$i]["specializations"][] = empty($tmpM->specialization) ? "-" : $tmpM->specialization;
                $members[$i]["isAdmin"] = $tmpM->isAdmin;
                ++$i;
            }
        }

        // Send the success message back with JSON.
        $result = new JsonModel(array(
            'success' => true,
            'projectDetails' => $projectDetails,
            'members'   => $members
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
