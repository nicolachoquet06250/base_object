<?php

class ArrayList {
    protected $classe;
    protected $list = [];

	/**
	 * ArrayList constructor.
	 *
	 * @param string $classe
	 */
    public function __construct($classe) {
        $this->classe = $classe[0];
    }

	/**
	 * @param array[BaseObject] ...$objs
	 */
	public function append(...$objs) {
		foreach ($objs as $obj) {
			if ($obj instanceof $this->classe) {
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
			else throw new Exception('key '.$key.' out of range');
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
     * @return array
     * @throws Exception
     */
    public function get($key = null) {
        if($key !== null) {
            if(isset($this->list[$key])) {
                return $this->list[$key];
            }
            else throw new Exception('key '.$key.' out of range');
        }
        return $this->list;
    }

	/**
	 * @return array
	 */
    public function __debugInfo() {
        return $this->list;
    }
}