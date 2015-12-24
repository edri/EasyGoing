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
   public function getEvent($eventId, $isTaskEvent)
   {
      $rowset = $this->_tableGateway->select(array(
         "id"           => $eventId,
         "isTaskEvent"  => $isTaskEvent
      ));
      return $rowset->current();
   }

   // Get and return as an array the events list of the given project or task.
   // Parameters:
   //    linkedEntityId: the project or task's ID, depending on the second parameter.
   //    isTaskEvent: indicate whether the function return project's events (false) or
   //                 task's events (true).
   public function getEntityEvents($linkedEntityId, $isTaskEvent)
   {
      $arrayResults = array();
      $resultSet = $this->_tableGateway->select(array(
         "linkedEntityId"  => $linkedEntityId,
         "isTaskEvent"     => $isTaskEvent
      ));

      foreach ($resultSet as $row)
         $arrayResults[] = $row;

      return $arrayResults;
   }
}
