<?php

namespace project\utils;

use Exception;
use project\extended\classes\util;

class Project extends util {

	/**
	 * @param callable $callback
	 * @param callable|null $catch
	 * @param array $args
	 */
	public static function main(callable $callback, callable $catch = null, $args = []) {
		try {
			$callback(new Project(), $args);
		}
		catch (Exception $e) {
			if($catch) {
				$catch($e, new Project());
			}
			else {
				echo $e->getMessage()."\n";
			}
		}
	}
}