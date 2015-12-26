<?php
namespace Application\Model;

// This class contains all data of an event-task mapping entity.
class EventOnTask
{
   public $event;
   public $task;

   public function exchangeArray($data)
   {
      $this->event  = (!empty($data['event'])) ? $data['event'] : null;
      $this->task  = (!empty($data['task'])) ? $data['task'] : null;
   }
}
