<?php

namespace project\utils;

use Exception;
use project\extended\classes\util;
use project\extended\classes\view;

class Project extends util {

	/**
	 * @param callable $callback
	 * @param callable|null $catch
	 * @param array $args
	 * @param string $page_name
	 * @param string $template_name
	 * @return view|integer|string|null
	 */
	public static function main(callable $callback, callable $catch = null, $args = [], $page_name = 'main', $template_name = 'main') {
		$args['page_name'] = $page_name;
		$args['template_name'] = $template_name;
		try {
			return $callback(new Project(), $args);
		}
		catch (Exception $e) {
			if($catch) {
				$catch($e, new Project(), $args);
			}
			else {
				echo $e->getMessage()."\n";
			}
		}
		return null;
	}

	public static function __callStatic($name, $arguments) {
		$callback = $arguments[0];
		$catch = null;
		$args = [];
		$template_name = null;

		/**
		 * @var ScssParser $scss_parser
		 */
		$scss_parser = (new Project())->get_scss_parser(__DIR__);
		$scss_parser->parse()->get_scss_array();
		$scss_parser->genere_scss_file()->genere_scss_doc_array()->genere_doc_file();

		if(isset($arguments[1]) && gettype($arguments[1]) === 'array') {
			$args = $arguments[1];
		}
		elseif (isset($arguments[1]) && gettype($arguments[1]) === 'object' && get_class($arguments[1]) === 'Closure') {
			$catch = $arguments[1];
			if(isset($arguments[2]) && self::is_array($arguments[2])) {
				$args = $arguments[2];
			}
			elseif(isset($arguments[2]) && self::is_string($arguments[2])) {
				$template_name = $arguments[2];
			}
			if($template_name === null && isset($arguments[3]) && self::is_string($arguments[3])) {
				$template_name = $arguments[3];
			}
		}
		$page_name = $name;
		if($template_name === null) {
			$template_name = $page_name;
		}
		if(($result = self::main($callback, $catch, $args, $page_name, $template_name)) !== null) {
			if (self::is_object($result, view::class)) {
				/**
				 * @var view $result
				 */
				$result->set_template_name($template_name);
				return $result->display()."\n";
			} elseif (self::is_string($result) || self::is_integer($result)) {
				return $result;
			} elseif (self::is_array($result)) {
				self::var_dump($result);
				return '';
			}
		}
		header("HTTP/1.0 500 Internal Error");
		$view = view::get(['page_name' => 500], ['template_name' => 500]);
		$view->set_template_name('errors/500');
		$view->set_template_404('errors/404');
		return $view->display(true);
	}

	public function get_scss_parser($root_dir) {
		return $this->get_util('ScssParser', $root_dir);
	}
}