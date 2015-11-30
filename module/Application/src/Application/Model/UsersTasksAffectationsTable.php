<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

// Contains the methods that allows to work with a projects-members' mapping entity.
class UsersTasksAffectationsTable
{
   protected $tableGateway;

   public function __construct(TableGateway $tableGateway)
   {
      $this->tableGateway = $tableGateway;
   }

   public function updateTaskAffectation($userId, $taskId) 
   {
      
   }
}
