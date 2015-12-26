<?php
namespace Application\Model;

// This class contains all of the data for tutorial
class Tutorial
{
   private function _generateData($div, $text) 
   {
      return array('div' => $div, 'text' => $text);
   }

   public function tutorialProjects() 
   {
      return array(
         $this->_generateData('project1', 'Salut les amis'),
         $this->_generateData('project2', 'Bonjour les gens'),
         $this->_generateData('project3', 'Bonjour Président !')
      );
   }
    
    public function tutorialUsers() {
        return array(
         $this->_generateData('user1', 'User 1'),
         $this->_generateData('user2', 'User 2'),
         $this->_generateData('user3', 'User 3')
      );
    }
}
