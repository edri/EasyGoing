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

   public function indexAction()
   {
      $project = $this->_getProjectTable()->getProject($this->params('id'));

      if(empty($project))
         $this->redirect()->toRoute('projects');

      return new ViewModel(array(
         'project' => $project
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
      return new ViewModel();
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


       // Send the success message back with JSON.
     $result = new JsonModel(array(
      'success' => true,
      'message' => $id
      ));

     return $result;
  }
}


?>
