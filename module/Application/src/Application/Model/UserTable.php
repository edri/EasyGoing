<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;

// Contains the methods that allows to work with an user entity.
class UserTable
{
	protected $tableGateway;

	public function __construct(TableGateway $tableGateway)
	{
		$this->tableGateway = $tableGateway;
	}

	// Check an user's creditentials, to ensure it logged in correctly.
	// Return 'true' if creditentials are right, and 'false' if wrong.
	public function checkCreditentials($username, $hashedPassword)
	{
		// Try to get the row that matchs with the given username and hashed
		// password.
		$rowset = $this->tableGateway->select(array(
			'username' => $username,
			'hashedPassword' => $hashedPassword
			));
		$row = $rowset->current();

		// Return true or false, depending on the given creditentials'
		// correctness.
		return $row ? true : false;
	}
	// Checks if the given e-mail address doesn't already exist in the DB.


	public function checkIfMailExists($email)
	{
		$rowset = $this->tableGateway->select(array('email' => $email));
		$row = $rowset->current();
		return $row;
	}

		// add a new user
	public function addUser($username, $password, $fname, $lname, $email, $picture)
	{
		$this->tableGateway->insert(array(
			'username'				=> $username,
			'hashedPassword'			=> $password,
			'firstName'			=> isset($fname) ? $fname : "-",
			'lastName'			=> isset($lname) ? $lname : "-",
			'email'				=> $email,
			'filePhoto'			=> isset($picture) ? $picture : "-"
			));

		return $this->tableGateway->lastInsertValue;
	}

	public function getAllUsers()
	{
		return $this->tableGateway->select();
	}

	public function getUsersNotMembersOfProject($projectId)
	{
		/** EN CONSTRUCTION **/

	    $adapter = $this->tableGateway->getAdapter();
		 $sql = new Sql($adapter);
/*
      SELECT * FROM users
      WHERE id NOT IN (
         SELECT id FROM users
          INNER JOIN projectsUsersMembers ON projectsUsersMembers.user = users.id
          WHERE projectsUsersMembers.project = 2
      )*/

       $subSelect = $sql->select();
	    $subSelect->from('users');
	    $subSelect->columns(array('id'));
	    $subSelect->join('projectsUsersMembers', 'users.id = projectsUsersMembers.user');
	    $subSelect->where('projectsUsersMembers.project = ?', $projectId);

	    $select = $sql->select();
	    $select->from('users');
	    $select->where('id NOT IN (?)', $subSelect);

	    $statement = $sql->prepareStatementForSqlObject($select);
	    $results   = $statement->execute();

	    foreach ($results as $person) {
	        echo "<pre>";
	        print_r($person);
	        echo "</pre>";
	    }

	    return $results;
	}
}
