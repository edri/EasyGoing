<?php
namespace Application\Model;

// This class contains all data of a projects-members' mapping entity.
class ProjectsUsersMembers
{
	public $user;
	public $project;
	public $isAdmin;

	public function exchangeArray($data)
	{
		$this->user  = (!empty($data['user'])) ? $data['user'] : null;
		$this->project  = (!empty($data['project'])) ? $data['project'] : null;
		$this->isAdmin  = (!empty($data['isAdmin'])) ? $data['isAdmin'] : null;
	}
}
