<?php
namespace Application\Model;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
// Contains the methods that allows to work with an user entity.
class UserTable
{
   protected $_tableGateway;
   public function __construct(TableGateway $tableGateway)
   {
      $this->_tableGateway = $tableGateway;
   }
   // Check an user's creditentials, to ensure it logged in correctly.
   public function checkCreditentials($username, $hashedPassword)
   {
      // Try to get the row that matchs with the given username and hashed
      // password.
      $rowset = $this->_tableGateway->select(array(
         'username' => $username,
         'hashedPassword' => $hashedPassword
      ));
      $row = $rowset->current();
      // Return true or false, depending on the given creditentials'
      // correctness.
      if(isset($row->id))
         return $row;
      else
         return null;
   }
   // Checks if the given e-mail address doesn't already exist in the DB.
   public function checkIfMailExists($email)
   {
      $rowset = $this->_tableGateway->select(array('email' => $email));
      $row = $rowset->current();
      return $row;
   }
	public function addCookie($cookie, $userId)
	{
		$this->_tableGateway->update(array('cookie' => $cookie), array('id' => $userId));
	}
   // add a new user
   public function addUser($username, $password, $fname, $lname, $email, $picture, $wantTutorial)
   {
      $this->_tableGateway->insert(array(
         'username'				=> $username,
         'hashedPassword'		=> $password,
         'firstName'			=> isset($fname) ? $fname : "-",
         'lastName'				=> isset($lname) ? $lname : "-",
         'email'				=> $email,
         'filePhoto'			=> isset($picture) ? $picture : "-",
         'wantTutorial'    		=> $wantTutorial,
		 'wantNotifications'    => $wantNotifications
      ));
      return $this->_tableGateway->lastInsertValue;
   }
   public function getAllUsers()
   {
      return $this->_tableGateway->select();
   }

	public function getUserByCookie($cookie)
	{		
		$rowset = $this->_tableGateway->select(array(			
			'cookie'    => $cookie
		));
		$row = $rowset->current();
		return $row;
	}
	public function getUserById($id)
	{		
		$rowset = $this->_tableGateway->select(array(			
			'id'    => $id
		));
		$row = $rowset->current();
		return $row;
	}  

	// Get and return SYSTEM user, used for some automatic task's events.
   public function getSystemUser()
   {
      $rowset = $this->_tableGateway->select(array(
         'username'  => "SYSTEM"
      ));
      $row = $rowset->current();
      return $row;
   }
}