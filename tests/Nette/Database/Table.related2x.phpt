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



// full list
$log = array();
foreach ($connection->table('company') as $company) {
	$log[] = "Company $company->name";

	foreach ($company->related('author')->related('book') as $book) {
		$log[] = " $book->id: $book->title";
	}
}
Assert::equal(array(
	'Company Facebook',
	' 1: 1001 tipu a triku pro PHP',
	' 2: JUSH',
	'Company Nette Foundation',
	' 3: Nette',
	' 4: Dibi',
	'Company JuznaSoft',
), $log);



// with conditionals
$log = array();
foreach ($connection->table('company') as $company) {
	$log[] = "Company $company->name";

	foreach ($company->related('author')->where('name LIKE "J%"')->related('book') as $book) {
		$log[] = " $book->id: $book->title";
	}
}
Assert::equal(array(
	'Company Facebook',
	' 1: 1001 tipu a triku pro PHP',
	' 2: JUSH',
	'Company Nette Foundation',
	'Company JuznaSoft',
), $log);



// 3x
QueryLogger::clear();
$log = array();
foreach ($connection->table('company') as $company) {
	$log[] = "Company $company->name";
	foreach ($company->related('author')->related('book')->related('book_tag') as $tag) {
		$log[] = $tag->tag->name;
	}
}
Assert::equal(array(
	'Company Facebook',
	'PHP',
	'MySQL',
	'JavaScript',
	'Company Nette Foundation',
	'PHP', // Note this tag is here twice because we iterate over each book_tag
	'PHP',
	'MySQL',
	'Company JuznaSoft',
), $log);
Assert::same(5, count(QueryLogger::$queries)); // company + author + book + book_tag + tag



// 3x + ref
QueryLogger::clear();
$log = array();
foreach ($connection->table('company') as $company) {
	$log[] = "Company $company->name";
	foreach ($company->related('author')->related('book')->related('book_tag')->ref('tag') as $tag) {
		$log[] = $tag->name;
	}
}
Assert::equal(array(
	'Company Facebook',
	'PHP',
	'MySQL',
	'JavaScript',
	'Company Nette Foundation',
	'PHP',
	'MySQL',
	'Company JuznaSoft',
), $log);
Assert::same(5, count(QueryLogger::$queries)); // company + author + book + book_tag + tag
