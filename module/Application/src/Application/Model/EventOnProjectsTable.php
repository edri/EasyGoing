<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

// Contains the methods that allows to work with an event-project mapping entity.
class EventOnProjectsTable
{
   protected $_tableGateway;

   public function __construct(TableGateway $tableGateway)
   {
      $this->_tableGateway = $tableGateway;
   }

   // Link the given event's ID to the given project's ID.
   public function add($eventId, $projectId)
   {
      $this->_tableGateway->insert(array(
         "event"      => $eventId,
         "project"   => $projectId
      ));
   }
}
