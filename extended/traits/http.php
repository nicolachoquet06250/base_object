<?php

namespace project\extended\traits;


trait http {
	public static function http_get($key = null, $value = null, $insert = false) {
		if($insert) {
			$_GET[$key] = $value;
		}
		else {
			if($value) {
				return isset($_GET[$key]) ? $_GET[$key] === $value : false;
			}
			return $key ? $_GET[$key] : null;
		}
		return null;
	}

	public static function http_post($key = null) {
		return $key ? $_POST[$key] : null;
	}

	public static function http_put($key = null) {
		if($key) {
			return strtolower(self::http_server('REQUEST_METHOD')) === 'put' ? self::http_post($key) : null;
		}
		return null;
	}

	public static function http_delete($key = null) {
		if($key) {
			return strtolower(self::http_server('REQUEST_METHOD')) === 'delete' ? self::http_post($key) : null;
		}
		return null;
	}

	public static function http_server($key = null) {
		return $key ? $_SERVER[$key] : null;
	}

	public static function http_session($key = null) {
		return $key ? $_SESSION[$key] : null;
	}

	public static function http_cookie($key = null) {
		return $key ? $_COOKIE[$key] : null;
	}

	public static function http_files($key = null) {
		return $key ? $_FILES[$key] : null;
	}

	public static function http_request($key = null) {
		return $key ? $_REQUEST[$key] : null;
	}

	public static function get_host() {
		return self::http_server('HTTP_HOST');
	}

	public static function get_uri() {
		return self::http_server('REQUEST_URI');
	}

	public static function get_complete_current_url() {
		return self::get_host().'/'.self::get_uri();
	}
}