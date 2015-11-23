<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

// Contains the methods that allows to work with the mapping view between
// projects' details and users, with all important project's data.
class ViewProjectDetailsTable
{
	protected $tableGateway;

	public function __construct(TableGateway $tableGateway)
	{
		$this->tableGateway = $tableGateway;
	}

	// Get and return the details of the given project.
	// The given user's ID is here to check the connected user can acces this
	// project's details.
	public function getProjectDetails($projectId, $userId)
	{
		$rowset = $this->tableGateway->select(array("projectId" => $projectId, "userId" => $userId));
		$row = $rowset->current();
		return $row;
	}
}
