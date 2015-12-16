<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

// Contains the methods that allows to work with a event-user mapping entity.
class ProjectsUsersMembersTable
{
   protected $_tableGateway;

   public function __construct(TableGateway $tableGateway)
   {
      $this->_tableGateway = $tableGateway;
   }
}
