<?php
namespace Application\Model;

// This class contains data of an view mapping projects and users, but containing
// only the data to display in the projects' list.
// Data showing when the user clicks on a project come from the ViewProjectAllData
// view.
class ViewProjectMin
{
   public $id;
   public $name;
   public $fileLogo;
   public $userId;
   public $isAdmin;
   public $creator;

   public function exchangeArray($data)
   {
      $this->id  = (!empty($data['id'])) ? $data['id'] : null;
      $this->name  = (!empty($data['name'])) ? $data['name'] : null;
      $this->fileLogo  = (!empty($data['fileLogo'])) ? $data['fileLogo'] : null;
      $this->userId  = (!empty($data['userId'])) ? $data['userId'] : null;
      $this->isAdmin  = (!empty($data['isAdmin'])) ? $data['isAdmin'] : null;
      $this->creator  = (!empty($data['creator'])) ? $data['creator'] : null; 
   }
}
