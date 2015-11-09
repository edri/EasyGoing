<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

// Contains the methods that allows to work with a projects-members' mapping entity.
class ProjectsUsersMembersTable
{
	protected $tableGateway;

	public function __construct(TableGateway $tableGateway)
	{
		$this->tableGateway = $tableGateway;
	}

    // Add the given member to the given project.
	public function addMemberToProject($userId, $projectId, $isAdmin = false)
	{
		$this->tableGateway->insert(array(
            "user"      => $userId,
            "project"   => $projectId,
            "isAdmin"   => $isAdmin
        ));
	}
}
