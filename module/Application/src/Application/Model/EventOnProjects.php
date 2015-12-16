<?php
namespace Application\Model;

// This class contains all data of an event-project mapping entity.
class EventOnProjects
{
   public $event;
   public $project;

   public function exchangeArray($data)
   {
      $this->event  = (!empty($data['event'])) ? $data['event'] : null;
      $this->project  = (!empty($data['project'])) ? $data['project'] : null;
   }
}
