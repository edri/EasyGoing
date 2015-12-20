<?php
namespace Application\Model;

// This class contains all data of an event's type entity.
class EventType
{
   public $id;
   public $type;
   public $fileLogo;
   public $isTaskTag;

   public function exchangeArray($data)
   {
      $this->id  = (!empty($data['id'])) ? $data['id'] : null;
      $this->type  = (!empty($data['type'])) ? $data['type'] : null;
      $this->fileLogo  = (!empty($data['fileLogo'])) ? $data['fileLogo'] : null;
      $this->isTaskTag  = (!empty($data['isTaskTag'])) ? $data['isTaskTag'] : null;
   }
}
