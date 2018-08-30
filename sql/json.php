<?php

namespace project\sql;

use project\extended\classes\dao;
use project\extended\classes\sql_connector;
use project\services\managers\dao_manager;
use project\utils\ArrayList;

class json extends sql_connector {
	private const 	SELECT = 'select',
					UPDATE = 'update',
					DELETE = 'delete',
					INSERT = 'insert';
	private $database, $path, $request,
			$result = null, $last_id = null;

	private function save_request_infos($type, $table, $fields = []) {
		$this->request['table'] = $table;
		$this->request['dao'] = false;
		$this->request['dao_class'] = false;
		if(strstr($table, 'dao')) {
			$this->request['dao_class'] = $table;
			$this->request['table'] = str_replace(['project\dao\\', '_dao'], '', $table);
			$this->request['dao'] = $this->request['table'];
		}
		$this->request['key'] = $type;
		if(!empty($fields)) {
			$this->request['fields'] = $fields;
		}
	}

	/**
	 * @return string|null
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
	 * @param $field
	 * @return int
	 */
	private function get_last_field($field) {
		/**
		 * @var \project\utils\json $json_util
		 */
		$json_util = $this->get_util('json');
		$json_obj = $json_util::get_from_file($this->connector.'/'.$this->request['table']);
		$json = $json_obj->json();
		$complete_table = $json->body;
		$nb_lignes = count($complete_table);
		if($nb_lignes > 0) {
			return $complete_table[$nb_lignes === 0 ? $nb_lignes : ($nb_lignes - 1)]->$field;
		}
		else {
			return null;
		}
	}

	/**
	 * @return int
	 * @throws \Exception
	 */
	private function get_last_id() {
		$primary_key = $this->get_primary_key();
		$last_field = $this->get_last_field($primary_key);
		return $last_field !== null ? $last_field : 0;
	}

	/**
	 * @param $field
	 * @return bool
	 */
	private function field_exists($field) {
		$field_exists = false;
		foreach ($this->request['fields'] as $local_field) {
			if($field->field === array_keys($local_field)[0]) {
				$field->value = $local_field[$field->field];
				$field_exists = $field;
				break;
			}
		}
		return $field_exists;
	}

	/**
	 * @param $field_exists
	 * @param $field
	 * @return array
	 */
	private function get_finally_field($field_exists, $field) {
		$field_value = null;

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
				$last_field = $this->get_last_field($field_name);
				if($last_field !== null) {
					$last_field++;
				}
				else {
					$last_field = 0;
				}
				$field_value = $last_field;
			}
		}
		else {
			$field_name = $field_exists->field;
			$field_value = $field_exists->value;
		}

		return [$field_name, $field_value];
	}

	/**
	 * @param $require_type
	 * @param $line
	 * @param $field_name
	 * @param $field_value
	 * @param $failed_field
	 * @return bool
	 */
	private function replace_default_value_if_need($require_type, &$line, $field_name, $field_value, &$failed_field) {
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
			return false;
		}
		return true;
	}

	/**
	 * @param $json
	 * @param $line
	 * @return bool
	 * @throws \Exception
	 */
	private function save_new_line($json, $line) {
		$json->body = (array)$json->body;
		$json->body[] = $line;

		$this->last_id = $this->get_last_id();

		foreach ($json->body as $field) {
			if($field->{$this->get_primary_key()} === $this->last_id) {
				$bad_last_id = $line[$this->get_primary_key()];
				$this->throw_exception('La ligne avec l\'identifiant de la valeur \''.$bad_last_id.'\' existe déja', __LINE__);
//				throw new \Exception('La ligne avec l\'identifiant de la valeur \''.$bad_last_id.'\' existe déja');
			}
		}

		/**
		 * @var \project\utils\json $json_util_w
		 */
		$json_util_w = $this->get_util('json', $json);
		$json_util_w->create_file($this->connector.'/'.$this->request['table']);

		$last_id = $this->get_last_id();

		if($this->last_id === $last_id-1) {
			$this->last_id = $last_id;
			return true;
		}
		return false;
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
		if($if_not_exists) {
			if(!file_exists($this->connector.'/'.$table.'.json')) {
				$json_util->create_file($this->connector.'/'.$table);
			}
		}
		else {
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
	public function delete($in): sql_connector {
		$this->save_request_infos(self::DELETE, $in);
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
				$result = ($dao = $this->request['dao_class']) !== false ? new ArrayList($dao) : [];

				if(empty($this->request['fields'])) {
					foreach ($json->body as $line) {
						if(($dao = $this->request['dao']) !== false) {
							$new_line = dao_manager::create()->create_dao($dao);
							foreach ($new_line->get_fields() as $field) {
								$new_line->set_field($field, $line->$field);
							}
							$result->append($new_line);
						}
						else {
							$result[] = (array)$line;
						}
					}
				}
				else {
					foreach ($json->body as $line) {
						$new_line = ($dao = $this->request['dao']) !== false ? dao_manager::create()->create_dao($dao) : [];

						foreach ($this->request['fields'] as $h_field) {
							$alias = null;
							if($this->is_array($h_field)) {
								$h_field_arr = $h_field;
								$h_field = array_keys($h_field)[0];
								$alias = $h_field_arr[$h_field];
							}
							if(($dao = $this->request['dao']) !== false) {
								$new_line->set_field($h_field, $line->$h_field);
							}
							else {
								$new_line[($alias ? $alias : $h_field)] = $line->$h_field;
							}
						}
						if(($dao = $this->request['dao']) !== false) {
							$result->append($new_line);
						}
						else {
							$result[] = (array)$new_line;
						}
					}
				}

				if(isset($this->request['where'])) {
					if(($dao = $this->request['dao_class']) !== false) {
						$result_tmp = new ArrayList($dao);
						/**
						 * @var dao $value
						 */
						foreach ($result->get() as $value) {
							$OK = [];
							foreach ($this->request['where'] as $i => $where) {
								if ($where !== self:: AND && $where !== self:: OR) {
									$part1 = $where[0];
									$part2 = $where[1];
									$op    = $where[2];

									$OK[] = ($op === self::EQUALS && $value->get_field($part1) === $part2)
											|| ($op === self::DIF && $value->get_field($part1) !== $part2)
											|| ($op === self::SUP && $value->get_field($part1) > $part2)
											|| ($op === self::SUP_OR_EQUALS && $value->get_field($part1) >= $part2)
											|| ($op === self::INF && $value->get_field($part1) < $part2)
											|| ($op === self::INF_OR_EQUALS && $value->get_field($part1) <= $part2);
								}
							}
							$valid_OK = true;
							foreach ($OK as $item) {
								if (!$item) {
									$valid_OK = false;
									break;
								}
							}

							if ($valid_OK) {
								$result_tmp->append($value);
							}
						}
					}
					else {
						$result_tmp = [];
						foreach ($result as $value) {
							$OK = [];
							foreach ($this->request['where'] as $i => $where) {
								if ($where !== self:: AND && $where !== self:: OR) {
									$part1 = $where[0];
									$part2 = $where[1];
									$op    = $where[2];

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
								if (!$item) {
									$valid_OK = false;
									break;
								}
							}

							if ($valid_OK) {
								$result_tmp[] = $value;
							}
						}
					}
					$result = $result_tmp;
				}

				if(isset($this->request['order'])) {
					foreach ($this->request['order'] as $order) {
						if(($dao = $this->request['dao']) !== false) {
							$result = $result->get();
							if (isset($this->request['sens'])) {
								usort($result, [
									dao_manager::create()->create_dao($this->request['table']),
									'order_by_'.$order.'__'.$this->request['sens']
								]);
							} else {
								usort($result, [
									dao_manager::create()->create_dao($this->request['table']),
									'order_by_'.$order
								]);
							}
						}
						else {
							if (isset($this->request['sens'])) {
								usort($result, [
									dao_manager::create()->create_dao($this->request['table']),
									'order_by_'.$order.'__'.$this->request['sens']
								]);
							} else {
								usort($result, [
									dao_manager::create()->create_dao($this->request['table']),
									'order_by_'.$order
								]);
							}
						}
					}
				}

				if(isset($this->request['group'])) {
					foreach ($this->request['order'] as $order) {
						if(($dao = $this->request['dao']) !== false) {
							$result = $result->get();
							if (isset($this->request['sens'])) {
								usort($result, [
									dao_manager::create()->create_dao($this->request['table']),
									'group_by_'.$order.'__'.$this->request['sens']
								]);
							} else {
								usort($result, [
									dao_manager::create()->create_dao($this->request['table']),
									'group_by_'.$order
								]);
							}
						}
						else {
							if (isset($this->request['sens'])) {
								usort($result, [
									dao_manager::create()->create_dao($this->request['table']),
									'group_by_'.$order.'__'.$this->request['sens']
								]);
							} else {
								usort($result, [
									dao_manager::create()->create_dao($this->request['table']),
									'group_by_'.$order
								]);
							}
						}
					}
				}

				$this->result = $result;
				return $this->result;
			case self::INSERT:
				$json_obj = $json_util::get_from_file($this->connector.'/'.$this->request['table']);
				$json = $json_obj->json();
				$line = [];
				$line_valid = true;
				$failed_field = [];

				foreach ($json->header as $field) {
					$field_exists = $this->field_exists($field);
					list($field_name, $field_value) = $this->get_finally_field($field_exists, $field);

					$require_type = $field->type;
					if(!$this->replace_default_value_if_need($require_type, $line, $field_name, $field_value, $failed_field)) {
						$line_valid = false;
						break;
					}
				}

				if($line_valid) {
					return $this->save_new_line($json, $line);
				}
				$this->throw_exception('Le champ \''.$failed_field['name'].'\' n\'est pas au bon format : il est au format \''.$failed_field['type']['actual'].'\' alors qu\'il devrait être au format \''.$failed_field['type']['expected'].'\'', __LINE__);
//				else throw new \Exception('Le champ \''.$failed_field['name'].'\' n\'est pas au bon format : il est au format \''.$failed_field['type']['actual'].'\' alors qu\'il devrait être au format \''.$failed_field['type']['expected'].'\'');
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
				return true;
			case self::DELETE:
				$request = $this->request;
				if(isset($request['where'])) {
					$json_obj = $json_util::get_from_file($this->connector.'/'.$request['table']);
					$complete_table = $json_obj->json();
					$complete_table_body = $complete_table->body;
					foreach ($complete_table_body as $i => $line) {
						$OK = [];
						foreach ($request['where'] as $where) {
							$where_part1 = $where[0];
							$where_part2 = $where[1];
							$op = $where[2];
							$OK[] = (($op === self::EQUALS && $line->$where_part1 === $where_part2)
							|| ($op === self::DIF && $line->$where_part1 !== $where_part2)
							|| ($op === self::SUP && $line->$where_part1 > $where_part2)
							|| ($op === self::SUP_OR_EQUALS && $line->$where_part1 >= $where_part2)
							|| ($op === self::INF && $line->$where_part1 < $where_part2)
							|| ($op === self::INF_OR_EQUALS && $line->$where_part1 <= $where_part2));
						}
						$del_OK = true;
						foreach ($OK as $item) {
							if(!$item) {
								$del_OK = false;
								break;
							}
						}
						if($del_OK) {
							unset($complete_table_body[$i]);
						}
					}
					$complete_table->body = $complete_table_body;
					/**
					 * @var \project\utils\json $json_util_w
					 */
					$json_util_w = $this->get_util('json', $complete_table);
					$json_util_w->create_file($this->connector.'/'.$this->request['table']);
				}
				return true;
			default:
				break;
		}
		$this->request = [];
		return $this->result;
	}
}