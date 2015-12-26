<?php
namespace Application\Model;

// This class contains all of the data for tutorial
class Tutorial
{
   private function _generateData($div, $text) 
   {
      return array('div' => $div, 'text' => $text);
   }

   public function projectsTutorial() 
   {
      return array(
         $this->_generateData('salut', 'Salut les amis'),
         $this->_generateData('bonjour', 'Bonjour les gens'),
         $this->_generateData('coucou', 'Bonjour Président !')
      );
   }
}
