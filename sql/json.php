<?php

namespace project\sql;

use project\dao\user_dao;
use project\extended\classes\sql_connector;
use project\services\managers\dao_manager;

class json extends sql_connector {
	private const SELECT = 'select', UPDATE = 'update', DELETE = 'delete', INSERT = 'insert';
	private $database, $path, $request, $result = null, $last_id = null;

	private function save_request_infos($type, $table, $fields) {
		$this->request['table'] = $table;
		$this->request['key'] = $type;
		$this->request['fields'] = $fields;
		if(count($fields) === 1 && count($fields[0]) > 1 && isset($fields[0][0])) {
			$this->request['fields'] = $fields[0];
		}
	}

	/**
	 * @throws \Exception
	 */
	private function get_primary_key() {
		/**
		 * @var \project\utils\json $json_util
		 */
		$json_util = $this->get_util('json');
		$json_obj = $json_util::get_from_file($this->connector.'/'.$this->request['table']);

		$header = $json_obj->json()->header;

		foreach ($header as $value) {
			if(isset($value->key) && $value->key === 'primary') {
				return $value->field;
			}
		}
		return null;
	}

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
		$this->save_request_infos(self::SELECT, $table, $fields);
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public 	function add($in, ...$fields) : sql_connector {
		$this->save_request_infos(self::INSERT, $in, $fields);
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function update($in, ...$fields): sql_connector {
		$this->save_request_infos(self::UPDATE, $in, $fields);
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function where(...$where): sql_connector {
		if(!empty($where)) {
			if(isset($where[0]) && !empty($where[0])) {
				$this->request['where'] = $where;
			}
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
					foreach ($this->request['order'] as $order) {
						if(isset($this->request['sens'])) {
							usort($result, [
								dao_manager::create()->create_dao($this->request['table']),
								'order_by_'.$order.'__'.$this->request['sens']
							]);
						}
						else {
							usort($result, [
								dao_manager::create()->create_dao($this->request['table']),
								'order_by_'.$order
							]);
						}
					}
				}

				if(isset($this->request['group'])) {
					foreach ($this->request['order'] as $order) {
						if(isset($this->request['sens'])) {
							usort($result, [
								dao_manager::create()->create_dao($this->request['table']),
								'group_by_'.$order.'__'.$this->request['sens']
							]);
						}
						else {
							usort($result, [
								dao_manager::create()->create_dao($this->request['table']),
								'group_by_'.$order
							]);
						}
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
							$field->value = $local_field[$field->field];
							$field_exists = $field;
							break;
						}
					}
					if($field_exists === false) {
						$field_name = $field->field;
						$default = null;
						if(isset($field->default)) {
							$field_value = $field->default;
							if($field_value === self::NOW) {
								$field_value = self::NOW();
							}
						}
						else {
							$field_value = $field->value;
						}
						$last_field = null;
						if(isset($field->increment) && $field->increment === 'auto_increment') {
							$complete_table = $this->get($this->request['table'])->go();
							$last_field = count($complete_table) > 1 ? $complete_table[count($complete_table)-1][$field->field] : 0;
							$last_field++;
							$field_value = $last_field;
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
					$complete_table = $this->get($this->request['table'])->go();
					$primary_key = $this->get_primary_key();
					$this->last_id = $complete_table[count($complete_table)-1][$primary_key];
					$json_util_w->create_file($this->connector.'/'.$this->request['table']);
					$complete_table = $this->get($this->request['table'])->go();
					$last_id = $complete_table[count($complete_table)-1][$primary_key];
					if($this->last_id === $last_id-1) {
						$this->last_id = $last_id;
						return true;
					}
					return false;
				}
				else throw new \Exception('Le champ \''.$failed_field['name'].'\' n\'est pas au bon format : il est au format \''.$failed_field['type']['expected'].'\' alors qu\'il devrait être au format \''.$failed_field['type']['actual'].'\'');
				break;
			case self::UPDATE:
				$request = $this->request;
				if(isset($request['where'])) {
					$json_obj = $json_util::get_from_file($this->connector.'/'.$request['table']);
					$complete_table = $json_obj->json();
					$complete_table_body = $complete_table->body;
					foreach ($complete_table_body as $i => $line) {
						foreach ($request['where'] as $where) {
							if(in_array(array_keys($where)[0], array_keys((array) $line))) {
								$field = array_keys($where)[0];
								if($line->$field === $where[$field]) {
									foreach ($request['fields'] as $local_field) {
										$local_field_name = array_keys($local_field)[0];
										$local_field_value = $local_field[$local_field_name];
										$complete_table_body[$i]->$local_field_name = $local_field_value;
									}
								}
							}
						}
					}
					$complete_table->body = $complete_table_body;
					/**
					 * @var \project\utils\json $json_util_w
					 */
					$json_util_w = $this->get_util('json', $complete_table);
					$json_util_w->create_file($this->connector.'/'.$this->request['table']);
				}
				break;
			case self::DELETE:
			default:
				break;
		}
		return $this->result;
	}
}