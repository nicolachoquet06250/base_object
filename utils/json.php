<?php

namespace project\utils;

use project\extended\classes\util;

class json extends util {
	private $json;
	private $is_string;

	/**
	 * json constructor.
	 *
	 * @param $json
	 */
	public function __construct($json = null) {
		$json = $json ? $json : '';
		$json = gettype($json) === 'array' ? $json[0] : $json;
		$this->json($json);
		$this->json_is_string(is_string($json));
	}

	/**
	 * @param bool|null $is_string
	 * @return $this
	 */
	public function json_is_string(bool $is_string = null) {
		if($is_string !== null) {
			$this->is_string = $is_string;
			return $this;
		}
		return $this->is_string;
	}

	/**
	 * @param null $json
	 * @return $this|object|array
	 */
	public function json($json = null) {
		if($json !== null) {
			$this->json = $json;
			return $this;
		}
		return $this->json;
	}

	/**
	 * @param $file_name
	 */
	public function create_file($file_name) {
		if(!$this->json_is_string()) {
			$this->json(json_encode($this->json()));
		}
		file_put_contents($file_name.'.json', $this->json());
	}

	/**
	 * @param string $file
	 * @param bool $assoc
	 * @return json
	 */
	public static function get_from_file(string $file, bool $assoc = false): json {
		return new json(json_decode(file_get_contents($file.'.json', $assoc)));
	}

}