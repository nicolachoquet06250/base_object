<?php

namespace project\sql;

use project\extended\classes\sql_connector;

class mysql extends sql_connector {
	private $database, $path, $file_path;

	/**
	 * @inheritdoc
	 */
	public function after__construct() {
		$this->database = $this->connection->database;
		$this->path = $this->connection->path;
		$this->file_path = $this->path.$this->database;
	}

	/**
	 * @inheritdoc
	 */
	protected function create_database() : sql_connector {
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function create_table($table, array $fields, array $types, array $default, array $keys) : sql_connector {
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function get($table, ...$fields): sql_connector {

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function where(...$where): sql_connector {
		if(!empty($where)) {

		}
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function go($format = 'json') {
		// TODO: Implement go() method.
	}
}