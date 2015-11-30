<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

// Contains the methods that allows to work with the mapping view between
// projects and users, with only data to show in the projects' list.
class ViewUsersProjectsTable
{
   protected $_tableGateway;

   public function __construct(TableGateway $_tableGateway)
   {
      $this->_tableGateway = $_tableGateway;
   }

   public function getUsersInProject($projectId)
   {
      return $this->_tableGateway->select(array("project" => $projectId))->buffer();
   }
}
