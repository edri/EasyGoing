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

   public function updateTaskAffectation($userId, $taskId) 
   {
      $this->_tableGateway->update(array('user' => $userId), array('task' => $taskId));
   }

   public function addAffectation($userId, $taskId)
   {
      $this->_tableGateway->insert(array(
         'user' => $userId,
         'task' => $taskId
      ));

      return $this->_tableGateway->lastInsertValue;   
   }
}
