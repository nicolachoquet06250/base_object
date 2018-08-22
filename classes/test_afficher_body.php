<?php

namespace project\classes;
use project\extended\classes\util;

class test_afficher_body extends util {
	/**
	 * @return string
	 */
	public function display() {
		return 'méthode normal';
	}

	public static function toto($args, ...$_) {
		if(count($_) > 0) {
			if(!self::is_array($args)) {
				$args = [$args];
			}
			$args = array_merge($args, $_);
		}
		return 'méthode statique avec les arguments suivants : '.implode(' ', $args);
	}
}