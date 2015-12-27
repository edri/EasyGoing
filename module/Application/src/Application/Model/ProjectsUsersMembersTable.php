<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

// Contains the methods that allows to work with a projects-members' mapping entity.
class ProjectsUsersMembersTable
{
   protected $_tableGateway;

   public function __construct(TableGateway $tableGateway)
   {
      $this->_tableGateway = $tableGateway;
   }

   public function getMemberRight($userId, $projectId)
   {
      $rowset = $this->_tableGateway->select(array(
         'user'      => $userId,
         'project'   => $projectId
      ));
      return $rowset->current();
   }

   // Add the given member to the given project.
   public function addMemberToProject($userId, $projectId, $isAdmin = false)
   {
      $this->_tableGateway->insert(array(
         "user"      => $userId,
         "project"   => $projectId,
         "isAdmin"   => $isAdmin
      ));
   }

   public function removeMember($userId, $projectId)
   {
      $this->_tableGateway->delete(array(
         "user"    => $userId,
         "project" => $projectId
      ));
   }
}
