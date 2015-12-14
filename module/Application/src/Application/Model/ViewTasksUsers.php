<?php

/*
   SELECT * FROM users INNER JOIN usersTasksAffectations ON users.id = usersTasksAffectations.user
*/

namespace Application\Model;

// This class contains data of an view mapping projects and users, but containing
// only the data to display in the projects' list.
// Data showing when the user clicks on a project come from the ViewProjectAllData
// view.
class ViewTasksUsers
{
   public $id;
   public $email;
   public $username;
   public $hashedPassword;
   public $firstName;
   public $lastName;
   public $filePhoto;
   public $wantTutorial;
   public $wantNotification;
   public $task;

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
      $this->wantNotification  = (!empty($data['wantNotification'])) ? $data['wantNotification'] : null;
      $this->task  = (!empty($data['task'])) ? $data['task'] : null;
   }
}