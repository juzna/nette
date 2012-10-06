<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Database\Table;

use Nette;



/**
 * Double grouped selection, i.e. (1:n)^2
 * E.g. getting all books written by authors of a given company (company -> author -> book)
 *
 * Column ($column, e.g. author_id) will be mapped via $mapping to sourceId (i.e. $companyId)
 *
 * @author     Jan Dolecek
 */
class DoubleGroupedSelection extends GroupedSelection
{
	/** @var array middleId -> sourceId */
	protected $mapping;



	/**
	 * Creates filtered and grouped table representation.
	 * @param  Selection  $refTable
	 * @param  string  database table name
	 * @param  string  joining column
	 * @param  int  primary key of grouped rows
	 */
	public function __construct(Selection $refTable, $table, $column, array $mapping)
	{
		parent::__construct($refTable, $table, $column);
		$this->mapping = $mapping;
	}



	/********************* internal ****************d*g**/



	protected function doMapping(&$rows, &$output, $limit)
	{
		$limit = $this->sqlBuilder->getLimit();

		$offset = array();
		foreach ($rows as $key => $row) {
			$id = $this->mapping[$row[$this->column]];
			$ref = & $output[$id];
			$skip = & $offset[$id];
			if ($limit === NULL || $rows <= 1 || (count($ref) < $limit && $skip >= $this->sqlBuilder->getOffset())) {
				$ref[$key] = $row;
			} else {
				unset($rows[$key]);
			}
			$skip++;
			unset($ref, $skip);
		}
	}



	protected function calculateAggregation($function) {
		throw new \Nette\NotImplementedException;
	}

}
