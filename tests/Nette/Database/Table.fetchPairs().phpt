<?php

/**
 * Test: Nette\Database\Table: Fetch pairs.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @multiple   databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");



$apps = $connection->table('book')->order('title')->fetchPairs('id', 'title');  // SELECT * FROM `book` ORDER BY `title`
Assert::same(array(
	1 => '1001 tipu a triku pro PHP',
	4 => 'Dibi',
	2 => 'JUSH',
	3 => 'Nette',
), $apps);




$ids = $connection->table('book')->order('id')->fetchPairs('id', 'id');  // SELECT * FROM `book` ORDER BY `id`
Assert::same(array(
	1 => 1,
	2 => 2,
	3 => 3,
	4 => 4,
), $ids);



$connection->table('author')->get(11)->update(array('born' => new DateTime('2002-02-20')));
$connection->table('author')->get(12)->update(array('born' => new DateTime('2002-02-02')));
$connection->table('author')->get(13)->update(array('born' => new DateTime('2002-02-01')));
$list = $connection->table('author')->order('born')->fetchPairs('born', 'name');
Assert::same(array(
	'2002-02-01 00:00:00' => 'Jan Dolecek',
	'2002-02-02 00:00:00' => 'David Grudl',
	'2002-02-20 00:00:00' => 'Jakub Vrana',
), $list);
