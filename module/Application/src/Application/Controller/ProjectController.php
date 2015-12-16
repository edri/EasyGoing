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
   // Get the given table's entity, represented by the created model.
   private function _getTable($tableName)
   {
      $sm = $this->getServiceLocator();
      // Instanciate the object with the created model.
      $table = $sm->get('Application\Model\\'.$tableName);

      return $table;
   }

   public function onDispatch( \Zend\Mvc\MvcEvent $e )
   {
      $sessionUser = new container('user');

      if(!$sessionUser->connected)
         $this->redirect()->toRoute('user');

      if(empty($this->_getTable('ProjectTable')->getProject($this->params('id'))))
         $this->redirect()->toRoute('projects');

      return parent::onDispatch( $e );
   }


   public function indexAction()
   {
      $project = $this->_getTable('ProjectTable')->getProject($this->params('id'));
      $tasks = $this->_getTable('TaskTable')->getAllTasksInProject($this->params('id'));
      $members = $this->_getTable('ViewUsersProjectsTable')->getUsersInProject($this->params('id'));
      $events = $this->_getTable('ViewEventTable')->getProjectEvents($this->params('id'));

      return new ViewModel(array(
         'project'  => $project,
         'tasks'    => $tasks,
         'members'  => $members,
         'events'   => $events
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
         $deadline = $_POST["deadline"];
         $duration = $_POST["duration"];
         $sessionUser = new container('user');

         $affectation = $this->_getTable('TaskTable')->addTask($name, $description, $deadline, $duration, $priority, $projectId);

         $this->_getTable('UsersTasksAffectationsTable')->addAffectation($sessionUser->id, $affectation);

         $this->redirect()->toRoute('project', array(
             'id' => $projectId
         ));
      }
   }

   public function editTaskAction()
   {
      $request = $this->getRequest();

      if($request->isPost())
      {
         $id = $_POST["id"];
         $name = $_POST["name"];
         $description = $_POST["description"];
         $priority = $_POST["priority"];
         $deadline = $_POST["deadline"];
         $duration = $_POST["duration"];

         $this->_getTable('TaskTable')->updateTask($name, $description, $deadline, $duration, $priority, $id);

         $this->redirect()->toRoute('project', array(
             'id' => $this->params('id')
         ));
      }
      else
      {
         $taskId = $this->params('otherId');
         $task = $this->_getTable('TaskTable')->getTaskById($taskId);

         return new ViewModel(array(
               'task' => $task
            ));
      }
   }

   public function boardViewMembersAction()
   {
      // Get members of a project
      $members = $this->_getTable('ViewUsersProjectsTable')->getUsersInProject($this->params('id'));//$this->_getViewUsersProjectsTable()->getUsersInProject($this->params('id'));

      // Get tasks in a project for each member
      $arrayTasksForMember = array();
      foreach($members as $member)
      {
         $arrayTasksForMember[$member->id] = array();
         $tasksForMember = $this->_getTable('ViewUsersTasksTable')->getTasksForMemberInProject($this->params('id'), $member->id);
         foreach($tasksForMember as $task)
            array_push($arrayTasksForMember[$member->id], $task);
      }

      $result = new ViewModel(array(
         'projectId'         => $this->params('id'),
         'members'           => $members,
         'tasksForMember'    => $arrayTasksForMember
      ));
      $result->setTerminal(true);

      return $result;
   }

   public function boardViewTasksAction()
   {
      // Get tasks in a project
      $tasks = $this->_getTable('TaskTable')->getAllTasksInProject($this->params('id'));

      // Get user(s) doing a task
      $arrayMembersForTask = array();
      foreach($tasks as $task)
      {
         $arrayMembersForTask[$task->id] = array();
         $membersForTask = $this->_getTable('ViewTasksUsersTable')->getUsersAffectedOnTask($task->id);
         foreach($membersForTask as $member)
            array_push($arrayMembersForTask[$task->id], $member);
      }

      $result = new ViewModel(array(
         'projectId'         => $this->params('id'),
         'tasks'             => $tasks,
         'membersForTask'    => $arrayMembersForTask
      ));
      $result->setTerminal(true);

      return $result;
   }

   public function moveTaskAction() {
      // Get POST data
      $data = $this->getRequest()->getPost();

      $this->_getTable('TaskTable')->updateStateOfTask($data['taskId'], $data['targetSection']);
      //$this->_getTable('UsersTasksAffectationsTable')->updateTaskAffectation($data['targetMemberId'], $data['taskId']);

      return $this->getResponse()->setContent(json_encode(array(
         'taskId'          => $data['taskId'],
         'targetMemberId'  => $data['targetMemberId'],
         'targetSection'   => $data['targetSection']
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
            $this->_getTable('ProjectsUsersMembersTable')->addMemberToProject($value, $this->params('id'));
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
        $sessionUser = new container('user');

		// The user must be authenticated to access this part, otherwise he will be
		// redirected to the home page.
		if ($sessionUser && $sessionUser->connected)
		{
            $id = (int)$this->params('id');
            $projectDetails = $this->_getTable('ViewProjectDetailsTable')->getProjectDetails($id, $sessionUser->id);
            $tempMembers = $this->_getTable('ViewProjectsMembersSpecializationsTable')->getProjectMembers($id);
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
            return new JsonModel(array(
                'success' => true,
                'projectDetails' => $projectDetails,
                'members'   => $members
            ));
        }
		else
		{
			return new JsonModel(array(
                'success' => false
            ));
		}
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
      $members = $this->_getTable('ViewUsersProjectsTable')->getUsersInProject($projectId)->buffer();
      $users = $this->_getTable('UserTable')->getAllUsers()->buffer();

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
