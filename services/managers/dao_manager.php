<?php

namespace project\services\managers;

use Exception;
use project\dao\user_dao;
use project\extended\classes\dao;
use project\extended\traits\manager;
use project\utils\ArrayList;

class dao_manager extends dao {
    use manager;

    private $daos = [];

    /**
     * dao_manager constructor.
     * @throws Exception
     */
    public function __construct() {
    	parent::__construct();
        $daos = opendir('./dao');
        while (($dao = readdir($daos)) !== false) {
            if($dao !== '.' && $dao !== '..') {
                $dao_class = str_replace('.php', '', $dao);
                $this->set_array('daos', $dao_class, $dao_class);
            }
        }
    }

    /**
     * @return dao_manager
     * @throws Exception
     */
    public static function create() {
        return new dao_manager();
    }

	/**
	 * @param array $daos
	 * @return dao[]|dao|null
	 * @throws Exception
	 */
    public function create_dao(...$daos) {
    	$dao_array = [];
		foreach ($daos as $dao) {
			$dao_table = $dao;
			if($dao = '\project\dao\\'.$this->get_array('daos', $dao.'_dao')) {
				require_once 'dao/'.$dao_table.'_dao.php';
				$dao_array[] = new $dao();
			}
    	}
    	return empty($dao_array) ? null : (count($dao_array) === 1 ? $dao_array[0] : $dao_array);
    }

    /**
     * @param $dao
     * @param $champ
     * @param $value
     * @return dao
     * @throws Exception
     */
    public function get_dao_from($dao, $champ, $value) {
		$dao_table = $dao;
        if($dao = '\project\dao\\'.$this->get_array('daos', $dao.'_dao')) {
            $data_test_method = 'get_test_datas_for_'.$dao_table;
            $data_test = $this->get_util('data_test')->$data_test_method();
            require_once 'dao/'.$dao_table.'_dao.php';
			/**
			 * @var ArrayList $array
			 */
            $array = $this->get_util('ArrayList', $dao);
            foreach ($data_test as $data) {
                /**
                 * @var dao $dao_obj
                 */
                $dao_obj = new $dao();
                foreach ($dao_obj->get_fields() as $field) {
                    $dao_obj->set($field, $data[$field]);
                }
                $array->append($dao_obj);
            }

            /**
             * @var user_dao $user
             */
            foreach ($array->get() as $user) {
                if($user->get($champ) === $value) {
                    return $user;
                }
            }
            $this->throw_exception('User not contains field '.$champ.' equal to '.$value, __LINE__);
        }
        return null;
    }

	/**
	 * @param $dao
	 * @return ArrayList|null
	 * @throws Exception
	 */
	public function get_dao_list($dao) {
		$dao_table = $dao;
		if($dao = '\project\dao\\'.$this->get_array('daos', $dao.'_dao')) {
			$data_test_method = 'get_test_datas_for_'.$dao_table;
			$data_test = $this->get_util('data_test')->$data_test_method();
			require_once 'dao/'.$dao_table.'_dao.php';
			/**
			 * @var ArrayList $array
			 */
			$array = $this->get_util('ArrayList', $dao);
			foreach ($data_test as $data) {
				/**
				 * @var dao $dao_obj
				 */
				$dao_obj = new $dao();
				foreach ($dao_obj->get_fields() as $field) {
					$dao_obj->set($field, $data[$field]);
				}
				$array->append($dao_obj);
			}
			return $array;
		}
		return null;
	}
}