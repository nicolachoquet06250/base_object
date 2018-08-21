<?php

class dao extends BaseObject {
    use manager;

	/**
	 * @return array
	 */
    public function get_fields() {
    	$fields = array_keys(get_class_vars(get_class($this)));
    	$base_object_fields = array_keys(get_class_vars('BaseObject'));
		foreach ($fields as $key => $field) {
			if(in_array($field, $base_object_fields)) {
				unset($fields[$key]);
			}
    	}
        return $fields;
    }

	/**
	 * @param string $champ
	 * @param string|int|BaseObject $valeur
	 * @return $this
	 * @throws Exception
	 */
	public function set_field(string $champ, $valeur) {
		if(!in_array($champ, array_keys(get_class_vars('BaseObject')))) {
			return $this->set($champ, $valeur);
		}
		throw new Exception('Field '.$champ.' not found');
	}

    /**
     * @param string $champ
     * @return string|int|BaseObject
     * @throws Exception
     */
    public function get_field(string $champ) {
        return $this->get($champ);
    }

	/**
	 * @param array ...$fields
	 * @return dao
	 * @throws Exception
	 */
	public function create_new(...$fields) {
    	$class = __CLASS__;
		/**
		 * @var dao $dao
		 */
    	$dao = new $class();
		foreach ($dao->get_fields() as $i => $field) {
			$dao->set_field($field, $fields[$i]);
    	}
    	return $dao;
	}
}