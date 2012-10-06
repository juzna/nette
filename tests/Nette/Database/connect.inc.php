<?php

/**
 * Test: Nette\Database test boostap.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 */

require __DIR__ . '/../bootstrap.php';


$config = parse_ini_file(__DIR__ . '/databases.ini', TRUE);
$current = isset($_SERVER['argv'][1]) ? $config[$_SERVER['argv'][1]] : reset($config);


try {
	$rc = new ReflectionClass('Nette\Database\Connection');
	/** @var Nette\Database\Connection */
	$connection = $rc->newInstanceArgs($current);

} catch (PDOException $e) {
	TestHelpers::skip("Connection to '$current[dsn]' failed. Reason: " . $e->getMessage());
}

TestHelpers::lock($current['dsn'], dirname(TEMP_DIR));

unset($config, $current, $rc);
$driverName = $connection->getAttribute(PDO::ATTR_DRIVER_NAME);



/**
 * Logs executed queries
 */
class QueryLogger
{
	public static $queries;



	public static function log(\Nette\Database\Statement $result, $params)
	{
		self::$queries[] = array(
			$result->queryString,
			$params
		);
	}


	public static function clear()
	{
		self::$queries = array();
	}

}

$connection->onQuery[] = callback('QueryLogger::log');
