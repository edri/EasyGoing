<?php
namespace Application\Model;

// This class contains all of the data for tutorial
class Tutorial
{
   private function _generateData($div, $text) 
   {
      return array('div' => $div, 'text' => $text);
   }

   public function projects() 
   {
        return array(
         $this->_generateData('listOfProjects', 'This is the list of the projects you are affected in.
          Click on a project to see details.'),
         $this->_generateData('createProject', 'Here you can create a new project.'),
         $this->_generateData('searchProject', 'Here you can search a project.')
        );
   }
    
    public function project() {
        return array(
            $this->_generateData('addTask', 'Here you can add a task in your project.'),
            $this->_generateData('addMember', 'Here you can add a member in your project.'),
            $this->_generateData('historical', 'This is the historical of the project'),
            $this->_generateData('dashboardType', 'Here you can select the type of dashboard you want')
        );
    }
    
    public function taskDetails() {
        return array(
            $this->_generateData('news', 'Here you can see the news concerning this task')
        );
    }
}
