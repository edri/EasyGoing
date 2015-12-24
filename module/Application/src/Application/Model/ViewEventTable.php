<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

// Contains the methods that allows to work with an entity of the view referencing all event's properties.
class ViewEventTable
{
   protected $_tableGateway;

   public function __construct(TableGateway $tableGateway)
   {
      $this->_tableGateway = $tableGateway;
   }

   // Get and return the event which match with the given ID.
   public function getEvent($eventId)
   {
      $rowset = $this->_tableGateway->select(array('id' => $eventId));
      return $rowset->current();
   }

   // Get and return  as an array the events list of the given project.
   public function getProjectEvents($projectId)
   {
      $arrayResults = array();
      $resultSet = $this->_tableGateway->select(array("project" => $projectId));

      foreach ($resultSet as $row)
         $arrayResults[] = $row;

      return $arrayResults;
   }
}
