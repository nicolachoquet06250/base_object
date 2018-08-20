<?php

require_once 'autoload.php';

Project::main(function () {

	$user = dao_manager::create()->get_dao_from('user', 'prenom', 'Yann');
	var_dump($user->get_field('prenom'));

	$slider = dao_manager::create()->get_dao_from('slider', 'name', 'Slider 1');
	var_dump($slider->get_field('src'));

	$users = dao_manager::create()->get_dao_list('user');
	var_dump($users->get(0));

});