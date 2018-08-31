<?php

namespace project;

use project\utils\Project;
use project\extended\classes\view;

require_once 'autoload.php';

echo Project::register(function () {
	return view::get();
});