<?php
namespace Application\Model;

// This class contains all data of a task's entity.
class Task
{
   public $id;
   public $name;
   public $description;
   public $deadLineDate;
   public $durationsInHours;
   public $priorityLevel;
   public $state;
   public $project;
   public $parentTask;

   public function exchangeArray($data)
   {
      $this->id  = (!empty($data['id'])) ? $data['id'] : null;
      $this->name  = (!empty($data['name'])) ? $data['name'] : null;
      $this->description  = (!empty($data['description'])) ? $data['description'] : null;
      $this->deadLineDate  = (!empty($data['deadLineDate'])) ? $data['deadLineDate'] : null;
      $this->durationsInHours  = (!empty($data['durationsInHours'])) ? $data['durationsInHours'] : null;
      $this->priorityLevel  = (!empty($data['priorityLevel'])) ? $data['priorityLevel'] : null;
      $this->state  = (!empty($data['state'])) ? $data['state'] : null;
      $this->project  = (!empty($data['project'])) ? $data['project'] : null;
      $this->parentTask  = (!empty($data['parentTask'])) ? $data['parentTask'] : null;
   }
}
