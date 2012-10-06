<?php

/**
 * Test: Nette\Database\Table: M:N relationship
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . '/nette_test1.sql');

// full parameters given
foreach ($connection->table('book')->where('author_id', 11) as /** @var \Nette\Database\Table\ActiveRow $book */ $book) {
	echo "$book->id: $book->title\n";

	foreach ($book->relatedMN('book_tag', 'book_id', 'tag', 'tag_id') as $tag) {
		echo " - $tag->name\n";
	}
}


// just tables (and guess the column names)
foreach ($connection->table('book')->where('author_id', 11) as /** @var \Nette\Database\Table\ActiveRow $book */ $book) {
	echo "$book->id: $book->title\n";

	foreach ($book->relatedMN('book_tag', 'tag') as $tag) {
		echo " - $tag->name\n";
	}
}
