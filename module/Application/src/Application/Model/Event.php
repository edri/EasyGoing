<?php
namespace Application\Model;

// This class contains all data of an event entity.
class Event
{
   public $id;
   public $date;
   public $message;
   public $eventType;

   public function exchangeArray($data)
   {
      $this->id  = (!empty($data['id'])) ? $data['id'] : null;
      $this->date  = (!empty($data['date'])) ? $data['date'] : null;
      $this->message  = (!empty($data['message'])) ? $data['message'] : null;
      $this->eventType  = (!empty($data['eventType'])) ? $data['eventType'] : null;
   }
}
