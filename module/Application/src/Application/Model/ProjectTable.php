<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

// Contains the methods that allows to work with a project entity.
class ProjectTable
{
	protected $tableGateway;

	public function __construct(TableGateway $tableGateway)
	{
		$this->tableGateway = $tableGateway;
	}

   public function getProject($id)
   {
      $rowset = $this->tableGateway->select(array('id' => $id));
      return $rowset->current();
   }
}
