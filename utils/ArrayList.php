<?php

namespace project\utils;

use Exception;
use project\extended\classes\BaseObject;
use project\extended\classes\util;

class ArrayList extends util {
    protected $classe;
    protected $list = [];

	/**
	 * ArrayList constructor.
	 *
	 * @param string $classe
	 */
    public function __construct($classe) {
    	parent::__construct();
    	$this->classe = $this->is_array($classe) ? $classe[0] : $classe;
    }

	/**
	 * @param BaseObject[] ...$objs
	 * @throws Exception
	 */
	public function append(...$objs) {
		foreach ($objs as $obj) {
			if ($obj instanceof $this->classe) {
				$obj->set_key(count($this->get()));
				$obj->set_arraylist($this);
				$this->list[] = $obj;
			}
		}
    }

	/**
	 * @param BaseObject[] ...$keys
	 * @throws Exception
	 */
    public function delete(...$keys) {
		foreach ($keys as $key) {
			if(isset($this->list[$key])) {
				unset($this->list[$key]);
			}
			else $this->throw_exception('key '.$key.' out of range', __LINE__);
			//throw new Exception('key '.$key.' out of range');
    	}

    }

	/**
	 * @param int $key
	 * @param BaseObject $new_obj
	 */
    public function update($key, $new_obj) {
        if($new_obj instanceof $this->classe) {
            $this->list[$key] = $new_obj;
        }
    }

    /**
     * @param null|int $key
     * @return BaseObject[]
     * @throws Exception
     */
    public function get($key = null) {
        if($key !== null) {
            if(isset($this->list[$key])) {
                return $this->list[$key];
            }
            else $this->throw_exception('key '.$key.' out of range', __LINE__);//throw new Exception('key '.$key.' out of range');
        }
        return $this->list;
    }

	/**
	 * @return BaseObject[]
	 */
    public function __debugInfo() {
        return $this->list;
    }
}