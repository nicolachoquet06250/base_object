<?php

namespace project\sql;

use project\extended\classes\sql_connector;

class mysql extends sql_connector {
	private $database, $path, $file_path;
	public function __construct($connection) {
		parent::__construct();
		$this->database = $connection->database;
		$this->path = $connection->path;
		$this->file_path = $this->path.$this->database;
	}

	protected function create_database() {

	}

	public function create_table($table, array $fields, array $types, array $default, array $keys)
	{
		// TODO: Implement create_table() method.
	}
}