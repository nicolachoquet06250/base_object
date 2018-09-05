<?php

namespace project\services;


use project\extended\traits\singleton;

class meta_service {
	use singleton;
	private $metas = [];

	public function construct($params) {}

	private function run_method($method, $params) {
		if($result = $this->$method($params)) {
			return $result;
		}
		return '';
	}

	public function set_meta($name, $value) {
		if(in_array('set_'.$name, get_class_methods(self::class))) {
			$method = 'set_'.$name;
			$this->run_method($method, $value);
			return $this;
		}
		return $this;
	}
	public function set_charset($charset) {
		$this->metas['charset'] = $charset;
		return $this;
	}
	public function set_title($title) {
		$this->metas['title'] = $title;
		return $this;
	}
	public function set_description($description) {
		$this->metas['description'] = $description;
		return $this;
	}
	public function set_author($author) {
		$this->metas['author'] = $author;
		return $this;
	}
	public function set_css_link($link) {
		$this->metas['css_link'][basename($link)] = $link;
		return $this;
	}
	public function set_ico_link($link) {
		$this->metas['ico_link'] = $link;
	}

	public function render_meta($name, $base = null) {
		if(in_array('render_'.$name, get_class_methods(self::class))) {
			$method = 'render_'.$name;
			return $this->run_method($method, $base);
		}
		return '';
	}
	public function render_charset() {
		return '<meta charset="'.$this->get_charset().'" />';
	}
	public function render_title() {
		return '<title>'.$this->get_title().'</title>';
	}
	public function render_description() {
		return '<meta name="description" content="'.$this->get_description().'" />';
	}
	public function render_author() {
		return '<meta name="author" content="'.$this->get_author().'" />';
	}
	public function render_css_link($base) {
		return '<link rel="stylesheet" href="'.$this->get_css_link($base).'" />';
	}
	public function render_ico_link() {
		return '<link rel="icon" href="'.$this->get_ico_link().'" />';
	}

	public function get_meta($name, $base = null) {
		if(in_array('get_'.$name, get_class_methods(self::class))) {
			$method = 'get_'.$name;
			return $this->run_method($method, $base);
		}
		return null;
	}
	public function get_charset() {
		return $this->metas['charset'];
	}
	public function get_title() {
		return $this->metas['title'];
	}
	public function get_description() {
		return $this->metas['description'];
	}
	public function get_author() {
		return $this->metas['author'];
	}
	public function get_css_link($base) {
		return $this->metas['css_link'][$base];
	}
	public function get_ico_link() {
		return $this->metas['ico_link'];
	}
}