<?php
namespace Application\Model;

// This class contains all data of a event-user mapping entity.
class EventUser
{
   public $user;
   public $event;

   public function exchangeArray($data)
   {
      $this->user  = (!empty($data['user'])) ? $data['user'] : null;
      $this->event  = (!empty($data['event'])) ? $data['event'] : null;
   }
}
