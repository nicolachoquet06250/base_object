<?php

class ArrayList
{
    protected $classe;
    protected $list = [];
    public function __construct($classe) {
        $this->classe = $classe;
    }

    public function append($obj) {
        if($obj instanceof $this->classe) {
            $this->list[] = $obj;
        }
    }

    /**
     * @param $key
     * @throws Exception
     */
    public function delete($key) {
        if(isset($this->list[$key])) {
            unset($this->list[$key]);
        }
        else throw new Exception('key '.$key.' out of range');
    }

    public function update($key, $new_obj) {
        if($new_obj instanceof $this->classe) {
            $this->list[$key] = $new_obj;
        }
    }

    /**
     * @param null $key
     * @return mixed
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

    public function __debugInfo() {
        return $this->list;
    }
}