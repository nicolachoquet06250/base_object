<?php

namespace project\sql;

use project\extended\classes\sql_connector;

class json extends sql_connector {
	private $database, $path, $file_path;
	public function __construct($connection) {
		parent::__construct();
		$this->database = $connection->database;
		$this->path = $connection->path;
		$this->file_path = $this->path.'/'.$this->database;
	}

	protected function create_database() {
		if(!is_dir($this->file_path)) {
			mkdir($this->file_path, 0777, true);
		}
		return $this;
	}

	function create_table($table, array $fields, array $types, array $default, array $keys) {
		$this->create_database();
		var_dump($fields, $types, $default, $keys);
	}
}