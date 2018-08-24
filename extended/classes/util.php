<?php

namespace project\extended\classes;


class util extends BaseObject {

	public function var_dump(...$var) {
		echo '<pre>';
		var_dump($var);
		echo '</pre>';
	}

	private function parcour_dir($class_to_find, $directory = './classes') {
		$dir = opendir($directory);
		$class = false;
		while (($file = readdir($dir)) !== false) {
			if($file !== '.' && $file !== '..') {
				if (is_dir($directory.'/'.$file)) {
					return $this->parcour_dir($class_to_find, $directory.'/'.$file);
				} else {
					if($file === $class_to_find.'.php') {
						$class = [
							'path'  => $directory.'/'.$file,
							'name' => 'project\\'.str_replace(['/', '.\\'], ['\\', ''], $directory).'\\'.str_replace('.php', '', $file)
						];
						break;
					}
				}
			}
		}
		return $class;
	}

	/**
	 * @param $class
	 * @param mixed ...$params
	 * @return object
	 * @throws \Exception
	 */
	public function get_object($class, ...$params) {
		$origin_class = $class;
		if($class = $this->parcour_dir($class)) {
			require_once $class['path'];
			$class_name = $class['name'];
			return new $class_name($params);
		}
		throw new \Exception('class '.$origin_class.' not found');
	}

	/**
	 * @param $class
	 * @param $method
	 * @param mixed ...$params
	 * @return mixed
	 * @throws \Exception
	 */
	public function get_method($class, $method, ...$params) {
		$origin_class = explode('\\', $class)[count(explode('\\', $class))-1];
		if($class = $this->parcour_dir($origin_class)) {
			require_once $class['path'];
			$class_name = $class['name'];
			if(in_array($method, get_class_methods($class_name))) {
				return (new $class_name())->$method($params);
			}
			throw new \Exception('method '.$origin_class.'::'.$method.'() not found');
		}
		throw new \Exception('class '.$origin_class.' not found');
	}

	/**
	 * @param $class
	 * @param $method
	 * @param mixed ...$params
	 * @return mixed
	 * @throws \Exception
	 */
	public function get_static_method($class, $method, ...$params) {
		$origin_class = explode('\\', $class)[count(explode('\\', $class))-1];
		if($class = $this->parcour_dir($origin_class)) {
			require_once $class['path'];
			$class_name = $class['name'];
			if(in_array($method, get_class_methods($class_name))) {
				return $class_name::$method($params);
			}
			throw new \Exception('method '.$origin_class.'::'.$method.'() not found');
		}
		throw new \Exception('class '.$origin_class.' not found');
	}

	/**
	 * @param $mode
	 * @param $alias
	 * @return bool|array
	 */
	private function sql($mode, $alias) {
		return isset($this->sql[$mode]->$alias) ? $this->sql[$mode]->$alias : false;
	}

	/**
	 * @param $mode
	 * @param $alias
	 * @return bool|sql_connector
	 */
	public function sql_connector($mode, $alias) {
		if($infos = $this->sql($mode, $alias)) {
			$mode = '\project\\sql\\'.$mode;
			require_once str_replace(['\project\\', '\\'], ['', '/'], $mode).'.php';
			return new $mode($infos);
		}
		return false;
	}

	public function is_array($var) {
		return is_array($var);
	}
	public function is_int($var) {
		return is_int($var);
	}
	public function is_integer($var) {
		return is_integer($var);
	}
	public function is_string($var) {
		return is_string($var);
	}
	public function is_object($var, $tested_class = null) {
		$origin_class = get_class($var);
		if($tested_class) {
			return is_object($var) && ($origin_class === $tested_class || $var instanceof $tested_class);
		}
		else {
			return is_object($var);
		}
	}
}