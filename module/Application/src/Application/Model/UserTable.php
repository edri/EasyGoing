<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

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
			'login' => $username,
			'password' => $hashedPassword
		));
		$row = $rowset->current();

		// Return true or false, depending on the given creditentials'
		// correctness.
		if (!$row)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
}
