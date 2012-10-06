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
 * Grouped M:N selection
 * E.g. when selecting `tags` from a `book` via `book_tag`; grouped by primary key
 *
 * @author     Jan Dolecek
 */
class MNGroupedSelection extends AbstractGroupedSelection
{
	/** @var array targetId -> sourceId[]; e.g. tag_id => book_id[] */
	protected $mapping;



	/**
	 * Creates filtered and grouped table representation.
	 * @param  Selection  $refTable
	 * @param  string  database table name
	 */
	public function __construct(Selection $refTable, $table, array $mapping)
	{
		parent::__construct($refTable, $table);
		$this->mapping = $mapping;
	}



	/********************* internal ****************d*g**/



	protected function doMapping(&$rows, &$output)
	{
		$limit = $this->sqlBuilder->getLimit();

		$offset = array();
		foreach ($rows as $key => $row) {
			$iid = $row[$this->primary]; // item id
			foreach ($this->mapping[$iid] as $targetId) { // e.g. target is book_id
				$ref = & $output[$targetId];
				$skip = & $offset[$targetId];
				if ($limit === NULL || $rows <= 1 || (count($ref) < $limit && $skip >= $this->sqlBuilder->getOffset())) {
					$ref[$key] = $row;
				} else {
					unset($rows[$key]);
				}
				$skip++;
				unset($ref, $skip);
			}
		}
	}



	protected function calculateAggregation($function) {
		throw new \Nette\NotImplementedException;
	}

}
