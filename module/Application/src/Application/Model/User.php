<?php
namespace Application\Model;

// This class contains all data of an user's entity.
class User
{
	public $id;
	public $email;
	public $username;
	public $hashedPassword;
	public $firstName;
	public $lastName;
	public $filePhoto;
	public $wantTutorial;
	public $wantNotifications;

	public function exchangeArray($data)
	{
		$this->id  = (!empty($data['id'])) ? $data['id'] : null;
		$this->email  = (!empty($data['email'])) ? $data['email'] : null;
		$this->username  = (!empty($data['username'])) ? $data['username'] : null;
		$this->hashedPassword  = (!empty($data['hashedPassword'])) ? $data['hashedPassword'] : null;
		$this->firstName  = (!empty($data['firstName'])) ? $data['firstName'] : null;
		$this->lastName  = (!empty($data['lastName'])) ? $data['lastName'] : null;
		$this->filePhoto  = (!empty($data['filePhoto'])) ? $data['filePhoto'] : null;
		$this->wantTutorial  = (!empty($data['wantTutorial'])) ? $data['wantTutorial'] : null;
		$this->wantNotifications  = (!empty($data['wantNotifications'])) ? $data['wantNotifications'] : null;
	}
}
