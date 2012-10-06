<?php

/**
 * Test: Nette\Database\Table: Related().
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 * @multiple   databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");



foreach ($connection->table('company') as /** @var \Nette\Database\Table\ActiveRow $company */ $company) {
	echo "Company $company->name:\n";

	foreach ($company->related('author')->where('name LIKE "J%"')->related('book') as $book) {
		echo " $book->id: $book->title\n";
	}
}
