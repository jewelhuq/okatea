<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Orignal file from Dotclear 2.
 * Copyright (c) 2003-2013 Olivier Meunier & Association Dotclear
 * Licensed under the GPL version 2.0 license.
 */
namespace Okatea\Tao\Database;

/**
 * Cette classe permet de faciliter l'insertion et la modification dans la base de données.
 */
class Cursor
{
	protected $db;

	protected $data = [];

	protected $table;

	/**
	 * Constructor
	 *
	 * Init cursor object on a given table. Note that you can init it with
	 * {@link dbLayer::openCursor() openCursor()} method of your connection object.
	 *
	 * Example:
	 * <code>
	 * <?php
	 * $cur = $con->openCursor('table');
	 * $cur->field1 = 1;
	 * $cur->field2 = 'foo';
	 * $cur->insert(); // Insert field ...
	 *
	 * $cur->update('WHERE field3 = 4'); // ... or update field
	 * ?>
	 * </code>
	 *
	 * @see dbLayer::openCursor()
	 * @param
	 *        	dbLayer	&$con		Connection object
	 * @param string $table
	 */
	public function __construct($db, $table)
	{
		$this->db = $db;
		$this->setTable($table);
	}

	/**
	 * Set table
	 *
	 * Changes working table and resets data
	 *
	 * @param string $table
	 */
	public function setTable($table)
	{
		$this->table = $table;
		$this->data = [];
	}

	/**
	 * Set field
	 *
	 * Set value <var>$v</var> to a field named <var>$n</var>. Value could be
	 * an string, an integer, a float, a null value or an array.
	 *
	 * If value is an array, its first value will be interpreted as a SQL
	 * command. String values will be automatically escaped.
	 *
	 * @see __set()
	 * @param string $n
	 * @param mixed $v
	 */
	public function setField($n, $v)
	{
		$this->data[$n] = $v;
	}

	/**
	 * Unset field
	 *
	 * Remove a field from data set.
	 *
	 * @param string $n
	 */
	public function unsetField($n)
	{
		unset($this->data[$n]);
	}

	/**
	 * Field exists
	 *
	 * @return boolean if field named <var>$n</var> exists
	 */
	public function isField($n)
	{
		return isset($this->data[$n]);
	}

	/**
	 * Field value
	 *
	 * @see __get()
	 * @return mixed for a field named <var>$n</var>
	 */
	public function getField($n)
	{
		if (isset($this->data[$n]))
		{
			return $this->data[$n];
		}

		return null;
	}

	/**
	 * Set Field
	 *
	 * Magic alias for {@link setField()}
	 */
	public function __set($n, $v)
	{
		$this->setField($n, $v);
	}

	/**
	 * Field value
	 *
	 * Magic alias for {@link getField()}
	 *
	 * @return mixed for a field named <var>$n</var>
	 */
	public function __get($n)
	{
		return $this->getField($n);
	}

	/**
	 * Field isset
	 *
	 * @return boolean
	 */
	public function __isset($n)
	{
		return $this->isField($n);
	}

	/**
	 * Field unset
	 *
	 * @return void
	 */
	public function __unset($n)
	{
		if ($this->isField($n))
		{
			$this->unsetField($n);
		}
	}

	/**
	 * Empty data set
	 *
	 * Removes all data from data set
	 */
	public function clean()
	{
		$this->data = [];
	}

	private function formatFields()
	{
		$data = [];

		foreach ($this->data as $k => $v)
		{
			$k = $this->db->escapeSystem($k);

			if ($v === null)
			{
				$data[$k] = 'NULL';
			}
			elseif (is_string($v))
			{
				$data[$k] = "'" . $this->db->escapeStr($v) . "'";
			}
			elseif (is_array($v))
			{
				$data[$k] = is_string($v[0]) ? "'" . $this->db->escapeStr($v[0]) . "'" : $v[0];
			}
			else
			{
				$data[$k] = $v;
			}
		}

		return $data;
	}

	/**
	 * Get insert query
	 *
	 * Returns the generated INSERT query
	 *
	 * @return string
	 */
	public function getInsert()
	{
		$data = $this->formatFields();

		$insReq = 'INSERT INTO ' . $this->db->escapeSystem($this->table) . " (\n" . implode(",\n", array_keys($data)) . "\n) VALUES (\n" . implode(",\n", array_values($data)) . "\n) ";

		return $insReq;
	}

	public function getInsertUpdate()
	{
		$data = $this->formatFields();
		$fields = [];

		$insReq = 'INSERT INTO ' . $this->db->escapeSystem($this->table) . " (\n" . implode(",\n", array_keys($data)) . "\n) VALUES (\n" . implode(",\n", array_values($data)) . "\n) \n" . 'ON DUPLICATE KEY UPDATE ';

		foreach ($data as $k => $v)
		{
			$fields[] = $k . ' = ' . $v . "";
		}

		$insReq .= implode(",\n", $fields);

		return $insReq;
	}

	/**
	 * Get update query
	 *
	 * Returns the generated UPDATE query
	 *
	 * @param string $where
	 * @return string
	 */
	public function getUpdate($where)
	{
		$data = $this->formatFields();
		$fields = [];

		$updReq = 'UPDATE ' . $this->db->escapeSystem($this->table) . " SET \n";

		foreach ($data as $k => $v)
		{
			$fields[] = $k . ' = ' . $v . "";
		}

		$updReq .= implode(",\n", $fields);
		$updReq .= "\n" . $where;

		return $updReq;
	}

	/**
	 * Execute insert query
	 *
	 * Executes the generated INSERT query
	 */
	public function insert()
	{
		if (!$this->table)
		{
			throw new \RuntimeException('No table name.');
		}

		$insReq = $this->getInsert();

		if (!$this->db->execute($insReq))
		{
			throw new \RuntimeException('Unable to execute ' . $insReq);
			return false;
		}

		return true;
	}

	public function insertUpdate()
	{
		if (!$this->table)
		{
			throw new \RuntimeException('No table name.');
		}

		$insReq = $this->getInsertUpdate();

		if (!$this->db->execute($insReq))
		{
			throw new \RuntimeException('Unable to execute ' . $insReq);
			return false;
		}

		return true;
	}

	/**
	 * Execute update query
	 *
	 * Executes the generated UPDATE query
	 *
	 * @param string $where
	 */
	public function update($where)
	{
		if (!$this->table)
		{
			throw new \RuntimeException('No table name.');
		}

		$updReq = $this->getUpdate($where);

		if (!$this->db->execute($updReq))
		{
			throw new \RuntimeException('Unable to execute ' . $updReq);
			return false;
		}

		return true;
	}
}
