<?php
			
	namespace project;
			
	use project\extended\classes\view;
	use project\utils\Project;

	require_once 'autoload.php';
					
	echo Project::CssDoc(function ($_this, $args) {
		$page_name = $args['page_name'];
		$template_name = $args['template_name'];
		
		/** @var Project $_this */
		return view::get(
			['page_name' => $page_name],
			['template_name' => $template_name],
			['last_update' => $_this->get_scss_parser(__DIR__)->get_last_update_file()]
		);
	}, ['__DIR__', __DIR__]);