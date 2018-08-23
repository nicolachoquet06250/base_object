<?php

namespace project\sql;

use project\extended\classes\sql_connector;

class json extends sql_connector {
	private const SELECT = 'select', UPDATE = 'update', DELETE = 'delete', INSERT = 'insert';
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
		if(!$if_not_exists && !is_file($this->connector.'/'.$table.'.json')) {
			$json_util->create_file($this->connector.'/'.$table);
		}
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function get($table, ...$fields): sql_connector {
		$this->request['key'] = self::SELECT;
		$this->request['table'] = $table;
		$this->request['fields'] = $fields;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public 	function add($in, ...$fields) : sql_connector {
		$this->request['table'] = $in;
		$this->request['key'] = self::INSERT;
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
		$this->request['sens'] = self::DESC;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function asc(): sql_connector {
		$this->request['sens'] = self::ASC;
		return $this;
	}

	/**
	 * @inheritdoc
	 * @throws \Exception
	 */
	public function go($format = 'json') {
		/**
		 * @var \project\utils\json $json_util
		 */
		$json_util = $this->get_util('json');

		switch ($this->request['key']) {
			case self::SELECT:
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

				if(isset($this->request['where'])) {
					$result_tmp = [];
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

				if(isset($this->request['order'])) {
					// TODO code pour ordonner les résultats
				}

				if(isset($this->request['group'])) {
					// TODO code pour grouper les résultats
				}

				if(isset($this->request['sens'])) {
					switch ($this->request['sens']) {
						case self::ASC:
							// TODO code pour ordonner les résultats dans le sens croissant
						case self::DESC:
							// TODO code pour ordonner les résultats dans le sens décroissant
						default:
							break;
					}
				}

				$this->result = $result;
				break;
			case self::INSERT:
				$json_obj = $json_util::get_from_file($this->connector.'/'.$this->request['table']);
				$json = $json_obj->json();
				$line = [];
				$line_valid = true;
				$failed_field = [];

				foreach ($json->header as $field) {
					$field_exists = false;
					foreach ($this->request['fields'] as $local_field) {
						if($field->field === array_keys($local_field)[0]) {
							$field->value = $local_field[array_keys($local_field)[0]];
							$field_exists = $field;
							break;
						}
					}
					if(!$field_exists) {
						$field_name = $field->field;
						$default = null;
						if(isset($field->default)) {
							$default = $field->default;
						}
						$field_value = $default;
						if($default === self::NOW) {
							$field_value = self::NOW();
						}
					}
					else {
						$field_name = $field_exists->field;
						$field_value = $field_exists->value;
					}
					$require_type = $field->type;
					if($this->{'is_'.$require_type}($field_value)) {
						if($field_value === self::NOW) {
							$field_value = self::NOW();
						}
						$line[$field_name] = $field_value;
					}
					else {
						$failed_field = [
							'name' => $field_name,
							'type' => [
								'expected' => $require_type,
								'actual' => gettype($field_value),
							],
						];
						$line_valid = false;
						break;
					}
				}

				if($line_valid) {
					$json->body[] = $line;
					/**
					 * @var \project\utils\json $json_util_w
					 */
					$json_util_w = $this->get_util('json', $json);
					$json_util_w->create_file($this->connector.'/'.$this->request['table']);
				}
				else throw new \Exception('Le champ \''.$failed_field['name'].'\' n\'est pas au bon format : il est au format \''.$failed_field['type']['expected'].'\' alors qu\'il devrait être au format \''.$failed_field['type']['actual'].'\'');
				break;
			case self::UPDATE:
			case self::DELETE:
			default:
				break;
		}
		return $this->result;
	}
}