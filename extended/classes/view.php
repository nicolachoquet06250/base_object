<?php

namespace project\extended\classes;


class view extends util {
	private $complete_path = '';
	private $view_path = './layouts';
	private $template_name = '';
	private $template_file_name = 'index.view.html';
	private $template_404 = './layouts/errors/404/index.view.html';
	private $vars = [];

	public function __construct($vars) {
		parent::__construct();
		$this->set_vars_array($vars);
	}

	protected function get_path() {
		return $this->view_path;
	}

	protected function get_template_content() {
		if(is_file($this->get_complete_path().'/'.str_replace('.html', '.php', $this->get_template_file_name()))) {
			list($var_keys, $var_values) = $this->get_vars_array();
			foreach ($var_keys as $i => $var_key) {
				$key = str_replace(['[', ']'], '', $var_key);
				$$key = $var_values[$i];
			}
			return include $this->get_complete_path().'/'.str_replace('.html', '.php', $this->get_template_file_name());
		}
		elseif (is_file($this->get_complete_path().'/'.$this->get_template_file_name())) {
			$template_content = file_get_contents($this->get_complete_path().'/'.$this->get_template_file_name());
			list($var_keys, $var_values) = $this->get_vars_array();
			$template_content = str_replace($var_keys, $var_values, $template_content);
			return $template_content;
		}
		else {
			return $this->_404();
		}
	}

	protected function get_vars() {
		return $this->vars;
	}
	protected function set_vars_array($vars) {
		$this->vars = $vars;
	}
	protected function get_vars_array() {
		$vars = $this->vars;
		$var_keys = [];
		$var_values = [];
		foreach ($vars as $var) {
			$var_keys[] = '['.array_keys($var)[0].']';
			$var_values[] = $var[array_keys($var)[0]];
		}
		return [$var_keys, $var_values];
	}

	public function set_template_name($template) {
		$this->template_name = $template;
	}
	public function get_template_name() {
		return $this->template_name;
	}

	protected function set_complete_path($complete_path) {
		$this->complete_path = $complete_path;
	}
	public function get_complete_path() {
		return $this->complete_path;
	}

	public function get_template_file_name(): string {
		return $this->template_file_name;
	}
	protected function set_template_file_name(string $template_file_name) {
		$this->template_file_name = $template_file_name;
	}

	public function get_template_404() {
		return $this->template_404;
	}
	public function set_template_404($template_404) {
		$this->template_404 = $template_404;
	}

	protected function before_display() {
		$this->set_complete_path($this->get_path().'/'.$this->get_template_name());
	}

	protected function _404() {
		header("HTTP/1.0 404 Page not found");
		if($this->template_404 && is_file($this->template_404)) {
			$this->vars[]['page'] = $this->vars[0]['page_name'];
			$this->vars[]['template'] = $this->vars[1]['template_name'];
			$this->vars[0]['page_name'] = '404';
			$this->vars[1]['template_name'] = '404';
			list($var_keys, $var_values) = $this->get_vars_array();
			$template_content = file_get_contents($this->template_404);
			foreach ($var_keys as $i => $var_key) {
				$template_content = str_replace($var_key, $var_values[$i], $template_content);
			}
			return $template_content;
		}
		return '404 - Page not found';
	}

	protected function display_for_sub_view() {
		$this->before_display();
		return $this->get_template_content();
	}

	/**
	 * @param bool $sub
	 * @return bool|integer|view|string
	 */
	public function display($sub = false) {
		$this->before_display();
		if($sub) {
			return $this->get_template_content();
		}
		else {
			if (is_file($this->get_complete_path().'/'.$this->get_template_file_name())) {
				if (is_file($this->get_complete_path().'/'.$this->get_template_name().'.view.php')) {
					require_once $this->get_complete_path().'/'.$this->get_template_name().'.view.php';
					$class = 'project\layouts\\'.$this->get_template_name();
					/**
					 * @var view $view
					 */
					$view = new $class($this->vars);
					$view->set_template_name($this->get_template_name());
					return $view->display();
				}
				else {
					return $this->get_template_content();
				}
			}
			else {
				return $this->get_template_content();
			}
		}
	}

	/**
	 * @param array[] ...$vars
	 * @return view
	 */
	public static function get(...$vars) {
		return new view($vars);
	}
}