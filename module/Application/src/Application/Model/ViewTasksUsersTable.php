<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

// Contains the methods that allows to work with the mapping view between
// projects and users, with only data to show in the projects' list.
class ViewTasksUsersTable
{
   protected $_tableGateway;

   public function __construct(TableGateway $_tableGateway)
   {
      $this->_tableGateway = $_tableGateway;
   }

   public function getUsersAffectedOnTask($taskId) {
      return $this->_tableGateway->select(array(
         'task' => $taskId
      ))->buffer();
   }
}
