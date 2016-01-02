<?php

namespace Application\Controller;

// Calling some useful Zend's libraries.
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Application\Model\Tutorial;
use Zend\View\Model\ViewModel;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;

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
    
    public function taskDetailsAction() {
      return new JsonModel(
         (new Tutorial())->taskDetails()
      );
    }
}
