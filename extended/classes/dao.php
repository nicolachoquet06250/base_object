<?php

namespace project\extended\classes;
use Exception;
use project\extended\traits\manager;

class dao extends util {
    use manager;

	/**
	 * @return array
	 */
    public function get_fields() {
    	$fields = array_keys(get_class_vars(get_class($this)));
    	$base_object_fields = array_keys(get_class_vars('\project\extended\classes\BaseObject'));
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
		if(!in_array($champ, array_keys(get_class_vars('\project\extended\classes\BaseObject')))) {
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
	 * @return $this
	 * @throws Exception
	 */
	public function create_new(...$fields) {
		if(count($fields) === 1 && count($fields[0]) > 1) {
			$fields = $fields[0];
		}
		foreach ($this->get_fields() as $i => $field) {
			$this->set_field($field, $fields[$i]);
    	}
    	return $this;
	}

	/**
	 * Algo d'ordonendement 'order_by'
	 *
	 * @param $field
	 * @param $sens
	 * @param $tab1
	 * @param $tab2
	 * @return bool
	 * @throws Exception
	 */
	protected function order_by($field, $sens, $tab1, $tab2) {
		if($this->is_object($tab1, dao::class)) {
			/**
			 * @var dao $tab1
			 * @var dao $tab2
			 */
			if ($sens === sql_connector::ASC) {
				if ($this->is_integer($tab1->get_field($field))) {
					return $tab1->get_field($field) > $tab2->get_field($field);
				} else {
					return ord(substr($tab1->get_field($field), 0, 1)) > ord(substr($tab2->get_field($field), 0, 1));
				}
			} else {
				if ($this->is_integer($tab1[$field])) {
					return $tab1->get_field($field) < $tab2->get_field($field);
				} else {
					return ord(substr($tab1->get_field($field), 0, 1)) < ord(substr($tab2->get_field($field), 0, 1));
				}
			}
		}
		else {
			if ($sens === sql_connector::ASC) {
				if ($this->is_integer($tab1[$field])) {
					return $tab1[$field] > $tab2[$field];
				} else {
					return ord(substr($tab1[$field], 0, 1)) > ord(substr($tab2[$field], 0, 1));
				}
			} else {
				if ($this->is_integer($tab1[$field])) {
					return $tab1[$field] < $tab2[$field];
				} else {
					return ord(substr($tab1[$field], 0, 1)) < ord(substr($tab2[$field], 0, 1));
				}
			}
		}
	}

	/**
	 * Algo d'ordonendement 'group_by'
	 *
	 * @param $field
	 * @param $sens
	 * @param $tab1
	 * @param $tab2
	 * @return bool
	 * @throws Exception
	 */
	protected function group_by($field, $sens, $tab1, $tab2) {
		if($this->is_object($tab1, dao::class)) {
			/**
			 * @var dao $tab1
			 * @var dao $tab2
			 */
			if ($sens === sql_connector::ASC) {
				if ($this->is_integer($tab1->get_field($field))) {
					return $tab1->get_field($field) > $tab2->get_field($field);
				} else {
					return ord(substr($tab1->get_field($field), 0, 1)) > ord(substr($tab2->get_field($field), 0, 1));
				}
			} else {
				if ($this->is_integer($tab1[$field])) {
					return $tab1->get_field($field) < $tab2->get_field($field);
				} else {
					return ord(substr($tab1->get_field($field), 0, 1)) < ord(substr($tab2->get_field($field), 0, 1));
				}
			}
		}
		else {
			if ($sens === sql_connector::ASC) {
				if ($this->is_integer($tab1[$field])) {
					return $tab1[$field] > $tab2[$field];
				} else {
					return ord(substr($tab1[$field], 0, 1)) > ord(substr($tab2[$field], 0, 1));
				}
			} else {
				if ($this->is_integer($tab1[$field])) {
					return $tab1[$field] < $tab2[$field];
				} else {
					return ord(substr($tab1[$field], 0, 1)) < ord(substr($tab2[$field], 0, 1));
				}
			}
		}
	}

	public function __call($name, $a) {
		if(strstr($name, 'order_by_')) {
			$field = str_replace('order_by_', '', $name);
			$field = explode('__', $field);
			$sens = isset($field[1]) ? $field[1] : sql_connector::ASC;
			$field = $field[0];
			return $this->order_by($field, $sens, $a[0], $a[1]);
		}
		elseif(strstr($name, 'group_by_')) {
			$field = str_replace('group_by_', '', $name);
			$field = explode('__', $field);
			$sens = isset($field[1]) ? $field[1] : sql_connector::ASC;
			$field = $field[0];
			return $this->group_by($field, $sens, $a[0], $a[1]);
		}
		return parent::__call($name, $a);
	}
}