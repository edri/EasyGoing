<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

// Contains the methods that allows to work with an event-task mapping entity.
class EventOnTaskTable
{
   protected $_tableGateway;

   public function __construct(TableGateway $tableGateway)
   {
      $this->_tableGateway = $tableGateway;
   }

   // Link the given event's ID to the given task's ID.
   public function add($eventId, $taskId)
   {
      $this->_tableGateway->insert(array(
         "event"  => $eventId,
         "task"   => $taskId
      ));
   }
}
