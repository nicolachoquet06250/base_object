<?php

class test extends BaseObject implements service {
	public function hello_world() {
		var_dump('Hello World');
	}

	public function first_service() {
		var_dump('Voici mon premier service');
	}
}