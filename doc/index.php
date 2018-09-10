<?php
			
	namespace project;
			
	use project\extended\classes\debug;
	use project\extended\classes\view;
	use project\utils\DocGenerator;
	use project\utils\PhpParser;
	use project\utils\Project;

	require_once '../autoload.php';

	function CssDoc() {
		return Project::CssDoc(function ($_this, $metas, $args) {
			$page_name     = $args['page_name'];
			$template_name = $args['template_name'];

			debug::log(['ceci est le premier log de debug', 'ceci est le second log de debug'], 'log18');

			/** @var Project $_this */
			/** @var DocGenerator $doc_generator */
			/** @var PhpParser $php_parser */
			$doc_generator = $_this->get_util('DocGenerator');
			$php_parser    = $doc_generator->get_php_parser();
			$php_parser->genere_php_doc_array();

			return view::get(
				['page_name' => $page_name],
				['template_name' => $template_name],
				['last_update' => $_this->get_scss_parser(ROOT_PATH)->get_last_update_file()]
			);
		});
	}
	echo CssDoc();