<?php

class Project
{
	public static function main(callable $callback, callable $catch = null, $args = []) {
		try {
			$callback($args);
		}
		catch (Exception $e) {
			if($catch) {
				$catch($e);
			}
			else {
				echo $e->getMessage()."\n";
			}
		}
	}
}