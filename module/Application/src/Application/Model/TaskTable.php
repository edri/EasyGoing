<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

// Contains the methods that allows to work with a task entity.
class TaskTable
{
   protected $_tableGateway;

   public function __construct(TableGateway $tableGateway)
   {
      $this->_tableGateway = $tableGateway;
   }

   public function getTaskById($id)
   {
      return $this->_tableGateway->select(array(
         'id' => $id
      ))->current();
   }

   public function getSubtasks($parentId)
   {
      return $this->_tableGateway->select(array(
         'parentTask' => $parentId
      ))->buffer();
   }

   public function updateTask($name, $description, $deadlineDate, $durationsInHours, $priorityLevel, $taskId)
   {
      $this->_tableGateway->update(array(
            'name'               => $name,
            'description'        => $description,
            'deadLineDate'       => $deadlineDate,
            'durationsInHours'   => $durationsInHours,
            'priorityLevel'      => $priorityLevel
         ), array(
            'id' => $taskId
         ));
   }


   public function addTask($name, $description, $deadlineDate, $durationsInHours, $priorityLevel, $projectId, $parentTask = null)
   {
      $this->_tableGateway->insert(array(
         'name'               => $name,
         'description'        => $description,
         'deadLineDate'       => $deadlineDate,
         'durationsInHours'   => $durationsInHours,
         'priorityLevel'      => $priorityLevel,
         'project'            => $projectId,
         'parentTask'         => $parentTask
      ));

      return $this->_tableGateway->lastInsertValue;
   }

   public function getAllTasksInProject($projectId)
   {
      return $this->_tableGateway->select(array(
         'project' => $projectId
      ))->buffer();
   }

   public function getAllParentTasksInProject($projectId)
   {
      return $this->_tableGateway->select(array(
         'project'    => $projectId,
         'parentTask' => null
      ))->buffer();
   }

   public function updateStateOfTask($taskId, $newState)
   {
      $this->_tableGateway->update(array('state' => $newState), array('id' => $taskId));
   }
   
   public function deleteTask($taskId)
   {
      $this->_tableGateway->delete(array(
         'id' => $taskId
      ));
   }
}
