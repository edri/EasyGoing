<?php

namespace Application\Controller;

// Calling some useful Zend's libraries.
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Application\Model\Tutorial;

class TutorialController extends AbstractActionController
{     
    public function projectsAction()        
    {        
      return new JsonModel(
         (new Tutorial())->projects()
      );
    }
    
    public function projectAction()        
    {        
      return new JsonModel(
         (new Tutorial())->project()
      );
    }
    
    public function taskDetailsAction() 
    {
      return new JsonModel(
         (new Tutorial())->taskDetails()
      );
    }
}
