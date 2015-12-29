<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

// Contains the methods that allows to work with a users-tasks's mapping entity.
class ProjectsUsersSpecializationsTable
{
   protected $_tableGateway;

   public function __construct(TableGateway $tableGateway)
   {
      $this->_tableGateway = $tableGateway;
   }

   public function addSpecialization($userId, $projectId, $specialization)
   {
      $this->_tableGateway->insert(array(
         "user"             => $userId,
         "project"          => $projectId,
         "specialization"   => $specialization
      ));
   }
   
   public function deleteSpecialization($userId, $projectId)
   {
      $this->_tableGateway->delete(array(
         "user"    => $userId,
         "project" => $projectId
      ));
   }
}
