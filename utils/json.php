<?php

class json {
	private $json;
	private $is_string;
	public function __construct($json) {
		$this->json($json);
		$this->is_string(is_string($json));
	}

	public function is_string(bool $is_string = null) {
		if($is_string !== null) {
			$this->is_string = $is_string;
			return $this;
		}
		return $this->is_string;
	}
	public function json($json = null) {
		if($json !== null) {
			$this->json = $json;
			return $this;
		}
		return $this->json;
	}

	public static function get_from_file(string $file, bool $assoc = false): json {
		return new json(json_decode(file_get_contents($file.'.json', $assoc)));
	}

	public static function put_to_file($json, string $file): bool {
		return file_put_contents($file, json_encode($json));
	}

}