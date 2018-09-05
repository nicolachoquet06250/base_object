<?php

namespace project\extended\traits;


trait singleton {
	private static $instence;
	abstract protected function construct($params);
	protected function __construct() {
		$this->construct(func_get_args());
	}

	/**
	 * @return $this
	 */
	public static function instence() {
		if(is_null(self::$instence)) {
			$class = __CLASS__;
			return new $class(func_get_args());
		}
		return self::$instence;
	}
}