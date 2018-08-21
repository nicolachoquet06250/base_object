<?php

namespace project\classes;
use project\extended\classes\BaseObject;

class test_afficher_body extends BaseObject {
	/**
	 * @return string
	 */
	public function display() {
		return '<body></body>'."\n";
	}
}