<?php

namespace project\utils;


use project\extended\classes\util;
use project\extended\traits\http;

class ScssParser extends util {
	use http;
	private $root_dir = null, $root_dir_core = null, $root_dir_custom = null, $base_dir = '/scss/', $scss_suffix = 'scss', $css_file = 'main', $css_suffix = 'css', $last_update_file = 'last_update.txt',
			$html_doc_dir = 'layouts/CssDoc/', $html_doc_file = 'index.view.html', $php_doc_file = 'index.php';
	private $scss_array = [], $docs = [];
	private $scss_reg_exp, $css_reg_exp;
	private $enable_last_updated = true;

	public function __construct($root_dir = null) {
		parent::__construct();
		if(is_array($root_dir) && !empty($root_dir)) {
			$this->root_dir = $root_dir[0];
		}
		$this->base_dir     = is_null($this->root_dir) ? __DIR__.$this->base_dir : $this->root_dir.$this->base_dir;
		$this->css_file     = $this->base_dir.$this->css_file.'.'.$this->scss_suffix;
		$this->scss_reg_exp = '/^([0-9]+)_(.+)\.'.$this->scss_suffix.'$/';
		$this->css_reg_exp = '/^([0-9]+)_(.+)\.'.$this->css_suffix.'$/';
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
		$this->{'root_dir_'.$type} = $path;
	}

	public function get_last_update_file() {
		if(is_file($this->base_dir.'/'.$this->last_update_file) && $this->enable_last_updated) {
			return file_get_contents($this->base_dir.'/'.$this->last_update_file);
		}
		return '';
	}

	public function get_css_filename() {
		return $this->css_file.'.'.$this->css_suffix;
	}

	public function get_scss_array() {
		$this->parse();
		return $this->scss_array;
	}

	public function get_doc_array() {
		return $this->docs;
	}

	private function parcour($directory, $step = 0) {
		if(is_dir($directory)) {
			foreach (new \DirectoryIterator($directory) as $fileInfo) {
				if (!$fileInfo->isDot() && $fileInfo->isDir() && strstr($fileInfo->getBasename(), '_')) {
					$new_directory = substr($directory, strlen($directory) - 1, 1) === '/' ? substr($directory, 0, strlen($directory) - 1) : $directory;
					$this->parcour($new_directory.'/'.$fileInfo->getBasename(), $step + 1);
				} elseif ($fileInfo->isFile()
						  && (strstr($fileInfo->getFilename(), '.'.$this->scss_suffix)
							  ||
							  strstr($fileInfo->getFilename(), '.'.$this->css_suffix))
						  && $fileInfo->getFilename() !== 'main.'.$this->scss_suffix
						  && $fileInfo->getFilename() !== 'main.'.$this->css_suffix
						  && $filepath = $fileInfo->getPathname()) {
					// Fichiers à l'étage 1

					$basename = basename($filepath);
					if (preg_match($this->scss_reg_exp, $basename, $matches) || preg_match($this->css_reg_exp, $basename, $matches)) {
						if ($step === 0) {
							$this->scss_array[$basename] = $filepath;
						} elseif ($step === 1) {
							$directory_name                               = basename(dirname($filepath));
							$this->scss_array[$directory_name][$basename] = $filepath;
						} elseif ($step > 1) {
							$directory_name     = basename(dirname($filepath));
							$sub_directory_name = basename(dirname(str_replace($directory_name.'/'.$basename, '', $directory)));
							$key                = $sub_directory_name.'/'.$directory_name;
							for ($i = 2, $max = $step; $i < $max; $i++) {
								$dir = str_replace($key, '', $directory);
								$key = basename($dir).'/'.$key;
							}

							$this->scss_array[$key][$basename] = $filepath;
						}
					}
				}
			}
		}
	}

	public function parse() {
		if($this->root_dir_core && $this->root_dir_custom) {
			$this->parcour($this->root_dir_core);
			$this->parcour($this->root_dir_custom);
		}
		else {
			$this->parcour($this->base_dir);
		}

		ksort($this->scss_array);
		foreach ($this->scss_array as $key => $value) {
			if($this->is_array($value)) {
				ksort($this->scss_array[$key]);
			}
		}

		return $this;
	}

	public function genere_scss_file() {
		$this->parse();
		$css_file_content = '';
		foreach ($this->get_scss_array() as $directory => $file_and_directory_array) {
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

	public function genere_scss_doc_array() {
		$file_content = file_get_contents($this->css_file);
		$doc_array = [];

		preg_replace_callback('`\/\/\ SOURCE\ ([a-zA-Z0-9\_\-\.\/]+)\n\/\*([^*]+)\*\/`', function ($matches) use (&$doc_array) {
			$source  = $matches[1];
			$doc = $matches[2];
			$doc_array[] = "@source\n".$source.$doc;
		}, $file_content);

		$this->docs = $doc_array;
		return $this;
	}

	public function genere_doc_file() {

		/**
		 * @var DocGenerator $doc_generator;
		 */
		$doc_generator = $this->get_util('DocGenerator');

		$block_css = '';
		$stylesguide = [];
		foreach ($this->docs as $cmp => $doc) {
			$doc = explode("\n", $doc);
			foreach ($doc as $i => $value) {
				if($value === '' || $value === ' ') {
					unset($doc[$i]);
				}
				if(substr($value, 0, 1) === ' ') {
					$doc[$i] = substr($value, 1, strlen($value));
				}
			}
			$new_doc_array = [];
			$last_key = '';
			foreach ($doc as $i => $value) {
				if(substr($value, 0, 1) === '@') {
					$last_key = str_replace('@', '', $value);
					$new_doc_array[$last_key] = '';
				}
				else {
					$new_doc_array[$last_key] .= $value."\n";
				}
			}
			$doc = $new_doc_array;
			$id = str_replace([' ', '-', '\'', ',', '[', ']', "\n"], ['', '', '_', '_', '6', '3', ''], $doc['title']);
			if(isset($doc['styleguide'])) {
				$doc['styleguide'] = str_replace("\n", '', $doc['styleguide']);
				$path = explode('.', $doc['styleguide']);
				if(count($path) === 1) {
					$stylesguide[$path[0]] = $id;
				}
				elseif (count($path) === 2) {
					$stylesguide[$path[0]][$path[1]] = $id;
				}
				elseif (count($path) === 3) {
					$stylesguide[$path[0]][$path[1]][$path[2]] = $id;
				}
				else {
					$part1 = $path[0];
					$part3 = $path[count($path)-1];
					unset($path[count($path)-1]);
					unset($path[0]);
					$part2 = implode('.', $path);
					$stylesguide[$part1][$part2][$part3] = $id;
				}
			}

			$block_css .= $doc_generator::code_card($id, $doc, 'css');
		}

		$html = $doc_generator::genere_template_file($this->enable_last_updated);

		$html = str_replace('[css_nav_menu]', $doc_generator::menu($stylesguide, 'css'), $html);
		$html = str_replace('[css_doc_page]', $block_css, $html);

		$html = str_replace('[php_nav_menu]', '', $html);
		$html = str_replace('[php_doc_page]', '', $html);

		if(!is_dir(ROOT_PATH.$this->html_doc_dir)) {
			mkdir(ROOT_PATH.$this->html_doc_dir, 0777, true);
		}
		if(!file_exists(ROOT_PATH.$this->html_doc_dir.$this->html_doc_file) || $html !== file_get_contents(ROOT_PATH.$this->html_doc_dir.$this->html_doc_file)) {
			file_put_contents(ROOT_PATH.$this->html_doc_dir.$this->html_doc_file, $html);
			if($this->enable_last_updated) {
				if($this->root_dir_core) {
					file_put_contents(realpath($this->root_dir_core).'/'.$this->last_update_file, date('Y-m-d'));
				}
				else {
					file_put_contents(realpath($this->base_dir).'/'.$this->last_update_file, date('Y-m-d'));
				}

			}
		}

		if($this->php_doc_file) {
			$php = '<?php
			
	namespace project;
			
	use project\extended\classes\view;
	use project\utils\Project;

	require_once \'autoload.php\';
					
	echo Project::CssDoc(function ($_this, $metas, $args) {
		$page_name = $args[\'page_name\'];
		$template_name = $args[\'template_name\'];
		
		/** @var Project $_this */
		return view::get(
			[\'page_name\' => $page_name],
			[\'template_name\' => $template_name],
			[\'last_update\' => $_this->get_scss_parser(ROOT_PATH)->get_last_update_file()]
		);
	});';
			if (!is_file($this->php_doc_file)) {
				file_put_contents(ROOT_PATH.$this->php_doc_file, $php);
			}
		}
	}

	public function prepare_main_for_sass_compilation() {
		$sass = file_get_contents($this->css_file);
		preg_replace_callback('`(\/\/\ SOURCE\ [a-zA-Z0-9\_\-\.\/]+\n)(\/\*[^*]+\*\/)`', function ($matches) use (&$sass) {
			$sass = str_replace($matches[1].$matches[2], '', $sass);
		}, $sass);
		if($this->root_dir_core) {
			file_put_contents($this->root_dir_core.'/ready-to-compile.'.$this->scss_suffix, $sass);
		}
		else {
			file_put_contents($this->base_dir.'/ready-to-compile.'.$this->scss_suffix, $sass);
		}
		return $this;
	}

	/**
	 * @throws \Exception
	 */
	public function compile() {
		$main_file = str_replace('.'.$this->scss_suffix, '.'.$this->css_suffix, $this->css_file);
		$output = null;
		$retour = null;
		if($this->root_dir_core) {
			exec('node-sass '.$this->root_dir_core.'/ready-to-compile.scss '.$main_file, $output, $retour);
		}
		else {
			exec('node-sass '.$this->base_dir.'/ready-to-compile.scss '.$main_file, $output, $retour);
		}
		if(file_exists($main_file)) {
			if(!is_dir($this->root_dir_core.'/concat')) {
				mkdir($this->root_dir_core.'/concat', 0777);
			}
			exec('mv '.$main_file.' '.$this->root_dir_core.'/concat/main.css');
			if(is_file($this->root_dir_core.'/concat/main.css')) {
				if(is_file($main_file)) {
					unlink($main_file);
				}
				$main_file = $this->root_dir_core.'/concat/main.css';
			}
		}
		unlink(($this->root_dir_core ? $this->root_dir_core : $this->base_dir).'/ready-to-compile.scss');
		unlink($this->css_file);
		$main_file_content = file_get_contents($main_file);
		$main_file_content = str_replace("\n", '', $main_file_content);
		file_put_contents($main_file, $main_file_content);
		if(count($output) === 0) {
			throw new \Exception('La compilation sass à échoué');
		}
	}
}