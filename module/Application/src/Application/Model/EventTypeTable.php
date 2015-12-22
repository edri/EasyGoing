<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

// Contains the methods that allows to work with an event's type entity.
class EventTypeTable
{
   protected $_tableGateway;

   public function __construct(TableGateway $tableGateway)
   {
      $this->_tableGateway = $tableGateway;
   }

   // Get all types of projects or tasks.
   // Parameter:
   //    isTaskTag: indicate whether the returned types list is the one linked
   //                  to projects (false) or to tasks (true);
   public function getTypes($isTaskTag)
   {
      $arrayResults = array();
      $resultSet = $this->_tableGateway->select(array(
         "isTaskTag"  => $isTaskTag
      ));

      foreach ($resultSet as $row)
         $arrayResults[] = $row;

      return $arrayResults;
   }

   // Get a type entity by its name.
   public function getTypeByName($name)
   {
      $rowset = $this->_tableGateway->select(array('type' => $name));
      return $rowset->current();
   }
}
