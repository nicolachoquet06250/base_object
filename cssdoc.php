<?php
			
	namespace project;
			
	use project\extended\classes\view;
	use project\utils\Project;

	require_once 'autoload.php';
					
	echo Project::CssDoc(function ($_this, $args) {
		$page_name = $args['page_name'];
		$template_name = $args['template_name'];
		
		return view::get(
			['page_name' => $page_name],
			['template_name' => $template_name]
		);
	}, ['__DIR__', __DIR__]);