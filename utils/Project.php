<?php

namespace project\utils;

use Exception;
use project\extended\classes\debug;
use project\extended\classes\util;
use project\extended\classes\view;

ini_set('display_errors', 'on');

class Project extends util {
	private static $callback = null;

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
		$project = new Project();
		try {
			return $callback($project, $project->get_service('meta'), $args);
		}
		catch (Exception $e) {
			if($catch) {
				$catch($e, $project, $project->get_service('meta'), $args);
			}
			else {
				echo $e->getMessage()."\n";
			}
		}
		return null;
	}

	/**
	 * @param $name
	 * @param $arguments
	 * @return mixed|string
	 * @throws Exception
	 */
	public static function __callStatic($name, $arguments) {
		debug::instence();
		debug::log(['ceci est le premier log de debug', 'ceci est le second log de debug'], 'log1');
		debug::log('ceci est le second log de debug', 'log2');
		debug::log('ceci est le troisième log de debug', 'log3');
		debug::log('ceci est le quatrieme log de debug', 'log4');

		$callback = $arguments[0];
		$catch = null;
		$args = [];
		$template_name = null;

		/**
		 * @var DocGenerator $doc_generator
		 */
		$doc_generator = (new Project())->get_util('DocGenerator', ROOT_PATH);
		$doc_generator->active_scss_doc()
					  ->active_php_doc()
					  ->get_scss_parser(ROOT_PATH.'scss', ROOT_PATH.'scss2')
					  ->genere_scss_doc()
					  ->compile_scss();

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
				return str_replace('[debug]', (debug::is_active() ? self::get_debug() : ''), $result->display())."\n";
			} elseif (self::is_string($result) || self::is_integer($result)) {
				return str_replace('[debug]', (debug::is_active() ? self::get_debug() : ''), $result);
			} elseif (self::is_array($result)) {
				self::var_dump($result);
				return (debug::is_active() ? self::get_debug() : '');
			}
		}
		header("HTTP/1.0 500 Internal Error");
		$view = view::get(['page_name' => 500], ['template_name' => 500]);
		$view->set_template_name('errors/500');
		$view->set_template_404('errors/404');
		return str_replace('[debug]', (debug::is_active() ? self::get_debug() : ''), $view->display(true));
	}

	public static function get_debug(callable $callback = null) {
		if($callback && !self::$callback) {
			self::$callback = $callback;
			return $callback(debug::logs());
		}
		elseif (self::$callback) {
			return self::$callback(debug::logs());
		}
		return self::default_callback_for_write_debug(debug::logs());
	}

	private static function default_callback_for_write_debug($logs) {
		$retour = '<table class="table table-bordered" style="width: 100%;">';
		$retour .= '
<tr>
	<th colspan="2" style="text-align: center;">
		Debug
	</th>
</tr>
';
		$max = 1;
		foreach ($logs as $id => $log) {
			if(self::is_array($log)) {
				if (count($log) > $max) {
					$max = count($log);
				}
			}
			if(self::is_array($log)) {
				$retour .= '
<tr>
	<th rowspan="[max_rowspan]">
		'.$id.'
	</th>
</tr>
';
				foreach ($log as $item) {
					$retour .= '
<tr>
	<td>
		'.$item.'
	</td>
</tr>
';
				}
			}
			else {
				$retour .= '
<tr>
	<th>
		'.$id.'
	</th>
	<td>
		'.$log.'
	</td>
</tr>
';
			}
		}
		$retour .= '</table>';
		$retour = str_replace('[max_rowspan]', ($max+1), $retour);
		return $retour;
	}

	/**
	 * @param $root_dir
	 * @return null|ScssParser
	 */
	public function get_scss_parser($root_dir) {
		/**
		 * @var ScssParser $scss_parser
		 */
		$scss_parser = $this->get_util('ScssParser', $root_dir);
		return $scss_parser;
	}
}