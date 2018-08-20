<?php

/**
 * Class BaseObject
 *
 * @method test get_service_test()
 * @method test set_service_test()
 */
class BaseObject {
	protected $gettable_and_settable_classes, $services;

	public function __construct() {
		$g_a_s_classes = (array) json::get_from_file('conf/gettable_and_settable_classes', true)->json();
		$this->services = (array) json::get_from_file('conf/services', true)->json();
		$this->gettable_and_settable_classes((array) $g_a_s_classes);
	}
	public function __call($name, $arguments) {
		$method_parts = explode('_', $name);
		if(
			($method_parts[0] === 'get' || $method_parts[0] === 'set')
			&& isset($this->gettable_and_settable_classes[$method_parts[1]])
		) {
			$method = $method_parts[0].'_'.$method_parts[1];
			unset($method_parts[0]);
			unset($method_parts[1]);
			return $this->$method(implode('_', $method_parts), $arguments);
		}
		return null;
	}
	public static function __callStatic($name, $arguments) {
		$o = new BaseObject();
		return $o->$name($arguments);
	}

	protected function get_service(string $name, $arguments) {
		if(isset($this->services[$name])) {
			require_once $this->services[$name]->path;
			$class = $this->services[$name]->class;
			return new $class($arguments);
		}
		return null;
	}
	protected function set_service(string $name, $arguments) {
		return $this->get_service($name, $arguments);
	}
	protected function gettable_and_settable_classes(array $gettable_and_settable_classes = null) {
		if($gettable_and_settable_classes !== null) {
			$this->gettable_and_settable_classes = $gettable_and_settable_classes;
			return $this;
		}
		return $this->gettable_and_settable_classes;
	}
}