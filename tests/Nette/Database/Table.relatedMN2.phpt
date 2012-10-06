<?php

/**
 * Test: Nette\Database\Table: M:N relationship with conditionals
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 * @multiple   databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");



foreach ($connection->table('book')->where('author_id', 11) as /** @var \Nette\Database\Table\ActiveRow $book */ $book) {
	echo "$book->id: $book->title\n";

	foreach ($book->related('book_tag')->where('approved = 1')->ref('tag') as $tag) {
		echo " - $tag->name\n";
	}
}
