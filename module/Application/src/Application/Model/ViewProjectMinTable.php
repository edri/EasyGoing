<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

// Contains the methods that allows to work with the mapping view between
// projects and users, with only data to show in the projects' list.
class ViewProjectMinTable
{
   protected $_tableGateway;

   public function __construct(TableGateway $tableGateway)
   {
      $this->_tableGateway = $tableGateway;
   }

   // Get and return the list of an user's projects, by its ID.
   public function getUserProjects($userId)
   {
      $resultSet = $this->_tableGateway->select(array("userId" => $userId));
      return $resultSet;
   }
}
