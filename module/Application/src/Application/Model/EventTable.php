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
}
