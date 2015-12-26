<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

// The namespace is important. It avoids us from being forced to call the Zend's methods with
// "Application\Controller" before.
namespace Application\Controller;

// Calling some useful Zend's libraries.
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;
use Application\Model\Tutorial;

// Default controller ; will be calling when the user access the "easygoing/" page.

// Be careful about the class' name, which must be the same as the file's name.
class TutorialController extends AbstractActionController
{     
   public function tutorialAction()        
   {        
      return new JsonModel(
         (new Tutorial())->getTutorialData()
      );
   }
}
