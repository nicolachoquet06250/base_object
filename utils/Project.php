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
			$callback($args);
		}
		catch (Exception $e) {
			if($catch) {
				$catch($e);
			}
			else {
				echo $e->getMessage()."\n";
			}
		}
	}
}