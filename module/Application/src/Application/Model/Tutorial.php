<?php
namespace Application\Model;

// This class contains all data of an user's entity.
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
            $this->_generateData('bonjour', 'Bonjour les gens')
        );
    }
    
}
