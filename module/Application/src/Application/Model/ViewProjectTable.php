<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

// Contains the methods that allows to work with the mapping view between
// projects and users.
class ViewProjectTable
{
	protected $tableGateway;

	public function __construct(TableGateway $tableGateway)
	{
		$this->tableGateway = $tableGateway;
	}

	// Get and return the list of an user's projects, by its ID.
	public function getUserProjects($userId)
	{
		$resultSet = $this->tableGateway->select(function (Select $select) use ($userId)
		{
			$where = new Where();
			$select->where(array('userId' => $userId));
			$select->order("name");
		});

		return $resultSet;
	}
}
