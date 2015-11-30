<?php
namespace Application\Model;

// This class contains data of a view which gives the members of a project with specializations.
class ViewProjectsMembersSpecializations
{
	public $project;
	public $username;
	public $specialization;
	public $isAdmin;

	public function exchangeArray($data)
	{
		$this->project  = (!empty($data['project'])) ? $data['project'] : null;
		$this->username  = (!empty($data['username'])) ? $data['username'] : null;
		$this->specialization  = (!empty($data['specialization'])) ? $data['specialization'] : null;
		$this->isAdmin  = (!empty($data['isAdmin'])) ? $data['isAdmin'] : null;
	}
}
