<?php

/**
 * Class BaseObject
 *
 * @method test get_service_test()
 * @method test set_service_test()
 */
class BaseObject extends stdClass {
	/**
	 * @var ArrayList $array
	 */
	private $array = null;
	private $key = null;
	protected $gettable_and_settable_classes, $services, $utils;

	/**
	 * BaseObject constructor.
	 */
	public function __construct() {
		$this->utils = (array) json::get_from_file('conf/utils', true)->json();
		$g_a_s_classes = (array) json::get_from_file('conf/gettable_and_settable_classes', true)->json();
		$this->services = (array) json::get_from_file('conf/services', true)->json();
		$this->gettable_and_settable_classes((array) $g_a_s_classes);
	}

	/**
	 * @param $name
	 * @param $arguments
	 * @return null
	 */
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

	/**
	 * @param $name
	 * @param $arguments
	 * @return mixed
	 */
	public static function __callStatic($name, $arguments) {
		$o = new BaseObject();
		return $o->$name($arguments);
	}

	/**
	 * @param string $name
	 * @param mixed ...$arguments
	 * @return null
	 */
	protected function get_service(string $name, ...$arguments) {
		if(isset($this->services[$name])) {
			require_once $this->services[$name]->path;
			$class = $this->services[$name]->class;
			return new $class($arguments);
		}
		return null;
	}

	/**
	 * @param string $name
	 * @param mixed ...$arguments
	 * @return null
	 */
	protected function set_service(string $name, ...$arguments) {
		return $this->get_service($name, $arguments);
	}

	/**
	 * @param string $name
	 * @param mixed ...$arguments
	 * @return null
	 */
	protected function get_util(string $name, ...$arguments) {
		if(isset($this->utils[$name])) {
			require_once $this->utils[$name]->path;
			$class = $this->utils[$name]->class;
			return new $class($arguments);
		}
		return null;
	}

	/**
	 * @param string $name
	 * @param mixed ...$arguments
	 */
	protected function set_util(string $name, ...$arguments) {
		$this->get_util($name, $arguments);
	}

	/**
	 * @param array|null $gettable_and_settable_classes
	 * @return $this
	 */
	protected function gettable_and_settable_classes(array $gettable_and_settable_classes = null) {
		if($gettable_and_settable_classes !== null) {
			$this->gettable_and_settable_classes = $gettable_and_settable_classes;
			return $this;
		}
		return $this->gettable_and_settable_classes;
	}

	/**
	 * @param ArrayList $arrayList
	 * @return $this
	 */
	public function set_arraylist(ArrayList $arrayList) {
		$this->array = $arrayList;
		return $this;
	}
	/**
	 * @return ArrayList
	 */
	public function get_arraylist() {
		return $this->array;
	}

	/**
	 * @param int $key
	 * @return $this
	 */
	public function set_key(int $key) {
		$this->key = $key;
		return $this;
	}
	/**
	 * @return int
	 */
	public function get_key() {
		return $this->key;
	}

	public function __destruct() {}
}