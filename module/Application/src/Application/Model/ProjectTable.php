<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

// Contains the methods that allows to work with a project entity.
class ProjectTable
{
   protected $_tableGateway;

   public function __construct(TableGateway $tableGateway)
   {
      $this->_tableGateway = $tableGateway;
   }

   public function getProject($id)
   {
      $rowset = $this->_tableGateway->select(array('id' => $id));
      return $rowset->current();
   }

   // Add a project in the database.
   public function saveProject($data)
   {
      $this->_tableGateway->insert($data);
      // Return new project's ID.
      return $this->_tableGateway->lastInsertValue;
   }

   // Edit the given project's ID with the given data.
   public function editProject($projectId, $data)
   {
      $this->_tableGateway->update(
         $data,
         array(
            'id' => $projectId
         ));
   }
}
