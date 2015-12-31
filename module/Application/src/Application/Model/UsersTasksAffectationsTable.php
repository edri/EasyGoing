<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

// Contains the methods that allows to work with a users-tasks's mapping entity.
class UsersTasksAffectationsTable
{
   protected $_tableGateway;

   public function __construct(TableGateway $tableGateway)
   {
      $this->_tableGateway = $tableGateway;
   }

   public function getAffectation($userId, $taskId)
   {
      return $this->_tableGateway->select(array(
         'user' => $userId,
         'task' => $taskId
      ))->current();
   }

   // Get an affectation by its task's ID.
   public function getAffectationByTaskId($taskId)
   {
      return $this->_tableGateway->select(array(
         'task' => $taskId
      ))->current();
   }

   public function updateTaskAffectation($userId, $taskId, $newUserId)
   {
      $this->_tableGateway->update(array(
         'user' => $newUserId
      ), array(
         'task' => $taskId,
         'user' => $userId
      ));
   }

   public function addAffectation($userId, $taskId)
   {
      $this->_tableGateway->insert(array(
         'user' => $userId,
         'task' => $taskId
      ));

      return $this->_tableGateway->lastInsertValue;
   }

   public function deleteAffectation($userId, $taskId)
   {
      $this->_tableGateway->delete(array(
         'user' => $userId,
         'task' => $taskId
      ));
   }
}
