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
 * Representation of filtered table grouped by some column.
 * GroupedSelection is based on the great library NotORM http://www.notorm.com written by Jakub Vrana.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @author     Jan Dolecek
 */
class GroupedSelection extends AbstractGroupedSelection
{
	/** @var string grouping column name */
	protected $column;

	/**
	 * Creates filtered and grouped table representation.
	 * @param  Selection  $refTable
	 * @param  string  database table name
	 * @param  string  joining column
	 */
	public function __construct(Selection $refTable, $table, $column)
	{
		parent::__construct($refTable, $table);
		$this->column = $column;
	}



	/*****************  sql selectors  *****************j*d*/


	/** @deprecated */
	public function through($column)
	{
		trigger_error(__METHOD__ . '() is deprecated; use ' . __CLASS__ . '::related("' . $this->name . '", "' . $column . '") instead.', E_USER_DEPRECATED);
		$this->column = $column;
		$this->delimitedColumn = $this->refTable->connection->getSupplementalDriver()->delimite($this->column);
		return $this;
	}



	public function select($columns)
	{
		if (!$this->sqlBuilder->getSelect()) {
			$this->sqlBuilder->addSelect("$this->name.$this->column");
		}

		return parent::select($columns);
	}



	public function order($columns)
	{
		if (!$this->sqlBuilder->getOrder()) {
			// improve index utilization
			$this->sqlBuilder->addOrder("$this->name.$this->column" . (preg_match('~\\bDESC$~i', $columns) ? ' DESC' : ''));
		}

		return parent::order($columns);
	}



	/********************* aggregations ****************d*g**/


	protected function calculateAggregation($function)
	{
		$aggregation = array();

		$selection = $this->createSelectionInstance();
		$selection->getSqlBuilder()->importConditions($this->getSqlBuilder());
		$selection->select($function);
		$selection->select("$this->name.$this->column");
		$selection->group("$this->name.$this->column");

		foreach ($selection as $row) {
			$aggregation[$row[$this->column]] = $row;
		}

		return $aggregation;
	}


	protected function doMapping(&$rows, &$output, $limit)
	{
		$offset = array();
		foreach ($this->rows as $key => $row) {
			$ref = & $output[$row[$this->column]];
			$skip = & $offset[$row[$this->column]];
			if ($limit === NULL || $rows <= 1 || (count($ref) < $limit && $skip >= $this->sqlBuilder->getOffset())) {
				$ref[$key] = $row;
			} else {
				unset($this->rows[$key]);
			}
			$skip++;
			unset($ref, $skip);
		}
	}



	/********************* manipulation ****************d*g**/



	public function insert($data)
	{
		if ($data instanceof \Traversable && !$data instanceof Selection) {
			$data = iterator_to_array($data);
		}

		if (Nette\Utils\Validators::isList($data)) {
			foreach (array_keys($data) as $key) {
				$data[$key][$this->column] = $this->active;
			}
		} else {
			$data[$this->column] = $this->active;
		}

		return parent::insert($data);
	}



	public function update($data)
	{
		$builder = $this->sqlBuilder;

		$this->sqlBuilder = new SqlBuilder($this);
		$this->where($this->column, $this->active);
		$return = parent::update($data);

		$this->sqlBuilder = $builder;
		return $return;
	}



	public function delete()
	{
		$builder = $this->sqlBuilder;

		$this->sqlBuilder = new SqlBuilder($this);
		$this->where($this->column, $this->active);
		$return = parent::delete();

		$this->sqlBuilder = $builder;
		return $return;
	}


	/*****************  misc  *****************j*d*/

	public function related($key, $throughColumn = NULL)
	{
		if (strpos($key, '.') !== FALSE) {
			list($key, $throughColumn) = explode('.', $key);
		} elseif (!is_string($throughColumn)) {
			list($key, $throughColumn) = $this->getConnection()->getDatabaseReflection()->getHasManyReference($this->getName(), $key);
		}
		$table = $key;


		$p = & $this->getRefTable($refPath)->referencing[$refPath . "-DoubleRelated:$table.$throughColumn"];
		if (!$p) {
			// Prepare mapping
			{
				if(!$this->sqlBuilder->getSelect()) {
					$this->sqlBuilder->addSelect("$this->primary, $this->column");
				}

				$this->execute();
				$mapping = array();
				foreach($this->rows as $row) $mapping[$row->{$this->primary}] = $row->{$this->column}; // authorId -> companyId
			}

			$p = $this->createDoubleGroupedSelectionInstance($table, $throughColumn, $mapping);
			$p->where("$table.$throughColumn", array_keys($mapping));
		}

		$c = clone $p; // clone prototype
		$c->setActive($this->active);
		return $c;
	}



	protected function createDoubleGroupedSelectionInstance($table, $throughColumn, $mapping)
	{
		return new DoubleGroupedSelection($this, $table, $throughColumn, $mapping);
	}



	public function ref($key, $throughColumn = NULL)
	{
		if (!$throughColumn) {
			list($key, $throughColumn) = $this->connection->getDatabaseReflection()->getBelongsToReference($this->name, $key);
		}
		$table = $key;



		$p = & $this->getRefTable($refPath)->referencing[$refPath . "1-n-1:$table.$throughColumn"];
		if (!$p) {
			// Prepare mapping
			{
				if(!$this->sqlBuilder->getSelect()) {
					$this->sqlBuilder->addSelect("$this->column, $throughColumn");
				}

				$this->execute();
				$mapping = array();
				foreach($this->rows as $row) $mapping[$row->$throughColumn][] = $row->{$this->column}; // tagId -> bookId[]
			}

			$p = $this->createMNGroupedSelectionInstance($table, $mapping);
			$p->where("$table.{$p->primary}", array_keys($mapping));
		}

		$c = clone $p; // clone prototype
		$c->setActive($this->active);
		return $c;

	}



	protected function createMNGroupedSelectionInstance($table, $mapping)
	{
		return new MNGroupedSelection($this, $table, $mapping);
	}

}
