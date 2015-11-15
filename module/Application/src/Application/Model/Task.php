<?php
namespace Application\Model;

// This class contains all data of a task's entity.
class Task
{
   public $id;
   public $name;
   public $description;
   public $deadLineDate;
   public $durationInHours;
   public $priorityLevel;
   public $project;

   public function exchangeArray($data)
   {
      $this->id  = (!empty($data['id'])) ? $data['id'] : null;
      $this->name  = (!empty($data['name'])) ? $data['name'] : null;
      $this->description  = (!empty($data['description'])) ? $data['description'] : null;
      $this->deadLineDate  = (!empty($data['deadLineDate'])) ? $data['deadLineDate'] : null;
      $this->durationInHours  = (!empty($data['durationInHours'])) ? $data['durationInHours'] : null;
      $this->priorityLevel  = (!empty($data['priorityLevel'])) ? $data['priorityLevel'] : null;
      $this->project  = (!empty($data['project'])) ? $data['project'] : null;
   }
}
