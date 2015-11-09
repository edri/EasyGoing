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

	// Add a project in the database.
	public function saveProject($data)
	{
		$this->tableGateway->insert($data);
        return $this->tableGateway->lastInsertValue;
	}
}
