<?php

namespace project\services;

use project\extended\classes\BaseObject;

class test extends BaseObject {
	public function hello_world() {
		var_dump('Hello World');
	}

	public function first_service() {
		var_dump('Voici mon premier service');
	}
}