<?php

namespace project\utils;

use project\extended\classes\util;
use project\extended\traits\http;

class PhpParser extends util {
	use http;
	private $root_dir = null, $root_dir_core = null, $root_dir_custom = null, $php_suffix = 'php',
		$last_update_file = 'last_update.txt', $html_doc_dir = 'layouts/PhpDoc/', $html_doc_file = 'index.view.html', $php_doc_file = 'index.php';
	private $php_array = [], $docs = [];
	private $php_reg_exp;
	private $enable_last_updated = true;

	public function __construct($root_dir = null) {
		parent::__construct();
		if(is_array($root_dir) && !empty($root_dir)) {
			$this->set_root_dir('*', $root_dir[0]);
		}
		else {
			$this->set_root_dir('*', ROOT_PATH);
		}
		$this->php_doc_file     = $this->get_root_dir().$this->php_doc_file;
		$this->php_reg_exp = '`([a-zA-Z0-9\-\_\.]+)\.'.$this->php_suffix.'`';
	}

	public function set_html_doc($path) {
		if(substr($path, strlen($path)-1, 1) !== '/') {
			$path .= '/';
		}
		$this->html_doc_dir = $path;
	}

	public function set_html_file($path) {
		$this->html_doc_file = $path;
	}

	public function set_php_file($path = null) {
		$this->php_doc_file = $path;
	}

	public function set_root_dir($type, $path) {
		$this->{'root_dir'.($type !== '*' || $type !== 'all' ? '' : '_'.$type)} = $path;
	}

	public function get_root_dir($type = '*') {
		return $this->{'root_dir'.($type !== '*' || $type !== 'all' ? '' : '_'.$type)};
	}

	public function get_last_update_file() {
		if(is_file($this->base_dir.'/'.$this->last_update_file) && $this->enable_last_updated) {
			return file_get_contents($this->base_dir.'/'.$this->last_update_file);
		}
		return '';
	}

	public function get_php_array() {
		return $this->scss_array;
	}

	public function get_doc_array() {
		return $this->docs;
	}

	private function parcour($directory, $step = 0) {
		if(is_dir($directory)) {
			foreach (new \DirectoryIterator($directory) as $fileInfo) {
				if (!$fileInfo->isDot() && $fileInfo->isDir() && substr($fileInfo->getBasename(), 0, 1) !== '.') {
					$new_directory = substr($directory, strlen($directory) - 1, 1) === '/' ? substr($directory, 0, strlen($directory) - 1) : $directory;
					$this->parcour($new_directory.'/'.$fileInfo->getBasename(), $step + 1);
				} elseif ($fileInfo->isFile() && strstr($fileInfo->getFilename(), '.'.$this->php_suffix) && $filepath = $fileInfo->getPathname()) {

					$basename = basename($filepath);
					if (preg_match($this->php_reg_exp, $basename, $matches)) {
						if ($step === 0) {
							$this->php_array[$basename] = $filepath;
						} elseif ($step === 1) {
							$directory_name                               = basename(dirname($filepath));
							$this->php_array[$directory_name][$basename] = $filepath;
						} elseif ($step > 1) {
							$directory_name     = basename(dirname($filepath));
							$sub_directory_name = basename(dirname(str_replace($directory_name.'/'.$basename, '', $directory)));
							$key                = $sub_directory_name.'/'.$directory_name;
							for ($i = 2, $max = $step; $i < $max; $i++) {
								$dir = str_replace($key, '', $directory);
								$key = basename($dir).'/'.$key;
							}

							$this->php_array[$key][$basename] = $filepath;
						}
					}
				}
			}
		}
	}

	/**
	 * @throws \ReflectionException
	 */
	public function parse() {
		if($this->root_dir_core && $this->root_dir_custom) {
			$this->parcour($this->root_dir_core);
			$this->parcour($this->root_dir_custom);
		}
		else {
			$this->parcour($this->root_dir);
		}

		ksort($this->php_array);
		foreach ($this->php_array as $key => $value) {
			if($this->is_array($value)) {
				ksort($this->php_array[$key]);
			}
		}
	}

	/**
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function genere_php_file() {
		$this->parse();
		$css_file_content = '';
		foreach ($this->get_php_array() as $directory => $file_and_directory_array) {
			if($this->is_string($file_and_directory_array)) {
				if(file_get_contents($file_and_directory_array) !== '') {
					$path = $file_and_directory_array;
					if($this->root_dir_core) {
						$css_file_content .= "\n// SOURCE ".str_replace([$this->root_dir_core.'/', $this->root_dir_custom.'/'], '', $path).
											 "\n".file_get_contents($path);
					}
					else {
						$css_file_content .= "\n// SOURCE ".str_replace($this->root_dir.'/', '', $path).
											 "\n".file_get_contents($path);
					}
				}
			}
			else {
				foreach ($file_and_directory_array as $_directory => $path) {
					if(file_get_contents($path) !== '') {
						if($this->root_dir_core) {
							$css_file_content .= "\n// SOURCE ".str_replace([$this->root_dir_core.'/', $this->root_dir_custom.'/'], '', $path).
												 "\n".file_get_contents($path);
						}
						else {
							$css_file_content .= "\n// SOURCE ".str_replace($this->root_dir.'/', '', $path).
												 "\n".file_get_contents($path);
						}
					}
				}
			}
		}
		$css_file_content = str_replace(['../', '@import "node_modules', 'url("fonts'], ['', '@import "../node_modules', 'url("../../fonts'], $css_file_content);
		if($this->root_dir_core) {
			$this->css_file = str_replace($this->base_dir, $this->root_dir_core.'/', $this->css_file);
		}
		file_put_contents($this->css_file, $css_file_content);

		return $this;
	}

	/**
	 * @throws \ReflectionException
	 */
	public function genere_php_doc_array() {
		$this->parse();
		$tmp = [];
		foreach ($this->php_array as $key => $path) {
			if($this->is_string($path)) {
				$class        = '';
				$namespace    = '';
				$is     = null;
				$file_content = file_get_contents($path);
				if(preg_match('`namespace ([^;]+);[^µ]+c\nlass ([a-zA-Z0-9\_]+)\ `', $file_content,$matches)) {
					$is = 'class';
					$namespace = $matches[1];
					$class = $matches[2];
				}
				elseif (preg_match('`namespace ([^;]+);[^µ]+\ntrait ([a-zA-Z0-9\_]+)\ `', $file_content,$matches)) {
					$is = 'class';
					$namespace = $matches[1];
					$class = $matches[2];
				}
				elseif(preg_match('`namespace ([^;]+);[^µ]+\nfunction ([a-zA-Z0-9\_]+)\ `', $file_content,$matches)) {
					$is = 'function';
					$namespace = $matches[1];
					$class = $matches[2];
				}

				if($is) {
					$tmp[$key] = [
						'source'      => $path,
						'is'        => $is,
						'namespace' => $namespace,
						$is         => $class
					];
				}
			}
			else {
				foreach ($path as $_key => $_path) {
					$class        = '';
					$namespace    = '';
					$is     = null;
					$file_content = file_get_contents($_path);
					if(preg_match('`namespace ([^;]+);[^µ]+\nclass ([a-zA-Z0-9\_]+)\ `', $file_content,$matches)) {
						$is = 'class';
						$namespace = $matches[1];
						$class = $matches[2];
					}
					elseif (preg_match('`namespace ([^;]+);[^µ]+\ntrait ([a-zA-Z0-9\_]+)\ `', $file_content,$matches)) {
						$is = 'class';
						$namespace = $matches[1];
						$class = $matches[2];
					}
					elseif(preg_match('`namespace ([^;]+);[^µ]+\nfunction ([a-zA-Z0-9\_]+)\ `', $file_content,$matches)) {
						$is = 'function';
						$namespace = $matches[1];
						$class = $matches[2];
					}

					if($is) {
						$tmp[$key][$_key] = [
							'source'      => $_path,
							'is'        => $is,
							'namespace' => $namespace,
							$is         => $class
						];
					}
				}
			}
		}

		foreach ($tmp as $key => $infos) {
			if($this->is_array($infos)) {
				foreach ($infos as $_key => $_infos) {
					require_once $_infos['source'];
					$is = $_infos['is'];
					$class = '\Reflection'.ucfirst($is);
					$namespace = $_infos['namespace'];
					$complete_class = $namespace.'\\'.$_infos[$is];
					/**
					 * @var \ReflectionClass|\ReflectionFunction $reflexion
					 */
					$reflexion = (new $class($complete_class));
					$global_comment = $reflexion->getDocComment();
					$tmp[$key][$_key]['_comment'] = $global_comment;
					if($is === 'class') {
						foreach (get_class_vars($complete_class) as $var => $value) {
							$prop = (new \ReflectionProperty($complete_class, $var));
							if($prop->isPublic() || $prop->isProtected()) {
								if ($doc = $prop->getDocComment()) {
									$tmp[$key][$_key]['vars'][$var] = $doc;
								}
							}
						}
						foreach (get_class_methods($complete_class) as $method) {
							$methode = (new \ReflectionMethod($complete_class, $method));
							if ($doc = $methode->getDocComment()) {
								$tmp[$key][$_key]['methods'][$method] = $doc;
							}
						}
					}
				}
			}
			else {
				require_once $infos['source'];
				$is = $infos['is'];
				$class = '\Reflection'.ucfirst($is);
				$namespace = $infos['namespace'];
				$complete_class = $namespace.'\\'.$infos[$is];
				/**
				 * @var \ReflectionClass|\ReflectionFunction $reflexion
				 */
				$reflexion = (new $class($complete_class));
				$global_comment = $reflexion->getDocComment();
				$tmp[$key]['_comment'] = $global_comment;
				if($is === 'class') {
					foreach (get_class_vars($complete_class) as $var => $value) {
						$prop = (new \ReflectionProperty($complete_class, $var));
						if($prop->isPublic() || $prop->isProtected()) {
							if ($doc = $prop->getDocComment()) {
								$tmp[$key]['vars'][$var] = $doc;
							}
						}
					}
					foreach (get_class_methods($complete_class) as $method) {
						$methode = (new \ReflectionMethod($complete_class, $method));
						if ($doc = $methode->getDocComment()) {
							$tmp[$key]['methods'][$method] = $doc;
						}
					}
				}
			}
		}
		$this->docs = $tmp;

		return $this;
	}
}