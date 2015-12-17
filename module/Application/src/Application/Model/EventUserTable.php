<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

// Contains the methods that allows to work with a event-user mapping entity.
class EventUserTable
{
   protected $_tableGateway;

   public function __construct(TableGateway $tableGateway)
   {
      $this->_tableGateway = $tableGateway;
   }

   // Link the given user's ID to the given event's ID.
   public function add($userId, $eventId)
   {
      $this->_tableGateway->insert(array(
         "user"   => $userId,
         "event"  => $eventId
      ));
   }
}
