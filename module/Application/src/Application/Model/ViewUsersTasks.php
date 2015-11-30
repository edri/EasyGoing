<?php
namespace Application\Model;

// This class contains data of an view mapping projects and users, but containing
// only the data to display in the projects' list.
// Data showing when the user clicks on a project come from the ViewProjectAllData
// view.
class ViewUsersTasks
{
   public $id;
   public $name;
   public $description;
   public $deadLineDate;
   public $durationInHours;
   public $priorityLevel;
   public $state;
   public $project;
   public $userId;

   public function exchangeArray($data)
   {
      $this->id  = (!empty($data['id'])) ? $data['id'] : null;
      $this->name  = (!empty($data['name'])) ? $data['name'] : null;
      $this->description  = (!empty($data['description'])) ? $data['description'] : null;
      $this->deadLineDate  = (!empty($data['deadLineDate'])) ? $data['deadLineDate'] : null;
      $this->durationInHours  = (!empty($data['durationInHours'])) ? $data['durationInHours'] : null;
      $this->priorityLevel  = (!empty($data['priorityLevel'])) ? $data['priorityLevel'] : null;
      $this->state  = (!empty($data['state'])) ? $data['state'] : null;
      $this->project  = (!empty($data['project'])) ? $data['project'] : null;
      $this->userId  = (!empty($data['userId'])) ? $data['userId'] : null;
   }
}
