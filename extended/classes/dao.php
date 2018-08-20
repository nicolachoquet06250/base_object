<?php

class dao extends BaseObject implements service
{
    use manager;

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
	 * @param $champ
	 * @param $valeur
	 * @return $this
	 * @throws Exception
	 */
	public function set_field($champ, $valeur) {
		if(!in_array($champ, array_keys(get_class_vars('BaseObject')))) {
			return $this->set($champ, $valeur);
		}
		throw new Exception('Field '.$champ.' not found');
	}

    /**
     * @param $champ
     * @return mixed
     * @throws Exception
     */
    public function get_field($champ) {
        return $this->get($champ);
    }
}