<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

// Contains the methods that allows to work with a task entity.
class TaskTable
{
   protected $tableGateway;

   public function __construct(TableGateway $tableGateway)
   {
      $this->tableGateway = $tableGateway;
   }

   public function test()
   {
      echo 'test';
   }

   public function addTask($name, $description, $deadlineDate, $durationsInHours, $priorityLevel, $projectId)
   {
      $this->tableGateway->insert(array(
         'name'               => $name,
         'description'        => $description,
         'deadLineDate'       => $deadlineDate,
         'durationsInHours'   => $durationsInHours,
         'priorityLevel'      => $priorityLevel,
         'project'            => $projectId
      ));

      return $this->tableGateway->lastInsertValue;
   }
}
