<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

// Contains the methods that allows to work with an event entity.
class EventTable
{
   protected $_tableGateway;

   public function __construct(TableGateway $tableGateway)
   {
      $this->_tableGateway = $tableGateway;
   }

   // Add the given event in the database.
   public function addEvent($date, $message, $eventType, $details = null)
   {
      $this->_tableGateway->insert(array(
         "date"      => $date,
         "message"   => $message,
         "eventType" => $eventType,
         "details"   => $details
      ));
      // Return new event's ID.
      return $this->_tableGateway->lastInsertValue;
   }
}
