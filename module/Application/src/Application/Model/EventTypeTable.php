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

   public function getTypeByName($name)
   {
      $rowset = $this->_tableGateway->select(array('type' => $name));
      return $rowset->current();
   }
}
