<?php

use project\classes\test_afficher_body;
use project\dao\user_dao;
use project\services\managers\dao_manager;
use project\utils\Project;

require_once 'autoload.php';

Project::main(function ($_this) {
	/**
	 * @var Project $_this
	 * @var user_dao $user
	 */

	$prenom = dao_manager::create()->get_dao_from('user', 'prenom', 'Yann')->get_field('prenom');
	$src = dao_manager::create()->get_dao_from('slider', 'name', 'Slider 1')->get_field('src');

	$user = dao_manager::create()->get_dao_list('user')->get(0);
	$nom = $user->get_field('nom');

	$new_user = dao_manager::create()
						   ->create_dao('user')
						   ->create_new(
						   	0, 'Choquet', 'Nicolas',
							'1102 ch de l\'espagnol', 'Les primevères',
							'06250', 'Mougins', '1995-07-21',
							date('Y-m-d'));
	$test_afficher_body = $_this->get_object('test_afficher_body');

	echo "--------------------------- dao ---------------------------\n";
	var_dump($prenom);
	var_dump($src);
	var_dump($nom);
	var_dump($new_user->get_field('date_naissence'));

	echo "--------------------------- util --------------------------\n";
	var_dump('test avec la méthode => '
			 .$_this->get_method(test_afficher_body::class, 'display'));
	var_dump('test sans la méthode => '
			 .$test_afficher_body->display());
	var_dump('test statique avec la méthode => '
			 .$_this->get_static_method(test_afficher_body::class, 'toto', 'test', 'avec', 'des', 'tapettes'));
	var_dump('test statique sans la méthode => '
			 .test_afficher_body::toto('test', 'avec', 'des', 'tapettes'));
	var_dump($_this->is_object($test_afficher_body));

});