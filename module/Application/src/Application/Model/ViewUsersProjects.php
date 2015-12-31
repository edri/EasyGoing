<?php
namespace Application\Model;

// This class contains data of an view mapping projects and users, but containing
// only the data to display in the projects' list.
// Data showing when the user clicks on a project come from the ViewProjectAllData
// view.
class ViewUsersProjects
{
   public $id;
   public $email;
   public $username;
   public $firstName;
   public $lastName;
   public $filePhoto;
   public $projectId;
   public $isAdmin;

   public function exchangeArray($data)
   {
      $this->id  = (!empty($data['id'])) ? $data['id'] : null;
      $this->email  = (!empty($data['email'])) ? $data['email'] : null;
      $this->username  = (!empty($data['username'])) ? $data['username'] : null;
      $this->firstName  = (!empty($data['firstName'])) ? $data['firstName'] : null;
      $this->lastName  = (!empty($data['lastName'])) ? $data['lastName'] : null;
      $this->filePhoto  = (!empty($data['filePhoto'])) ? $data['filePhoto'] : null;
      $this->projectId  = (!empty($data['projectId'])) ? $data['projectId'] : null;
      $this->isAdmin  = (!empty($data['isAdmin'])) ? $data['isAdmin'] : null;
   }
}
