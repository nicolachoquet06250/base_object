<?php

namespace project\extended\traits;


trait php_environement {
	public static function globals($key = null) {
		return $key ? $GLOBALS[$key] : $GLOBALS;
	}

	public static function env($key = null) {
		return $key ? $_ENV[$key] : $_ENV;
	}
}