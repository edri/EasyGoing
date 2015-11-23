<?php
namespace Application\Model;

// This class contains data of an view mapping projects' details and users, with all important
// project's data.
class ViewProjectDetails
{
	public $projectId;
	public $name;
	public $description;
	public $startDate;
	public $deadlineDate;
	public $userId;

	public function exchangeArray($data)
	{
		$this->projectId  = (!empty($data['projectId'])) ? $data['projectId'] : null;
		$this->name  = (!empty($data['name'])) ? $data['name'] : null;
		$this->description  = (!empty($data['description'])) ? $data['description'] : null;
		$this->startDate  = (!empty($data['startDate'])) ? $data['startDate'] : null;
		$this->deadlineDate  = (!empty($data['deadlineDate'])) ? $data['deadlineDate'] : null;
		$this->userId  = (!empty($data['userId'])) ? $data['userId'] : null;
	}
}
