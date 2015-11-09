<?php
namespace Application\Model;

// This class contains all data of a project's entity.
class Project
{
	public $id;
	public $name;
	public $description;
	public $startDate;
	public $deadLineDate;
	public $fileLogo;

	public function exchangeArray($data)
	{
		$this->id  = (!empty($data['id'])) ? $data['id'] : null;
		$this->name  = (!empty($data['name'])) ? $data['name'] : null;
		$this->description  = (!empty($data['description'])) ? $data['description'] : null;
		$this->startDate  = (!empty($data['startDate'])) ? $data['startDate'] : null;
		$this->deadLineDate  = (!empty($data['deadLineDate'])) ? $data['deadLineDate'] : null;
		$this->fileLogo  = (!empty($data['fileLogo'])) ? $data['fileLogo'] : null;
	}
}
