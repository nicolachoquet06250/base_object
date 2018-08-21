<?php

namespace project\utils;

class json {
	private $json;
	private $is_string;

	/**
	 * json constructor.
	 *
	 * @param $json
	 */
	public function __construct($json) {
		$this->json($json);
		$this->is_string(is_string($json));
	}

	/**
	 * @param bool|null $is_string
	 * @return $this
	 */
	public function is_string(bool $is_string = null) {
		if($is_string !== null) {
			$this->is_string = $is_string;
			return $this;
		}
		return $this->is_string;
	}

	/**
	 * @param null $json
	 * @return $this
	 */
	public function json($json = null) {
		if($json !== null) {
			$this->json = $json;
			return $this;
		}
		return $this->json;
	}

	/**
	 * @param string $file
	 * @param bool $assoc
	 * @return json
	 */
	public static function get_from_file(string $file, bool $assoc = false): json {
		return new json(json_decode(file_get_contents($file.'.json', $assoc)));
	}

	/**
	 * @param $json
	 * @param string $file
	 * @return bool
	 */
	public static function put_to_file($json, string $file): bool {
		return file_put_contents($file, json_encode($json));
	}

}