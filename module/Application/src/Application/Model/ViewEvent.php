<?php
namespace Application\Model;

// This class contains all data of an entity of the view referencing all event's properties.
class ViewEvent
{
   public $type;
   public $fileLogo;
   public $id;
   public $date;
   public $message;
   public $details;
   public $username;
   public $linkedEntityId;
   public $isTaskEvent;

   public function exchangeArray($data)
   {
      $this->type  = (!empty($data['type'])) ? $data['type'] : null;
      $this->fileLogo  = (!empty($data['fileLogo'])) ? $data['fileLogo'] : null;
      $this->id  = (!empty($data['id'])) ? $data['id'] : null;
      $this->date  = (!empty($data['date'])) ? $data['date'] : null;
      $this->message  = (!empty($data['message'])) ? $data['message'] : null;
      $this->details  = (!empty($data['details'])) ? $data['details'] : null;
      $this->username  = (!empty($data['username'])) ? $data['username'] : null;
      $this->linkedEntityId  = (!empty($data['linkedEntityId'])) ? $data['linkedEntityId'] : null;
      $this->isTaskEvent  = (!empty($data['isTaskEvent'])) ? $data['isTaskEvent'] : null;
   }
}
