<?php

require_once 'autoload.php';

Project::main(function () {

	var_dump(dao_manager::create()->get_dao_from('user', 'prenom', 'Yann')->get_field('prenom'));
	var_dump(dao_manager::create()->get_dao_from('slider', 'name', 'Slider 1')->get_field('src'));
	var_dump(dao_manager::create()->get_dao_list('user')->get(0)->get_field('nom'));

});