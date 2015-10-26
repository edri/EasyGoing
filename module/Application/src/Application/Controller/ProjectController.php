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
use Zend\View\Model\ViewModel;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;

class ProjectController extends AbstractActionController
{
   public function indexAction()
   {
      return new ViewModel(array(
            'title'       => 'Index projet ' . $this->params('id'),
            'description' => 'Description projet'
         ));
   }

   public function addMemberAction()
   {
      return new ViewModel();
   }

   public function addTaskAction()
   {
      return new ViewModel();
   }
   
}


?>