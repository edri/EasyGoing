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
use Zend\Http\Client;
use Zend\Http\Request;
use Application\Utility\Priority;

// Project controller ; will be calling when the user access the "easygoing/project" page.
// Be careful about the class' name, which must be the same as the file's name.
class ProjectController extends AbstractActionController
{
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

   // Get a project's members and each of their specializations with no repetition.
   // Parameters:
   //    - projectId : the concerned project's ID.
   private function getMembersSpecializations($projectId)
   {
      $tempMembers = $this->_getTable('ViewProjectsMembersSpecializationsTable')->getProjectMembers($projectId);
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

      return $members;
   }

   // Acts like a filter : every request go through the dispatcher, in which we
   // can do some stuff.
   // In this case, we just prevent unconnected users to access this controller
   // and check if the accessed project/task exists.
   public function onDispatch( \Zend\Mvc\MvcEvent $e )
   {
      $sessionUser = new container('user');

      if (!$sessionUser->connected)
      {
         $this->redirect()->toRoute('home');
      }

      if (!$this->_getTable('ProjectTable')->getProject($this->params('id')))
      {
         $this->redirect()->toRoute('projects');
      }

      if(!$this->_getTable('ProjectsUsersMembersTable')->getMemberRight($sessionUser->id, $this->params('id')))
      {
         $this->redirect()->toRoute('projects');
         return;
      }

      if ($this->params('otherId') != null && !$this->_getTable('TaskTable')->getTaskById($this->params('otherId')))
      {
         $this->redirect()->toRoute('projects');
      }

      return parent::onDispatch($e);
   }

   public function indexAction()
   {
      $sessionUser = new container('user');
      $projectId = $this->params('id');
      $project = $this->_getTable('ProjectTable')->getProject($projectId);
      $parentTasksInProject = $this->_getTable('TaskTable')->getAllParentTasksInProject($projectId);
      $subTasks = array();
      foreach($parentTasksInProject as $parentTask)
      {
         $subTasks[$parentTask->id] = $this->_getTable('TaskTable')->getSubTasks($parentTask->id);
      }

      $membersOfProject = $this->_getTable('ViewUsersProjectsTable')->getUsersInProject($projectId);
      // Get projects' events types.
      $eventsTypes = $this->_getTable('EventTypeTable')->getTypes(false);
      // Get project's events.
      $events = $this->_getTable('ViewEventTable')->getEntityEvents($projectId, false);
      $isManagerOfProject = $this->_userIsAdminOfProject($sessionUser->id, $projectId) ? true : false;
      $isCreatorOfProject = $this->_userIsCreatorOfProject($sessionUser->id, $projectId) ? true : false;
      // If the "showMembersSpecializations" cookie exists and is set to 1, the
      // page will display the members' specializations.
      $showSpecializations = isset($_COOKIE["showMembersSpecializations"]) && $_COOKIE["showMembersSpecializations"];

      // Send a HTTP POST request to the HTTP server to indicate we want to join
      // the current project's websockets flow.
      // An user can only join the project's flow if it made a "joinProjectRequest"
      // HTTP request and then send a confirmation with JavaScript to suscribe to
      // the flow.
      // This was done because of the JavaScript's security issues (as it is a
      // client side).
      try
      {
         $this->_sendRequest(array(
            "requestType"  => "joinProjectRequest",
            "userId"       => $sessionUser->id,
            "projectId"    => $projectId
         ));
      }
      catch (\Exception $e)
      {
         error_log("WARNING: could not connect to events servers. Maybe offline?");
      }

      return new ViewModel(array(
         'project'               => $project,
         'tasks'                 => $parentTasksInProject,
         'subTasks'              => $subTasks,
         'members'               => $membersOfProject,
         'eventsTypes'           => $eventsTypes,
         'events'                => $events,
         'isManager'             => $isManagerOfProject,
         'userId'                => $sessionUser->id,
         'isCreator'             => $isCreatorOfProject,
         'showSpecializations'   => $showSpecializations
      ));
   }

   public function editAction()
   {
      define("SUCCESS_MESSAGE", "ok");
      $sessionUser = new container('user');
      $projectId = $this->params("id");
      // Get connected user's rights on the project.
      $rights = $this->_getTable("ProjectsUsersMembersTable")->getMemberRight($sessionUser->id, $projectId);
      // The user can edit the project only if he is an admin of it.
      if ($rights->isAdmin)
      {
         $request = $this->getRequest();
         // Get project's data.
         $project = $this->_getTable("ProjectTable")->getProject($projectId);

         if ($request->isPost())
         {
            // Operation's result message.
            $result = SUCCESS_MESSAGE;
            // Posted values.
            $name = $_POST["name"];
            $description = (empty($_POST["description"]) ? "-" : $_POST["description"]);
            $startDate = date_parse($_POST["startDate"]);
            $deadline = date_parse($_POST["deadline"]);
            // Will be used attribute a name to the uploaded file.
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
                           // First move the temporary uploaded file in the server's directory to
                           // avoid some extensions issues with some OS.
                           move_uploaded_file($_FILES['logo']['tmp_name'], getcwd() . "/public/img/projects/tmp/" . $_FILES["logo"]["name"]);
                           // Then create a thumbnail (50px) of the image and save it in the hard drive of the server.
                           $this->_getUtilities()->createSquareImage(getcwd() . "/public/img/projects/tmp/" . $_FILES["logo"]["name"], $extension, getcwd() . "/public/img/projects/" . $fileName, 150);
                        }
                        catch (\Exception $e)
                        {
                           $result = "errorFilesUpload";
                        }

                        // Delete the temporary file if it exists.
                        if (file_exists(getcwd() . "/public/img/projects/tmp/" . $_FILES["logo"]["name"]))
                           unlink(getcwd() . "/public/img/projects/tmp/" . $_FILES["logo"]["name"]);

                        // Delete old project's logo if it wasn't the default one.
                        if ($project->fileLogo != "default.png")
                        {
                           if (file_exists(getcwd() . "/public/img/projects/" . $project->fileLogo))
                              unlink(getcwd() . "/public/img/projects/" . $project->fileLogo);
                        }
                     }
                  }
                  // If there is no file or the file is valid, we can edit the
                  // project in the database.
                  if ($fileValidated)
                  {
                     // Edits the project in the database.
                     if ($result == SUCCESS_MESSAGE)
                     {
                        // Only upload the edition if some values changed.
                        if (isset($fileName) || $name != $project->name || $description != $project->description || $_POST["startDate"] != $project->startDate || $_POST["deadline"] != $project->deadLineDate)
                        {
                           try
                           {
                              $editedProject = array(
                                 'name'         => $name,
                                 'description'  => $description,
                                 'startDate'    => $_POST["startDate"],
                                 'deadLineDate' => $_POST["deadline"],
                                 'fileLogo'     => isset($fileName) ? $fileName : $project->fileLogo
                              );
                              $this->_getTable("ProjectTable")->editProject($projectId, $editedProject);

                              // If project was successfully edited, add a project's edition event.
                              // First of all, get right event type.
                              $typeId = $this->_getTable("EventTypeTable")->getTypeByName("Project")->id;
                              // Then add the new creation event in the database.
                              $message = "<u>" . $sessionUser->username . "</u> edited the project.";
                              $details =
                                 "<table class='eventDetailsTable'>
                                    <tr>
                                       <th class='eventDetailsTaskAttribute'></th>
                                       <th>Old values</th>
                                       <th>New values</th>
                                    </tr>
                                    <tr>
                                       <td class='eventDetailsTaskAttribute'>Name: </td>
                                       <td>" . $project->name . "</td>
                                       <td>" . $name . "</td>
                                    </tr>
                                    <tr>
                                       <td class='eventDetailsTaskAttribute'>Description: </td>
                                       <td>" . $project->description . "</td>
                                       <td>" . $description . "</td>
                                    </tr>
                                    <tr>
                                       <td class='eventDetailsTaskAttribute'>Startdate: </td>
                                       <td>" . $project->startDate . "</td>
                                       <td>" . $_POST["startDate"] . "</td>
                                    </tr>
                                    <tr>
                                       <td class='eventDetailsTaskAttribute'>Deadline: </td>
                                       <td>" . $project->deadLineDate . "</td>
                                       <td>" . $_POST["deadline"] . "</td>
                                    </tr>
                                    <tr>
                                       <td class='eventDetailsTaskAttribute'>Logo: </td>
                                       <td></td>
                                       <td>" . (isset($fileName) ? "Aw yeah, new logo!" : "No new logo :(") . "</td>
                                    </tr>
                                 </table>";
                              $eventId = $this->_getTable('EventTable')->addEvent(date("Y-m-d"), $message, $typeId, $details);
                              // Link the new event to the new project.
                              $this->_getTable("EventOnProjectsTable")->add($eventId, $projectId);
                              // Finaly link the new event to the user who created it.
                              $this->_getTable("EventUserTable")->add($sessionUser->id, $eventId);
                              // Get event's data to send them to socket server.
                              $event = $this->_getTable("ViewEventTable")->getEvent($eventId, false);

                              try
                              {
                                 $this->_sendRequest(array(
                                    "requestType"        => "newEvent",
                                    "event"              => json_encode($event)
                                 ));
                              }
                              catch (\Exception $e)
                              {
                                 error_log("WARNING: could not connect to events servers. Maybe offline?");
                              }
                           }
                           catch (\Exception $e)
                           {
                              $result = "errorDatabaseAdding";
                           }
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
               $this->redirect()->toRoute('project', array(
                   'id' => $projectId
               ));
            }
            else
            {
               // Delete the tumbnail, if it exists.
               if (isset($fileName) && file_exists(getcwd() . "/public/img/projects/" . $fileName))
                  unlink(getcwd() . "/public/img/projects/" . $fileName);

               return new ViewModel(array(
                  'error'        => $result,
                  'name'         => $name,
                  'description'  => $description,
                  'startDate'    => $_POST["startDate"],
                  'deadline'     => $_POST["deadline"],
                  'logo'         => $project->fileLogo
               ));
            }
         }
         else
         {
            // If there is no POST request, send project's data to the view.
            return new ViewModel(array(
               "project"   => $project
            ));
         }
      }
      else
      {
         $this->redirect()->toRoute('projects');
      }
   }


   public function addTaskAction()
   {
      $request = $this->getRequest();
      $projectId = $this->params('id');
      $isSubTask = $this->params('otherId') ? true : false;

      if($isSubTask)
      {
         if($this->_getTable('TaskTable')->getTaskById($this->params('otherId'))->parentTask)
            $this->redirect()->toRoute('project', array(
               'id' => $projectId
            ));
      }

      if($request->isPost())
      {
         $sessionUser = new container('user');
         $name = $_POST["name"];
         $description = $_POST["description"];
         $priority = $_POST["priority"];
         $deadline = $_POST["deadline"];
         $duration = $_POST["duration"];

         if(isset($_POST["name"]) && isset($_POST["priority"]) && isset($_POST["duration"]))
         {
            $taskId = $this->_getTable('TaskTable')->addTask($name, $description, $deadline, $duration, $priority, $projectId, $this->params('otherId') ? $this->params('otherId') : null);

            if(!$isSubTask)
               $this->_getTable('UsersTasksAffectationsTable')->addAffectation($sessionUser->id, $taskId);

            // If task was successfully added, add two task's creation events: one for
            // the project's history, and another for the new task's news feed.
            // For the project's history.
            // First of all, get right event type.
            $typeId = $this->_getTable("EventTypeTable")->getTypeByName("Tasks")->id;
            // Then add the new event in the database.
            $message =
               "<u>" . $sessionUser->username . "</u> created task <font color=\"#FF6600\">" .
               $name . "</font> and assigned it to <font color=\"black\">(" . $sessionUser->username . ", TODO)</font>.";
            $eventId = $this->_getTable('EventTable')->addEvent(date("Y-m-d"), $message, $typeId);
            // Link the new event to the current project.
            $this->_getTable("EventOnProjectsTable")->add($eventId, $projectId);
            // Finaly link the new event to the user who created it.
            $this->_getTable("EventUserTable")->add($sessionUser->id, $eventId);
            // Get event's data to send them to socket server.
            $event = $this->_getTable("ViewEventTable")->getEvent($eventId, false);
            // For the task's news feed.
            $typeId = $this->_getTable("EventTypeTable")->getTypeByName("Info")->id;
            $message = "\"" . $sessionUser->username . "\" created the task.";
            $eventId = $this->_getTable('EventTable')->addEvent(date("Y-m-d"), $message, $typeId);
            $this->_getTable("EventOnTaskTable")->add($eventId, $taskId);
            // Get SYSTEM user's ID and link it to the new task's event.
            $systemUserId = $this->_getTable("UserTable")->getSystemUser()->id;
            $this->_getTable("EventUserTable")->add(($systemUserId ? $systemUserId : $sessionUser->id), $eventId);

            try
            {
               $this->_sendRequest(array(
                  "requestType"        => "newEvent",
                  "event"              => json_encode($event)
               ));
            }
            catch (\Exception $e)
            {
               error_log("WARNING: could not connect to events servers. Maybe offline?");
            }
         }

         $this->redirect()->toRoute('project', array(
             'id' => $projectId
         ));
      }

      return new ViewModel(array(
         'isSubTask' => $isSubTask
      ));
   }

   public function taskDetailsAction()
   {
      $taskId = $this->params('otherId');
      $projectId = $this->params('id');
      $sessionUser = new container('user');
      $task = $this->_getTable('TaskTable')->getTaskById($taskId);
      // Get tasks' events types.
      $eventsTypes = $this->_getTable('EventTypeTable')->getTypes(true);
      // Get task's events.
      $events = $this->_getTable('ViewEventTable')->getEntityEvents($taskId, true);

      // Send a HTTP POST request to the HTTP server to indicate we want to join
      // the current task's websockets flow.
      // An user can only join the task's flow if it made a "joinTaskRequest"
      // HTTP request and then send a confirmation with JavaScript to suscribe to
      // the flow.
      // This was done because of the JavaScript's security issues (as it is a
      // client side).
      try
      {
         $this->_sendRequest(array(
            "requestType"  => "joinTaskRequest",
            "userId"       => $sessionUser->id,
            "projectId"    => $projectId,
            "taskId"       => $taskId
         ));
      }
      catch (\Exception $e)
      {
         error_log("WARNING: could not connect to events servers. Maybe offline?");
      }

      return new ViewModel(array(
         'task'         => $task,
         'projectId'    => $projectId,
         'eventsTypes'  => $eventsTypes,
         'events'       => $events,
         'userId'       => $sessionUser->id
      ));
   }

   public function editTaskAction()
   {
      $request = $this->getRequest();

      if($request->isPost())
      {
         $sessionUser = new container('user');
         $projectId = $this->params('id');

         $id = $_POST["id"];
         $name = $_POST["name"];
         $description = $_POST["description"];
         $priority = $_POST["priority"];
         $deadline = $_POST["deadline"];
         $duration = $_POST["duration"];

         // Get old task's data for the historical.
         $oldTaskData = $this->_getTable('TaskTable')->getTaskById($id);
         // Upload task's data.
         $this->_getTable('TaskTable')->updateTask($name, $description, $deadline, $duration, $priority, $id);

         // Get priorites' texts.
         $oldPriorityText = "";
         $newPriorityText = "";
         foreach(Priority::getConstants() as $value)
         {
            if ($oldTaskData->priorityLevel == $value)
            {
               $oldPriorityText = Priority::toString($value);
            }
            if ($priority == $value)
            {
               $newPriorityText = Priority::toString($value);
            }
         }

         // If task was successfully edited, add a task's edition event.
         // First of all, get right event type.
         $typeId = $this->_getTable("EventTypeTable")->getTypeByName("Tasks")->id;
         // Then add the new event in the database.
         $message = "<u>" . $sessionUser->username . "</u> updated task <font color=\"#FF6600\">" . $name . "</font>.";
         // This event have some details.
         $details =
            "<table class='eventDetailsTable'>
               <tr>
                  <th class='eventDetailsTaskAttribute'></th>
                  <th>Old values</th>
                  <th>New values</th>
               </tr>
               <tr>
                  <td class='eventDetailsTaskAttribute'>Name: </td>
                  <td>" . $oldTaskData->name . "</td>
                  <td>" . $name . "</td>
               </tr>
               <tr>
                  <td class='eventDetailsTaskAttribute'>Deadline: </td>
                  <td>" . $oldTaskData->deadLineDate . "</td>
                  <td>" . $deadline . "</td>
               </tr>
               <tr>
                  <td class='eventDetailsTaskAttribute'>Duration: </td>
                  <td>" . $oldTaskData->durationsInHours . "h</td>
                  <td>" . $duration . "h</td>
               </tr>
               <tr>
                  <td class='eventDetailsTaskAttribute'>Priority: </td>
                  <td>" . $oldPriorityText . "</td>
                  <td>" . $newPriorityText . "</td>
               </tr>
               <tr>
                  <td class='eventDetailsTaskAttribute'>Description: </td>
                  <td>" . (empty($oldTaskData->description) ? "-" : $oldTaskData->description) . "</td>
                  <td>" . (empty($description) ? "-" : $description) . "</td>
               </tr>
            </table>";
         $eventId = $this->_getTable('EventTable')->addEvent(date("Y-m-d"), $message, $typeId, $details);
         // Link the new event to the current project.
         $this->_getTable("EventOnProjectsTable")->add($eventId, $projectId);
         // Finaly link the new event to the user who created it.
         $this->_getTable("EventUserTable")->add($sessionUser->id, $eventId);
         // Get event's data to send them to socket server.
         $event1 = $this->_getTable("ViewEventTable")->getEvent($eventId, false);
         // For the task's news feed.
         $typeId = $this->_getTable("EventTypeTable")->getTypeByName("Info")->id;
         $message = "\"" . $sessionUser->username . "\" updated the task.";
         $eventId = $this->_getTable('EventTable')->addEvent(date("Y-m-d"), $message, $typeId);
         $this->_getTable("EventOnTaskTable")->add($eventId, $id);
         // Get SYSTEM user's ID and link it to the new task's event.
         $systemUserId = $this->_getTable("UserTable")->getSystemUser()->id;
         $this->_getTable("EventUserTable")->add(($systemUserId ? $systemUserId : $sessionUser->id), $eventId);
         $event2 = $this->_getTable("ViewEventTable")->getEvent($eventId, true);

         try
         {
            $this->_sendRequest(array(
               "requestType"  => "newEvents",
               "events"       => array(json_encode($event1), json_encode($event2))
            ));

            // Send a edit request to inform users which are currently in the task page.
            $this->_sendRequest(array(
               "requestType"  => "taskEdited",
               "taskId"       => $id,
               "data"         => json_encode(array(
                                    "name"         => $name,
                                    "deadline"     => $deadline,
                                    "duration"     => $duration,
                                    "priority"     => $priority,
                                    "description"  => $description
                                 ))
            ));
         }
         catch (\Exception $e)
         {
            error_log("WARNING: could not connect to events servers. Maybe offline?");
         }

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
      $members = $this->_getTable('ViewUsersProjectsTable')->getUsersInProject($this->params('id'));
      // Get members' specializations.
      $membersSpecializations = $this->getMembersSpecializations($this->params('id'));
      // If the "showMembersSpecializations" cookie exists and is set to 1, the
      // page will display the members' specializations.
      $showSpecializations = isset($_COOKIE["showMembersSpecializations"]) && $_COOKIE["showMembersSpecializations"];
      $creatorId = $this->_getTable('ProjectTable')->getProject($this->params('id'))->creator;


      $parentTasksInProject = $this->_getTable('TaskTable')->getAllParentTasksInProject($this->params('id'));
      $subTasks = array();
      foreach($parentTasksInProject as $parentTask)
      {
         $subTasks[$parentTask->id] = $this->_getTable('TaskTable')->getSubTasks($parentTask->id);
      }


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
         'projectId'                => $this->params('id'),
         'creatorId'                => $creatorId,
         'members'                  => $members,
         'membersSpecializations'   => $membersSpecializations,
         'tasksForMember'           => $arrayTasksForMember,
         'showSpecializations'      => $showSpecializations,
         'subTasks'                 => $subTasks
      ));
      $result->setTerminal(true);

      return $result;
   }

   public function boardViewTasksAction()
   {
      // Get tasks in a project
      $tasks = $this->_getTable('TaskTable')->getAllParentTasksInProject($this->params('id'));

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

   public function assignTaskAction()
   {
      $sessionUser = new container('user');
      $projectId = $this->params('id');
      $data = $this->getRequest()->getPost();


      if($this->_userIsAssignToTask($data['targetMemberId'], $data['taskId']))
      {
         return $this->getResponse()->setContent(json_encode(array(
            'hasRightToAssignTask' => true,
            'alreadyAssigned'      => true
         )));
      }
      else
      {
         $this->_getTable('UsersTasksAffectationsTable')->addAffectation($data['targetMemberId'], $data['taskId']);

         // Get task's new affectation and section before erasing them.
         $newUsername = $this->_getTable("UserTable")->getUserById($this->_getTable('UsersTasksAffectationsTable')->getAffectationByTaskId($data['taskId'])->user)->username;
         $task = $this->_getTable("TaskTable")->getTaskById($data['taskId']);

         // If task was successfully assigned, add an event.
         // Get right event type.
         $typeId = $this->_getTable("EventTypeTable")->getTypeByName("Tasks")->id;
         // Then add the new event in the database.
         $message =
            "<u>" . $sessionUser->username . "</u> moved task <font color=\"#FF6600\">" . $task->name .
            "</font> from <font color=\"black\"><i>unassigned</i></font> to <font color=\"black\">(" . $newUsername . ", " . $task->state . ")</font>.";
         $eventId = $this->_getTable('EventTable')->addEvent(date("Y-m-d"), $message, $typeId);
         // Link the new event to the current project.
         $this->_getTable("EventOnProjectsTable")->add($eventId, $projectId);
         // Finaly link the new event to the user who created it.
         $this->_getTable("EventUserTable")->add($sessionUser->id, $eventId);
         // Get event's data to send them to socket server.
         $event1 = $this->_getTable("ViewEventTable")->getEvent($eventId, false);
         // For the task's news feed.
         $typeId = $this->_getTable("EventTypeTable")->getTypeByName("Info")->id;
         $message = "\"" . $sessionUser->username . "\" moved the task from \"unassigned\" to \"(" . $newUsername . ", " . $task->state . ")\".";
         $eventId = $this->_getTable('EventTable')->addEvent(date("Y-m-d"), $message, $typeId);
         $this->_getTable("EventOnTaskTable")->add($eventId, $data['taskId']);
         // Get SYSTEM user's ID and link it to the new task's event.
         $systemUserId = $this->_getTable("UserTable")->getSystemUser()->id;
         $this->_getTable("EventUserTable")->add(($systemUserId ? $systemUserId : $sessionUser->id), $eventId);
         $event2 = $this->_getTable("ViewEventTable")->getEvent($eventId, true);

         try
         {
            $this->_sendRequest(array(
               "requestType"  => "newEvents",
               "events"       => array(json_encode($event1), json_encode($event2))
            ));
         }
         catch (\Exception $e)
         {
            error_log("WARNING: could not connect to events servers. Maybe offline?");
         }

         return $this->getResponse()->setContent(json_encode(array(
            'hasRightToAssignTask' => true,
            'alreadyAssigned'      => false
         )));
      }
   }

   public function moveTaskAction()
   {
      $sessionUser = new container('user');
      $projectId = $this->params('id');
      // Get POST data
      $data = $this->getRequest()->getPost();

      // If he's super admin or he's manager and the other is not manager or he's assign to task and it's his assign to him
      if($this->_userIsCreatorOfProject($sessionUser->id, $projectId)
         || $this->_userIsAdminOfProject($sessionUser->id, $projectId) && !$this->_userIsAdminOfProject($data['oldMemberId'], $projectId)
         || $this->_getTable('UsersTasksAffectationsTable')->getAffectation($sessionUser->id, $data['taskId'])
            && $sessionUser->id == $data['oldMemberId'])
      {
         $this->_getTable('TaskTable')->updateStateOfTask($data['taskId'], $data['targetSection']);

         if($data['oldMemberId'] != $data['targetMemberId'])
         {
            $this->_getTable('UsersTasksAffectationsTable')->updateTaskAffectation($data['oldMemberId'], $data['taskId'], $data['targetMemberId']);
         }

         // If task was successfully moved, add a task's movement event.
         // First of all, get right event type, moved task's name and old/new task's user's name.
         $typeId = $this->_getTable("EventTypeTable")->getTypeByName("Tasks")->id;
         $name = $this->_getTable("TaskTable")->getTaskById($data['taskId'])->name;
         $oldUsername = $this->_getTable("UserTable")->getUserById($data['oldMemberId'])->username;
         $newUsername = $this->_getTable("UserTable")->getUserById($data['targetMemberId'])->username;
         // Then add the new event in the database.
         $message =
            "<u>" . $sessionUser->username . "</u> moved task <font color=\"#FF6600\">" . $name .
            "</font> from <font color=\"black\">(" . $oldUsername . ", " . $data['oldSection'] . ")</font>" .
            " to <font color=\"black\">(" . $newUsername . ", " . $data['targetSection'] . ")</font>.";
         $eventId = $this->_getTable('EventTable')->addEvent(date("Y-m-d"), $message, $typeId);
         // Link the new event to the current project.
         $this->_getTable("EventOnProjectsTable")->add($eventId, $projectId);
         // Finaly link the new event to the user who created it.
         $this->_getTable("EventUserTable")->add($sessionUser->id, $eventId);
         // Get event's data to send them to socket server.
         $event1 = $this->_getTable("ViewEventTable")->getEvent($eventId, false);
         // For the task's news feed.
         $typeId = $this->_getTable("EventTypeTable")->getTypeByName("Info")->id;
         $message = "\"" . $sessionUser->username . "\" moved the task from \"(" . $oldUsername . ", " . $data['oldSection'] . ")\" to \"(" . $newUsername . ", " . $data['targetSection'] . ")\".";
         $eventId = $this->_getTable('EventTable')->addEvent(date("Y-m-d"), $message, $typeId);
         $this->_getTable("EventOnTaskTable")->add($eventId, $data['taskId']);
         // Get SYSTEM user's ID and link it to the new task's event.
         $systemUserId = $this->_getTable("UserTable")->getSystemUser()->id;
         $this->_getTable("EventUserTable")->add(($systemUserId ? $systemUserId : $sessionUser->id), $eventId);
         $event2 = $this->_getTable("ViewEventTable")->getEvent($eventId, true);

         // Send task's event socket.
         try
         {
            $this->_sendRequest(array(
               "requestType"  => "newEvents",
               "events"       => array(json_encode($event1), json_encode($event2))
            ));

            // Send a task's moving event so the users which currently are in the
            // project can see the task dynamically moves.
            // Setting POST data.
            $this->_sendRequest(array(
               "requestType"     => "taskMoving",
               "projectId"       => $projectId,
               "taskId"          => $data['taskId'],
               "targetMemberId"  => $data['targetMemberId'],
               "targetSection"   => $data['targetSection'],
               "userId"          => $sessionUser->id
            ));
         }
         catch (\Exception $e)
         {
            error_log("WARNING: could not connect to events servers. Maybe offline?");
         }

         // Send back data and project's event socket's data.
         return $this->getResponse()->setContent(json_encode(array(
            'taskId'              => $data['taskId'],
            'targetMemberId'      => $data['targetMemberId'],
            'targetSection'       => $data['targetSection'],
            'event'               => $event1,
            'hasRightToMoveTask'  => true
         )));
      }
      else
      {
         return $this->getResponse()->setContent(json_encode(array(
            'hasRightToMoveTask'  => false
         )));
      }
   }

   public function unassignTaskAction()
   {
      $projectId = $this->params('id');
      $sessionUser = new container('user');
      $data = $this->getRequest()->getPost();
      $resMessage = 'Unassign success';

      // If he's super admin or he's manager and the other is not manager or he's assign to task and it's his assign to him
      if($this->_userIsCreatorOfProject($sessionUser->id, $projectId)
         || $this->_userIsAdminOfProject($sessionUser->id, $projectId) && !$this->_userIsAdminOfProject($data['oldMemberId'], $projectId)
         || $this->_getTable('UsersTasksAffectationsTable')->getAffectation($sessionUser->id, $data['userId'])
            && $sessionUser->id == $data['userId'])
      {
         // Get task's old affectation and section before erasing them.
         $oldUsername = $this->_getTable("UserTable")->getUserById($this->_getTable('UsersTasksAffectationsTable')->getAffectationByTaskId($data['taskId'])->user)->username;
         $task = $this->_getTable("TaskTable")->getTaskById($data['taskId']);

         $this->_getTable('UsersTasksAffectationsTable')->deleteAffectation($data['userId'], $data['taskId']);

         // If task was successfully unassigned, add an event.
         // Get right event type.
         $typeId = $this->_getTable("EventTypeTable")->getTypeByName("Tasks")->id;
         // Then add the new event in the database.
         $message =
            "<u>" . $sessionUser->username . "</u> moved task <font color=\"#FF6600\">" . $task->name .
            "</font> from <font color=\"black\">(" . $oldUsername . ", " . $task->state . ")</font> to <font color=\"black\"><i>unassigned</i></font>.";
         $eventId = $this->_getTable('EventTable')->addEvent(date("Y-m-d"), $message, $typeId);
         // Link the new event to the current project.
         $this->_getTable("EventOnProjectsTable")->add($eventId, $projectId);
         // Finaly link the new event to the user who created it.
         $this->_getTable("EventUserTable")->add($sessionUser->id, $eventId);
         // Get event's data to send them to socket server.
         $event1 = $this->_getTable("ViewEventTable")->getEvent($eventId, false);
         // For the task's news feed.
         $typeId = $this->_getTable("EventTypeTable")->getTypeByName("Info")->id;
         $message = "\"" . $sessionUser->username . "\" moved the task from \"(" . $oldUsername . ", " . $task->state . ")\" to \"unassigned\".";
         $eventId = $this->_getTable('EventTable')->addEvent(date("Y-m-d"), $message, $typeId);
         $this->_getTable("EventOnTaskTable")->add($eventId, $data['taskId']);
         // Get SYSTEM user's ID and link it to the new task's event.
         $systemUserId = $this->_getTable("UserTable")->getSystemUser()->id;
         $this->_getTable("EventUserTable")->add(($systemUserId ? $systemUserId : $sessionUser->id), $eventId);
         $event2 = $this->_getTable("ViewEventTable")->getEvent($eventId, true);

         try
         {
            $this->_sendRequest(array(
               "requestType"  => "newEvents",
               "events"       => array(json_encode($event1), json_encode($event2))
            ));
         }
         catch (\Exception $e)
         {
            error_log("WARNING: could not connect to events servers. Maybe offline?");
         }
      }
      else
      {
         $resMessage = 'You do not have rights to unassign this task !';
      }

      return $this->getResponse()->setContent(json_encode(array(
         'message' => $resMessage
      )));
   }

   public function deleteTaskAction()
   {
      $projectId = $this->params('id');
      $sessionUser = new container('user');
      $taskId = $this->params('otherId');
      $resMessage = 'Delete success';


      if($this->_userIsCreatorOfProject($sessionUser->id, $projectId))
      {
         // Get old task's data for the historical.
         $oldTaskData = $this->_getTable('TaskTable')->getTaskById($taskId);
         $this->_getTable('TaskTable')->deleteTask($taskId);

         // Get old task's priority's text.
         $priorityText = "";
         foreach(Priority::getConstants() as $value)
         {
            if ($oldTaskData->priorityLevel == $value)
            {
               $priorityText = Priority::toString($value);
            }
         }

         // If task was successfully deleted, add a task's deletion event.
         // First of all, get right event type.
         $typeId = $this->_getTable("EventTypeTable")->getTypeByName("Tasks")->id;
         // Then add the new event in the database.
         $message = "<u>" . $sessionUser->username . "</u> deleted task <font color=\"#FF6600\">" . $oldTaskData->name . "</font>.";
         // This event have some details.
         $details =
            "<table class='eventDetailsTable'>
               <tr>
                  <th class='eventDetailsTaskAttribute'></th>
                  <th>Deleted task's values</th>
               </tr>
               <tr>
                  <td class='eventDetailsTaskAttribute'>Name: </td>
                  <td>" . $oldTaskData->name . "</td>
               </tr>
               <tr>
                  <td class='eventDetailsTaskAttribute'>Deadline: </td>
                  <td>" . $oldTaskData->deadLineDate . "</td>
               </tr>
               <tr>
                  <td class='eventDetailsTaskAttribute'>Duration: </td>
                  <td>" . $oldTaskData->durationsInHours . "h</td>
               </tr>
               <tr>
                  <td class='eventDetailsTaskAttribute'>Priority: </td>
                  <td>" . $priorityText . "</td>
               </tr>
               <tr>
                  <td class='eventDetailsTaskAttribute'>Description: </td>
                  <td>" . (empty($oldTaskData->description) ? "-" : $oldTaskData->description) . "</td>
               </tr>
            </table>";
         $eventId = $this->_getTable('EventTable')->addEvent(date("Y-m-d"), $message, $typeId, $details);
         // Link the new event to the current project.
         $this->_getTable("EventOnProjectsTable")->add($eventId, $projectId);
         // Finaly link the new event to the user who created it.
         $this->_getTable("EventUserTable")->add($sessionUser->id, $eventId);
         // Get event's data to send them to socket server.
         $event = $this->_getTable("ViewEventTable")->getEvent($eventId, false);

         try
         {
            $this->_sendRequest(array(
               "requestType"  => "newEvent",
               "event"        => json_encode($event)
            ));

            // Send a delete request to inform users which are currently in the task page.
            $this->_sendRequest(array(
               "requestType"  => "taskDeleted",
               "taskId"       => $taskId,
               "username"     => $sessionUser->username
            ));
         }
         catch (\Exception $e)
         {
            error_log("WARNING: could not connect to events servers. Maybe offline?");
         }
      }
      else
      {
         $resMessage = 'You do not have rights to delete this task !';
      }

      return $this->getResponse()->setContent(json_encode(array(
         'message' => $resMessage
      )));
   }

   public function addMemberAction()
   {
      $sessionUser = new container('user');
      $projectId = $this->params('id');

      if($this->_userIsAdminOfProject($sessionUser->id, $projectId))
      {
         $request = $this->getRequest();

         if($request->isPost())
         {
            // Get right event type.
            $typeId = $this->_getTable("EventTypeTable")->getTypeByName("Users")->id;

            foreach ($_POST as $value)
            {

               if($value != 'isManager' && !is_array($value))
               {
                  $isManager = isset($_POST['is-manager-'.$value]) ? true : false;
                  $this->_getTable('ProjectsUsersMembersTable')->addMemberToProject($value, $this->params('id'), $isManager);

                  $specializations = $_POST['spe'.$value];

                  $MAX_SPEC_PER_USER = 5;
                  $i = 0;
                  // Will contain each specialization separated with a comma.
                  $specializationsString = "";
                  foreach($specializations as $spe)
                  {
                     if($i++ >= $MAX_SPEC_PER_USER)
                        break;

                     if($spe != '')
                     {
                        $this->_getTable('ProjectsUsersSpecializationsTable')->addSpecialization($value, $this->params('id'), $spe);

                        if ($i > 1)
                        {
                           $specializationsString .= ", ";
                        }

                        $specializationsString .= "\"<b>" . $spe . "</b>\"";
                     }
                  }


                  // If member was successfully added, add an event.
                  // Get new member's data.
                  $addedMember = $this->_getTable("UserTable")->getUserById($value);
                  // Then add the new event in the database.
                  $message =
                     "<u>" . $sessionUser->username . "</u> added user <u>" . $addedMember->username .
                     "</u>" . ($isManager ? " (<b>manager</b>)" : "") . " with " .
                     ($specializationsString != "" ? ("specialization(s) " . $specializationsString) : "no specialization") . ".";
                  $eventId = $this->_getTable('EventTable')->addEvent(date("Y-m-d"), $message, $typeId);
                  // Link the new event to the current project.
                  $this->_getTable("EventOnProjectsTable")->add($eventId, $projectId);
                  // Finaly link the new event to the user who created it.
                  $this->_getTable("EventUserTable")->add($sessionUser->id, $eventId);
                  // Get event's data to send them to socket server.
                  $event = $this->_getTable("ViewEventTable")->getEvent($eventId, false);

                  try
                  {
                     $this->_sendRequest(array(
                        "requestType"  => "newEvent",
                        "event"        => json_encode($event)
                     ));
                  }
                  catch (\Exception $e)
                  {
                     error_log("WARNING: could not connect to events servers. Maybe offline?");
                  }
               }

            }


            $this->redirect()->toRoute('project', array(
                'id' => $projectId
            ));


         }

         $usersNotMemberOfProject = $this->_getUsersNotMemberOfProject($this->params('id'));

         return new ViewModel(array(
            'users' => $usersNotMemberOfProject
         ));
      }
      else
      {
         $this->redirect()->toRoute('project', array(
             'id' => $projectId
         ));
      }
   }

   public function removeMemberAction()
   {
      $sessionUser = new container('user');
      $projectId = $this->params('id');

      $memberId = $this->params('otherId');

      if($memberId != $sessionUser->id)
      {
         if($this->_userIsCreatorOfProject($sessionUser->id, $projectId) ||
            $this->_userIsAdminOfProject($sessionUser->id, $projectId) && !$this->_userIsAdminOfProject($memberId, $projectId))
         {

            // Remove from project
            $this->_getTable('ProjectsUsersMembersTable')->removeMember($memberId, $projectId);

            // Remove specializations
            $this->_getTable('ProjectsUsersSpecializationsTable')->deleteSpecialization($memberId, $projectId);

            // Remove all affectations in the project
            // Foreach tasks in the project, if the user is assigned we delete it
            $tasks = $this->_getTable('TaskTable')->getAllTasksInProject($projectId);
            foreach($tasks as $task)
            {
               $this->_getTable('UsersTasksAffectationsTable')->deleteAffectation($memberId, $task->id);
            }

            // If member was successfully removed, add an event.
            // Get removed member's data.
            $removedMember = $this->_getTable("UserTable")->getUserById($memberId);
            // Get right event type.
            $typeId = $this->_getTable("EventTypeTable")->getTypeByName("Users")->id;
            // Then add the new event in the database.
            $message = "<u>" . $sessionUser->username . "</u> removed user <u>" . $removedMember->username . "</u> from project, bye bye!";
            $eventId = $this->_getTable('EventTable')->addEvent(date("Y-m-d"), $message, $typeId);
            // Link the new event to the current project.
            $this->_getTable("EventOnProjectsTable")->add($eventId, $projectId);
            // Finaly link the new event to the user who created it.
            $this->_getTable("EventUserTable")->add($sessionUser->id, $eventId);
            // Get event's data to send them to socket server.
            $event = $this->_getTable("ViewEventTable")->getEvent($eventId, false);

            try
            {
               $this->_sendRequest(array(
                  "requestType"  => "newEvent",
                  "event"        => json_encode($event)
               ));

               // Send a remove request to redirect the the concerned user out of the project.
               $this->_sendRequest(array(
                  "requestType"  => "memberRemoved",
                  "projectId"    => $projectId,
                  "memberId"     => $memberId,
                  "username"     => $sessionUser->username
               ));
            }
            catch (\Exception $e)
            {
               error_log("WARNING: could not connect to events servers. Maybe offline?");
            }
         }
      }

      $this->redirect()->toRoute('project', array(
          'id' => $projectId
      ));
   }

   public function detailsAction()
   {
      $sessionUser = new container('user');
      $id = (int)$this->params('id');
      $projectDetails = $this->_getTable('ViewProjectDetailsTable')->getProjectDetails($id, $sessionUser->id);
      $members = $this->getMembersSpecializations($id);

      // Send the success message back with JSON.
      return new JsonModel(array(
         'success'        => true,
         'projectDetails' => $projectDetails,
         'members'        => $members
      ));
   }

   public function postNewsFeedAction()
   {
      $request = $this->getRequest();
      if ($request->isPost())
      {
         $sessionUser = new container('user');
         // Get request's parameters.
         $taskId = (int)$_POST['taskId'];
         $eventText = $_POST["text"];
         $typeId = $_POST["typeId"];

         // Add new data in the database.
         // First of all, add the new event in the database.
         $eventId = $this->_getTable('EventTable')->addEvent(date("Y-m-d"), $eventText, $typeId);
         // Link the new event to the current project.
         $this->_getTable("EventOnTaskTable")->add($eventId, $taskId);
         // Finaly link the new event to the user who created it.
         $this->_getTable("EventUserTable")->add($sessionUser->id, $eventId);
         // Get event's data to send them to socket server.
         $event = $this->_getTable("ViewEventTable")->getEvent($eventId, true);

         try
         {
            $this->_sendRequest(array(
               "requestType"  => "newEvent",
               "event"        => json_encode($event)
            ));
         }
         catch (\Exception $e)
         {
            error_log("WARNING: could not connect to events servers. Maybe offline?");
         }

         // Send the success message back with JSON.
         return new JsonModel(array(
            'success' => true
         ));
      }
      else
      {
         // Send the success message back with JSON.
         return new JsonModel(array(
            'success' => false
         ));
      }
   }

   /**
   * Send a POST request to the event's server
   *
   * @param array postParams : params want to send by post request
   */
   private function _sendRequest($postParams)
   {
      // Make an HTTP POST request to the event's server so he can broadcast a
      // new websocket related to the new event.
      $client = new Client('http://127.0.0.1:8002');
      $client->setMethod(Request::METHOD_POST);
      // Setting POST data.
      $client->setParameterPost($postParams);
      // Send HTTP request to server.
      $response = $client->send();
   }

   /**
   * Check if the user is assigned to the task passed in params.
   *
   * @param int userId : id of the user
   * @param int taskId : id of the task
   * @return true if the user is assigned to the task, false otherwise
   */
   private function _userIsAssignToTask($userId, $taskId)
   {
      $userTaskAffectation = $this->_getTable('UsersTasksAffectationsTable')->getAffectation($userId, $taskId);
      return !empty($userTaskAffectation);
   }

   /**
   * Check if the user is creator of the project passed in params.
   *
   * @param int userId : id of the user
   * @param int projectId : id of the project
   * @return true if the user is creator of the project, false otherwise
   */
   private function _userIsCreatorOfProject($userId, $projectId)
   {
      return $this->_getTable('ProjectTable')->getProject($projectId)->creator == $userId;
   }

   /**
   * Check if the user is administrator of the project passed in params.
   *
   * @param int userId : id of the user
   * @param int projectId : id of the project
   * @return true if the user is administrator of the project, false otherwise
   */
   private function _userIsAdminOfProject($userId, $projectId)
   {
      return $this->_getTable('ViewProjectMinTable')->userIsAdminOfProject($userId, $projectId);
   }

   /**
   * Returns users that are not member of the project passed in params.
   *
   * @param int projectId : id of the project
   * @return members of the project
   */
   private function _getUsersNotMemberOfProject($projectId)
   {
      // Get members of project and all users
      $members = $this->_getTable('ViewUsersProjectsTable')->getUsersInProject($projectId)->buffer();
      $users = $this->_getTable('UserTable')->getAllUsers()->buffer();

      // We put all users that not in the members array -> it's the members of the project
      $notMembersArray = array();
      foreach($users as $user)
      {
         // Don't show the SYSTEM user.
         if ($user->username != "SYSTEM")
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
      }

      return $notMembersArray;
   }
}
