<?php

/**
 * Test: Nette\Database\Table: M:N relationship
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 * @multiple   databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");



// simple
$log = array();
foreach ($connection->table('book')->where('author_id', 11) as /** @var \Nette\Database\Table\ActiveRow $book */ $book) {
	$log[] = "$book->id: $book->title";

	foreach ($book->related('book_tag')->ref('tag') as $tag) {
		$log[] = " - $tag->name";
	}
}
Assert::equal(array(
	'1: 1001 tipu a triku pro PHP',
	' - PHP',
	' - MySQL',
	'2: JUSH',
	' - JavaScript',
), $log);



// with conditionals
$log = array();
foreach ($connection->table('book')->where('author_id', 11) as /** @var \Nette\Database\Table\ActiveRow $book */ $book) {
	$log[] = "$book->id: $book->title";

	foreach ($book->related('book_tag')->where('approved = 1')->ref('tag') as $tag) {
		$log[] =  " - $tag->name";
	}
}
Assert::equal(array(
	'1: 1001 tipu a triku pro PHP',
	' - PHP',
	'2: JUSH',
	' - JavaScript',
), $log);
