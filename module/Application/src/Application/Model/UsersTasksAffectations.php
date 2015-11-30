<?php
namespace Application\Model;

// This class contains all data of a projects-members' mapping entity.
class UsersTasksAffectations
{
   public $user;
   public $task;

   public function exchangeArray($data)
   {
      $this->user  = (!empty($data['user'])) ? $data['user'] : null;
      $this->task  = (!empty($data['task'])) ? $data['task'] : null;
   }
}
