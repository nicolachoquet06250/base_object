<?php

namespace project;

use project\classes\test_afficher_body;
use project\dao\user_dao;
use project\extended\classes\sql_connector;
use project\services\managers\dao_manager;
use project\services\meta;
use project\services\meta_;
use project\sql\json;
use project\utils\Project;
use project\extended\classes\view;

require_once 'autoload.php';

function Accueil() {
	return Project::Accueil(function ($_this, $metas, $args) {

		$template_name = $args['template_name'];

		/**
		 * @var Project $_this
		 * @var user_dao $user
		 * @var meta $metas
		 */
		$metas->set_title('Voici mon titre');
		$prenom = dao_manager::create()->get_dao_from('user', 'prenom', 'Yann')->get_field('prenom');
		$src    = dao_manager::create()->get_dao_from('slider', 'name', 'Slider 1')->get_field('src');

		$user = dao_manager::create()->get_dao_list('user')->get(0);
		$nom  = $user->get_field('nom');

		$new_user           = dao_manager::create()
										 ->create_dao('user')
										 ->create_new(
											 0, 'Choquet', 'Nicolas',
											 '1102 ch de l\'espagnol', 'Les primevères',
											 '06250', 'Mougins', '1995-07-21',
											 date('Y-m-d'));
		$test_afficher_body = $_this->get_object('test_afficher_body');

		$sql_connector = $_this->sql_connector('json', 'account');

		$sql_connector->create_table('user',
									 [
										 'id', 'nom', 'prenom',
										 'email', 'motdepasse', 'date_inscription'
									 ],
									 [
										 sql_connector::INT, sql_connector::STRING, sql_connector::STRING,
										 sql_connector::STRING, sql_connector::STRING, sql_connector::DATE
									 ],
									 [
										 'date_inscription' => sql_connector::NOW
									 ],
									 [
										 'id' => [
											 'primary',
											 'auto_increment'
										 ]
									 ]
		);

		$sql_connector->create_table('message',
									 [
										 'id', 'user', 'message', 'date_message'
									 ],
									 [
										 sql_connector::INT, sql_connector::OBJECT, sql_connector::STRING, sql_connector::DATE
									 ],
									 [
										 'date_message' => sql_connector::NOW,
									 ],
									 [
										 'id' => [
											 'primary',
											 'auto_increment'
										 ]
									 ]
		);

		$users = [
			[
				'nom'        => 'Choquet',
				'prenom'     => 'Nicolas',
				'email'      => 'nicolachoquet06250@gmail.com',
				'motdepasse' => '0000'
			],
			[
				'nom'        => 'Choquet',
				'prenom'     => 'Yann',
				'email'      => 'choquet.yann@gmail.com',
				'motdepasse' => '0000'
			],
			[
				'nom'        => 'Choquet',
				'prenom'     => 'André',
				'email'      => 'andchoq36@gmail.com',
				'motdepasse' => '0000'
			],
			[
				'nom'        => 'Loubet',
				'prenom'     => 'André',
				'email'      => 'loubet.andre@laposte.net',
				'motdepasse' => '0000'
			]
		];
		foreach ($users as $user) {
			//		$_this->var_dump($sql_connector->add('user',
			//							['nom' => $user['nom']], ['prenom' => $user['prenom']],
			//							['email' => $user['email']], ['motdepasse' => $user['motdepasse']])->go());
		}

		$users = $sql_connector->get(user_dao::class, 'id', 'nom', 'prenom', ['date_inscription' => 'di'])
							   ->where(
								   ['id', 10, json::INF_OR_EQUALS],
								   ['nom', 'Choquet', json::EQUALS]
							   )->order('prenom')
							   ->asc()->go();

		//	$sql_connector->update(user_dao::class, ['nom' => 'Loubet'])->where(['prenom' => 'André'])->go();
		//	$sql_connector->delete(user_dao::class)->where(['id', 3, json::EQUALS])->go();

		// 	test avec DirectoryIterator

		$view = view::get();
		$view->assign('page_name', $metas->get_title());
		$view->assign('template_name', $template_name);
		$view->assign('prenom', $prenom);
		$view->assign('src', $src);
		$view->assign('nom', $nom);
		$view->assign('date_naissence', $new_user->get_field('date_naissence'));
		$view->assign('test1', 'test avec la méthode => '.$_this->get_method(test_afficher_body::class, 'display'));
		$view->assign('test2', 'test sans la méthode => '.$test_afficher_body->display());
		$view->assign('test3', 'test statique avec la méthode => '
							   .$_this->get_static_method(test_afficher_body::class, 'toto', 'test', 'avec', 'des', 'tapettes'));
		$view->assign('test4', 'test statique sans la méthode => '.test_afficher_body::toto('test', 'avec', 'des', 'tapettes'));
		$view->assign('test5', $_this->is_object($test_afficher_body));
		$view->assign('users', $users);
		$view->assign('path_array', $_this->get_scss_parser(__DIR__)->parse()->get_scss_array());

		return $view;
	}, ['__DIR__', __DIR__]);
}
echo Accueil();