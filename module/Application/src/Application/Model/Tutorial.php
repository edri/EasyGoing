<?php
namespace Application\Model;

// This class contains all of the data for tutorial
class Tutorial
{
   private function _generateData($div, $text) 
   {
      return array('div' => $div, 'text' => $text);
   }

   public function tutorialProjectsIndex() 
   {
      return array(
         $this->_generateData('listOfProjects', 'This is the list of the projects you are affected in.
          Click on a project to see details.'),
         $this->_generateData('createProject', 'Here you can create a new project.'),
         $this->_generateData('searchProject', 'Here you can search a project.')
      );
   }
}
