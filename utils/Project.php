<?php

namespace project\utils;

use Exception;
use project\extended\classes\util;

class Project extends util {

	/**
	 * @param callable $callback
	 * @param callable|null $catch
	 * @param array $args
	 * @param string $page_name
	 */
	public static function main(callable $callback, callable $catch = null, $args = [], $page_name = 'main') {
		$args['page_name'] = $page_name;
		try {
			$callback(new Project(), $args);
		}
		catch (Exception $e) {
			if($catch) {
				$catch($e, new Project(), $args);
			}
			else {
				echo $e->getMessage()."\n";
			}
		}
	}

	public static function __callStatic($name, $arguments) {
		$callback = $arguments[0];
		$catch = null;
		$args = [];
		if(isset($arguments[1]) && gettype($arguments[1]) === 'array') {
			$args = $arguments[1];
		}
		elseif (isset($arguments[1]) && gettype($arguments[1]) === 'object' && get_class($arguments[1]) === 'Closure') {
			$catch = $arguments[1];
			if(isset($arguments[2])) {
				$args = $arguments[2];
			}
		}
		$page_name = $name;
		self::main($callback, $catch, $args, $page_name);
	}
}