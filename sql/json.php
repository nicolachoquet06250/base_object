<?php

namespace project\sql;

use project\extended\classes\sql_connector;

class json extends sql_connector {
	private static $SELECT = 'select', $UPDATE = 'update', $DELETE = 'delete', $INSERT = 'insert';
	private $database, $path, $request, $result = null;

	/**
	 * @inheritdoc
	 */
	protected function after__construct() {
		$this->database = $this->connection->database;
		$this->path = $this->connection->path;
		$this->connector = $this->path.'/'.$this->database;
		$this->request = [];
	}

	/**
	 * @inheritdoc
	 */
	protected function create_database() : sql_connector {
		if(!is_dir($this->connector)) {
			mkdir($this->connector, 0777, true);
		}
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function create_table($table, array $fields, array $types, array $default, array $keys, $if_not_exists = true) : sql_connector {
		$this->create_database();
		$header = $fields;
		foreach ($fields as $i => $field) {
			$header[$i] = [
				'field' => $field,
				'type' => $types[$i],
			];
			if(isset($keys[$field])) {
				$header[$i]['key'] = $keys[$field][0];
				if(in_array('auto_increment', $keys[$field])) {
					$header[$i]['increment'] = 'auto_increment';
				}
			}
			if(isset($default[$field])) {
				if($header[$i]['type'] === gettype($default[$field]) || ($header[$i]['type'] === self::OBJECT && gettype($default[$field]) === self::INTEGER)) {
					$header[$i]['default'] = $default[$field];
				}
			}
		}
		$table_content = [
			'header' => $header,
			'body'	 => new \stdClass(),
		];
		/**
		 * @var \project\utils\json $json_util
		 */
		$json_util = $this->get_util('json', $table_content);
		$json_file = $json_util::get_from_file($this->connector.'/'.$table, true);
		$json_util->create_file($this->connector.'/'.$table);
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function get($table, ...$fields): sql_connector {
		$this->request['key'] = self::$SELECT;
		$this->request['table'] = $table;
		$this->request['fields'] = $fields;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function where(...$where): sql_connector {
		if(!empty($where)) {
			$this->request['where'] = $where;
		}
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function order(...$by): sql_connector {
		$this->request['order'] = $by;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function group(...$by): sql_connector {
		$this->request['group'] = $by;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function desc(): sql_connector {
		$this->request['sens'] = 'desc';
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function asc(): sql_connector {
		$this->request['sens'] = 'asc';
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function go($format = 'json') {
		switch ($this->request['key']) {
			case self::$SELECT:
				/**
				 * @var \project\utils\json $json_util
				 */
				$json_util = $this->get_util('json');
				$json = $json_util::get_from_file($this->connector.'/'.$this->request['table'])->json();

				if(empty((array) $json->body)) return [];

				$result = [];
				if(empty($this->request['fields'])) {
					foreach ($json->body as $line) {
						$result[] = (array) $line;
					}
				}
				else {
					foreach ($json->body as $line) {
						$new_line = [];
						foreach ($this->request['fields'] as $h_field) {
							$alias = null;
							if($this->is_array($h_field)) {
								$h_field_arr = $h_field;
								$h_field = array_keys($h_field)[0];
								$alias = $h_field_arr[$h_field];
							}

							$new_line[($alias ? $alias : $h_field)] = $line->$h_field;
						}
						$result[] = (array) $new_line;
					}
				}

				$result_tmp = [];
				if($this->request['where']) {
					foreach ($result as $value) {
						$OK = [];
						foreach ($this->request['where'] as $i => $where) {
							if($where !== self::AND && $where !== self::OR) {
								$part1 = $where[0];
								$part2 = $where[1];
								$op = $where[2];

								$OK[] = ($op === self::EQUALS && $value[$part1] === $part2)
										|| ($op === self::DIF && $value[$part1] !== $part2)
										|| ($op === self::SUP && $value[$part1] > $part2)
										|| ($op === self::SUP_OR_EQUALS && $value[$part1] >= $part2)
										|| ($op === self::INF && $value[$part1] < $part2)
										|| ($op === self::INF_OR_EQUALS && $value[$part1] <= $part2);
							}
						}
						$valid_OK = true;
						foreach ($OK as $item) {
							if(!$item) {
								$valid_OK = false;
								break;
							}
						}

						if($valid_OK) {
							$result_tmp[] = $value;
						}
					}
					$result = $result_tmp;
				}

				$this->result = $result;
				break;
			case self::$INSERT:
			case self::$UPDATE:
			case self::$DELETE:
			default:
				break;
		}
		return $this->result;
	}
}