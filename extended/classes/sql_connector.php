<?php

namespace project\extended\classes;

abstract class sql_connector extends util {
	const STRING = 'string', INTEGER = 'integer', INT = 'integer', OBJECT = 'object', DATE = 'string';
	const NOW = 'NOW()';
	public static function NOW() { return date('Y-m-d'); }
	const AND = 'AND', OR = 'OR';
	const EQUALS = '=', DIF = '!=', SUP = '>', SUP_OR_EQUALS = '>=', INF = '<', INF_OR_EQUALS = '<=';
	const ASC = 'asc', DESC = 'desc';

	protected $connector = null, $connection = [];

	public function __construct($connection) {
		parent::__construct();
		$this->connection = $connection;
		$this->after__construct();
	}

	/**
	 * @return void
	 */
	abstract protected 	function after__construct();

	/**
	 * @return sql_connector
	 */
	abstract protected 	function create_database() : sql_connector;

	/**
	 * @param $table
	 * @param array $fields
	 * @param array $types
	 * @param array $default
	 * @param array $keys
	 * @return sql_connector
	 */
	abstract public 	function create_table($table, array $fields, array $types, array $default, array $keys) : sql_connector;

	/**
	 * @param $table
	 * @param array ...$fields
	 * @return sql_connector
	 */
	abstract public 	function get($table, ...$fields) : sql_connector;

	/**
	 * @param $in
	 * @param mixed ...$fields
	 * @return sql_connector
	 */
	abstract public 	function add($in, ...$fields) : sql_connector;

	/**
	 * @param mixed ...$where
	 * @return sql_connector
	 */
	abstract public 	function where(...$where) : sql_connector;

	/**
	 * @param array ...$by
	 * @return sql_connector
	 */
	abstract public 	function order(...$by) : sql_connector;

	/**
	 * @param array ...$by
	 * @return sql_connector
	 */
	abstract public 	function group(...$by) : sql_connector;

	/**
	 * @return sql_connector
	 */
	abstract public 	function desc() : sql_connector;

	/**
	 * @return sql_connector
	 */
	abstract public 	function asc() : sql_connector;

	/**
	 * @param string $format
	 * @return mixed
	 */
	abstract public 	function go($format = 'json');
}