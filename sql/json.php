<?php

namespace project\sql;

use project\extended\classes\BaseObject;

class json extends BaseObject {
	private $table;
	public function __construct($table) {
		parent::__construct();
		$this->table = $table;
	}

	public function create_table() {
		$json_utils = $this->get_util('json', [])->create_file($this->table);
		var_dump($this->table);
	}
}