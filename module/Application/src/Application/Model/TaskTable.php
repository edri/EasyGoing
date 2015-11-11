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

   public function addTask($name, $description, $deadlineDate, $durationsInHours, $priorityLevel, $projectId)
   {
      $this->_tableGateway->insert(array(
         'name'               => $name,
         'description'        => $description,
         'deadLineDate'       => $deadlineDate,
         'durationsInHours'   => $durationsInHours,
         'priorityLevel'      => $priorityLevel,
         'project'            => $projectId
      ));

      return $this->_tableGateway->lastInsertValue;
   }

   public function getAllTasksInProject($projectId)
   {
      return $this->_tableGateway->select(array(
         'project' => $projectId
      ))->buffer();
   }
}
