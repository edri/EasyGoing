<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

// Contains the methods that allows to work with the view which gives the members
// of a project with specializations.
class ViewProjectsMembersSpecializationsTable
{
	protected $tableGateway;

	public function __construct(TableGateway $tableGateway)
	{
		$this->tableGateway = $tableGateway;
	}

	// Return the given project's members with their specializations.
	public function getProjectMembers($projectId)
	{
		$arrayResults = array();
		$resultSet = $this->tableGateway->select(array("project" => $projectId));

		foreach ($resultSet as $row)
		{
			$arrayResults[] = $row;
		}

		return $arrayResults;
	}
}
