<?php

use project\dao\user_dao;
use project\services\managers\dao_manager;
use project\utils\Project;

require_once 'autoload.php';

Project::main(function ($args) {
	$prenom = dao_manager::create()->get_dao_from('user', 'prenom', 'Yann')->get_field('prenom');
	$src = dao_manager::create()->get_dao_from('slider', 'name', 'Slider 1')->get_field('src');

	/**
	 * @var user_dao $user
	 */
	$user = dao_manager::create()->get_dao_list('user')->get(0);
	$nom = $user->get_field('nom');

	$new_user = dao_manager::create()
						   ->create_dao('user')
						   ->create_new(
						   	0, 'Choquet', 'Nicolas',
							'1102 ch de l\'espagnol', 'Les primevères',
							'06250', 'Mougins', '1995-07-21',
							date('Y-m-d'));
	var_dump($prenom, $src, $nom, $new_user);

	unset($new_user);
});