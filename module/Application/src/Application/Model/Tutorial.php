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
         $this->_generateData('hello', "Well, hello you!<br/>It seems that you're new here... Huh... Don't worry I'll be there for you during your first time on Easygoing, and even more often if affinities!"),
         $this->_generateData('createProject', 'Okay first thing first, this is the link that allows you to create your <b>own</b> project &#9829;.'),
         $this->_generateData('tableListProjects', 'Here is the list of the projects you are affected in.<br/>Click on a project to see its details.'),
         $this->_generateData('searchProject', "Oh, and there you can quickly search for a project.<br/>I'm done for this page, see you!"),
        );
   }

    public function project()
    {
        return array(
            $this->_generateData('addTask', 'Hello again!<br/>Here you can add a task in your project.'),
            $this->_generateData('addMember', 'And there you can obviously add a member in your project.'),
            $this->_generateData('historical', "<u>This</u> is the historical of the project. It will be automatically filled when you or other members will do actions so don't mess with this little guy..."),
            $this->_generateData('dashboardType', 'Here you can select the type of dashboard you want. Be careful about the <b>View per tasks</b> type, which is read-only.'),
            $this->_generateData('showSpecializations', 'You can show each project\'s members\' specialization(s) or not. The choice you\'ll do will be stored for the future.<br/>So, <font color="#D93D3D">red</font> pill or <font color="#3DB2D9">blue</font> pill?'),
            $this->_generateData('board-container', 'And now, ladies and gentlemen, <i>theeere</i> is the project\'s dashboard ! All project\'s tasks are in there and members can move them.'),
            $this->_generateData('listTasks', 'Here is the list of tasks of the project...'),
            $this->_generateData('listMembers', '...and here is the list of members of the project.<br/>See you!')
        );
    }

    public function taskDetails()
    {
        return array(
           $this->_generateData('news', 'There you can see the news feed concerning this task...'),
           $this->_generateData('newsText', ".. and here you can write a new post. First select a tag and then write whatever you want: just let your imagination float in the meanders of your mind, but write something useful otherwise the project's manager will be mad about my bad advices!")
        );
    }
}
