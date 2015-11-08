<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

// Contains the methods that allows to work with a task entity.
class TaskTable
{
   protected $tableGateway;

   public function __construct(TableGateway $tableGateway)
   {
      $this->tableGateway = $tableGateway;
   }
}
