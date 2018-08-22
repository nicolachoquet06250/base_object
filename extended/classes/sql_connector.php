<?php

namespace project\extended\classes;


abstract class sql_connector extends util {
	abstract protected 	function create_database();
	abstract public 	function create_table($table, array $fields, array $types, array $default, array $keys);
}