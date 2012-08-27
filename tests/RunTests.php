<?php

require_once __DIR__ . '/Test/TestCase.php';

/**
 * Test wrapper for PhpUnit
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class RunTests extends PHPUnit_Framework_TestCase
{
	/** @var string[] */
	public $paths;



	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->paths = array(__DIR__);
	}



	/**
	 * Find all Test Cases
	 * Copied from TestRunner::run()
	 *
	 * @return array
	 */
	public function getTestPaths()
	{
		$tests = array();
		foreach ($this->paths as $path) {
			if (is_file($path)) {
				$files = array($path);
			} else {
				$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
			}
			foreach ($files as $entry) {
				$entry = (string) $entry;
				$info = pathinfo($entry);
				if (!isset($info['extension']) || $info['extension'] !== 'phpt') {
					continue;
				}
				$tests[ltrim(str_replace($path, '', $entry), '/')] = array($entry);
			}
		}

		return $tests;
	}



	/**
	 * Run one Test Case
	 *
	 * @dataProvider getTestPaths
	 */
	public function testFile($path)
	{
		$tc = new TestCase($path);
		$tc->setPhp('php', null, null);
		$tc->run();

		try {
			$tc->collect();
		} catch(TestCaseException $e) {
			if ($e->getCode() == TestCase::CODE_SKIP) {
				$this->markTestSkipped($tc->getOutput());
				return;
			}

			$baseFile = dirname($path) . '/output/' . basename($path, '.phpt');
			if (file_exists($actualFile = "$baseFile.actual") && file_exists($expectedFile = "$baseFile.expected")) {
				$this->assertEquals(file_get_contents($expectedFile), file_get_contents($actualFile));
				throw $e; // should never happen

			} else {
				throw $e; // rethrow
			}
		}
	}

}
