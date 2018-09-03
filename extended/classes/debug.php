<?php

namespace project\extended\classes;


use project\extended\traits\http;
use project\extended\traits\php_environement;

class debug {
	use http;
	use php_environement;

	private static $logs = [];
	private static $instence = null;
	private static $debug_file = '/../../debug.d';
	private static $debug_file_is_ok = false;
	private function __construct() {
		if(!self::$debug_file_is_ok) {
			self::$debug_file = __DIR__.self::$debug_file;
			self::$debug_file_is_ok = true;
		}
	}

	public static function instence() {
		if(!self::$instence) {
			self::$instence = new debug();
		}
		return self::$instence;
	}

	public static function log($message, $key = 'int') {
		if($key === 'int') {
			self::$logs[] = $message;
		}
		else {
			self::$logs[$key] = $message;
		}
		return array_keys(self::$logs)[count(array_keys(self::$logs))-1];
	}

	public static function delete_log($id): bool {
		if(isset(self::$logs[$id])) {
			unset(self::$logs[$id]);
		}
		return false;
	}

	public static function is_active(): bool {
		return file_exists(self::$debug_file) ? true : self::http_get('debug', 'on');
	}

	public static function active($set_state = true) {
		switch ($set_state) {
			case true:
				if(!file_exists(__DIR__.'/../../debug.d')) {
					touch(__DIR__.'/../../debug.d');
				}
				break;
			default:
				if(file_exists(__DIR__.'/../../debug.d')) {
					unlink(__DIR__.'/../../debug.d');
				}
				break;
		}
	}

	public static function logs() {
		return self::$logs;
	}
}