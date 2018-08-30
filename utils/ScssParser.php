<?php

namespace project\utils;


use project\extended\classes\util;

class ScssParser extends util {
	private $root_dir = null, $base_dir = '/scss/', $scss_suffix = 'scss', $css_file = 'main', $css_suffix = 'css',
			$html_doc_dir = 'views/CssDoc', $html_doc_file = 'index.view.html', $php_doc_file = 'cssdoc.php';
	private $scss_array = [], $docs = [];
	private $scss_reg_exp, $css_reg_exp;

	public function __construct($root_dir = null) {
		parent::__construct();
		if(is_array($root_dir) && !empty($root_dir)) {
			$this->root_dir = $root_dir[0];
		}
		$this->base_dir     = is_null($this->root_dir) ? __DIR__.$this->base_dir : $this->root_dir.$this->base_dir;
		$this->css_file     .= '.'.$this->scss_suffix;
		$this->css_file     = $this->base_dir.$this->css_file;
		$this->scss_reg_exp = '/^([0-9]+)_(.+)\.'.$this->scss_suffix.'$/';
		$this->css_reg_exp = '/^([0-9]+)_(.+)\.'.$this->css_suffix.'$/';
	}

	public function parse() {
		/*
		 * Parcour les répertoires à la racine du répertoire 'test'
		 */
		foreach (glob($this->base_dir.'**/*.'.$this->scss_suffix) as $filepath) {
			$basename = basename($filepath);
			if (preg_match($this->scss_reg_exp, $basename, $matches)) {
				$directory_name  								= basename(dirname($filepath));
				$directory_order 								= (int)strtok($directory_name, '_');
				$directory_ident 								= strtok('');

				$order       									= (int)$matches[1];
				$this->scss_array[$directory_ident][$basename]  = $filepath;
				$files_order[$directory_ident][$basename] 		= $order;
				$directories_order[$directory_ident]        	= $directory_order;
			}
		}
		/*
		 * Parcour les sous-répertoires
		 */
		foreach (glob($this->base_dir.'**/**/*.'.$this->scss_suffix) as $filepath) {
			$basename = basename($filepath);
			$directory = str_replace($basename, '', $filepath);
			if (preg_match($this->scss_reg_exp, $basename, $matches)) {
				$directory_name  												 = basename(dirname($filepath));
				$sub_directory_name 											 = basename(dirname(str_replace($directory_name.'/'.$basename, '', $directory)));
				$directory_order												 = (int)strtok($sub_directory_name, '_');
				$directory_ident 												 = strtok('');

				$order       													 = (int)$matches[1];
				$this->scss_array[$directory_ident][$directory_name][$basename]  = $filepath;
				$files_order[$directory_ident][$directory_name][$basename] 		 = $order;
				$directories_order[$directory_ident] 							 = $directory_order;
			}
		}

		foreach (glob($this->base_dir.'**/*.'.$this->css_suffix) as $filepath) {
			if(($basename = basename($filepath)) !== 'main.css') {
				if (preg_match($this->css_reg_exp, $basename, $matches)) {
					$directory_name  = basename(dirname($filepath));
					$directory_order = (int)strtok($directory_name, '_');
					$directory_ident = strtok('');

					$order                                         = (int)$matches[1];
					$this->scss_array[$directory_ident][$basename] = $filepath;
					$files_order[$directory_ident][$basename]      = $order;
					$directories_order[$directory_ident]           = $directory_order;
				}
			}
		}

		foreach (glob($this->base_dir.'**/**/*.'.$this->css_suffix) as $filepath) {
			$basename = basename($filepath);
			$directory = str_replace($basename, '', $filepath);
			if($basename !== 'main.css') {
				if (preg_match($this->css_reg_exp, $basename, $matches)) {
					$directory_name     = basename(dirname($filepath));
					$sub_directory_name = basename(dirname(str_replace($directory_name.'/'.$basename, '', $directory)));
					$directory_order    = (int)strtok($sub_directory_name, '_');
					$directory_ident    = strtok('');

					$order                                                          = (int)$matches[1];
					$this->scss_array[$directory_ident][$directory_name][$basename] = $filepath;
					$files_order[$directory_ident][$directory_name][$basename]      = $order;
					$directories_order[$directory_ident]                            = $directory_order;
				}
			}
		}

		foreach ($this->scss_array as $key => $value) {
			ksort($this->scss_array[$key]);
		}

		return $this;
	}

	public function get_css_filename() {
		return $this->css_file.'.'.$this->css_suffix;
	}

	public function get_scss_array() {
		return $this->scss_array;
	}

	public function genere_scss_file() {
		$this->parse();
		$css_file_content = '';
		foreach ($this->get_scss_array() as $directory => $file_and_directory_array) {
			foreach ($file_and_directory_array as $file_or_directory => $path_or_array) {
				if(is_array($path_or_array)) {
					foreach ($path_or_array as $path) {
						if(file_get_contents($path) !== '') {
							$css_file_content .= "\n// SOURCE ".str_replace($this->root_dir.'/', '', $path).
												 "\n".file_get_contents($path);
						}
					}
				}
				else {
					if(file_get_contents($path_or_array) !== '') {
						$css_file_content .= "\n// SOURCE ".str_replace($this->root_dir.'/', '', $path_or_array).
											 "\n".file_get_contents($path_or_array);
					}
				}
			}
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

	public function get_doc_array() {
		return $this->docs;
	}

	public function genere_doc_file() {
		$html = '<!Doctype html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Documentation Css</title>
		<style>
			.source-file {
				font-size: small;
			}
			.source-code {
				margin-bottom: 25px;
				margin-top: 15px;
			}
			.exemples-code {
				margin-bottom: 50px;
				margin-top: 15px;
			}
		</style>
	</head>
	<body>
		<div style="text-align: center;">
			<h1>Documentation Css</h1>
		</div>';

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
					$last_key = substr($value, 1, strlen($value)-1);
					$new_doc_array[$last_key] = '';
				}
				else {
					$new_doc_array[$last_key] .= $value."\n";
				}
			}
			$doc = $new_doc_array;


			$html .= '<i class="source-file">Fichier source: '.$doc['source'].'</i>';
			$html .= '<h2>'.$doc['title'].'</h2>';
			$html .= '<p>'.$doc['description'].'</p>';
			$html .= '<div>
		<b>EXEMPLES</b>
		<br />
		<div class="exemples-code" style="margin-bottom: 50px; margin-top: 15px;;">
			'.$doc['Markup:'].'
		</div>
	</div>
	<div>
		<b>CODE SOURCE</b>
		<br />
		<div class="source-code">
			<code>
<pre>'.htmlentities($doc['Markup:']).'</pre>
			</code>
		</div>
	</div>';
			if($cmp < count($this->docs)-1) {
				$html .= '<hr />';
			}
		}
		$html .= '	</body>
	</html>';

		if(!is_dir($this->html_doc_dir)) {
			mkdir($this->html_doc_dir, 0777, true);
		}
		file_put_contents($this->html_doc_dir.'/'.$this->html_doc_file, $html);

		$php = '<?php
			
	namespace project;
			
	use project\extended\classes\view;
	use project\utils\Project;

	require_once \'autoload.php\';
					
	echo Project::CssDoc(function ($_this, $args) {
		$page_name = $args[\'page_name\'];
		$template_name = $args[\'template_name\'];
		
		return view::get(
			[\'page_name\' => $page_name],
			[\'template_name\' => $template_name]
		);
	}, [\'__DIR__\', __DIR__]);';

		file_put_contents($this->php_doc_file, $php);
	}
}