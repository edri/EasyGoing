<?php
namespace Application\Model;

// This class contains all data of a projects-members' mapping entity.
class ProjectsUsersSpecializations
{
   public $user;
   public $project;
   public $specialization;

   public function exchangeArray($data)
   {
      $this->user  = (!empty($data['user'])) ? $data['user'] : null;
      $this->task  = (!empty($data['task'])) ? $data['task'] : null;
      $this->specialization  = (!empty($data['specialization'])) ? $data['specialization'] : null;
   }
   

}
