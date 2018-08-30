<?php

namespace project\utils;


use project\extended\classes\util;

class ScssParser extends util {
	private $root_dir = null, $base_dir = '/scss/', $scss_suffix = 'scss', $css_file = 'main', $css_suffix = 'css', $last_update_file = 'last_update.txt',
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

	public function get_last_update_file() {
		return file_get_contents($this->base_dir.'/'.$this->last_update_file);
	}

	public function get_css_filename() {
		return $this->css_file.'.'.$this->css_suffix;
	}

	public function get_scss_array() {
		return $this->scss_array;
	}

	public function get_doc_array() {
		return $this->docs;
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

	public function genere_doc_file() {
		$html = '<!Doctype html>
<html style="overflow-x: hidden;">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<title>Documentation Css</title>
		
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		<link rel="stylesheet" href="/scss/hightlight/styles/default.css">
	</head>
	<body>
		<header style="text-align: center;">
			<h1>Documentation Css</h1>
		</header>
		<div class="row">
			<nav class="col-3" style="min-height: 200px;">
				[nav_menu]
			</nav>
			<div class="col-9">
				<main class="container">';
		$stylesgide = [];
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
			if(isset($doc['Styleguide'])) {
				$doc['Styleguide'] = str_replace("\n", '', $doc['Styleguide']);
				$path = explode('.', $doc['Styleguide']);
				if(count($path) === 1) {
					$stylesgide[$path[0]] = $id;
				}
				elseif (count($path) === 2) {
					$stylesgide[$path[0]][$path[1]] = $id;
				}
				elseif (count($path) === 3) {
					$stylesgide[$path[0]][$path[1]][$path[2]] = $id;
				}
			}

			$html .= '		<div class="col-12" id="'.$id.'">';
			$html .= '			<i class="source-file">Fichier source: '.$doc['source'].'</i>';
			$html .= '			<h2>'.$doc['title'].'</h2>';
			$html .= '			<p>'.$doc['description'].'</p>';
			$html .= '			<div>
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
						<pre><code class="html">'.htmlentities($doc['Markup:']).'</code></pre>
					</div>
				</div>
			</div>';
			$html .= '			<hr />';
		}

		$nav = '<ul style="position: fixed;">';
		foreach ($stylesgide as $categorie => $sub_cat) {
			$nav .= '	<li>';
			$nav .= '		<b>
								'.$categorie.'
							</b>
							<ul>';
			foreach ($sub_cat as $class => $sub_class) {
				$nav .= '		<li>
									<b>'.$class.'</b>
									<ul>';
				foreach ($sub_class as $sub_sub_class => $id_div) {
					$nav .= '			<li><a href="#'.$id_div.'">'.$sub_sub_class.'</a></li>';
				}
				$nav .= '			</ul>
								</li>';
			}
			$nav .= '		</ul>
						</li>';
		}
		$nav .= '</ul>';
		$html .= '			</main>
						</div>
					</div>';
		$html .= '
	<footer style="text-align: center;">
		Dernière modification: [last_update]
	</footer>';
		$html .= '
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
		<script src="/scss/hightlight/highlight.pack.js"></script>
		<script>hljs.initHighlightingOnLoad();</script>';
		$html .= '	</body>
	</html>';
		$html = str_replace('[nav_menu]', $nav, $html);

		if(!is_dir($this->html_doc_dir)) {
			mkdir($this->html_doc_dir, 0777, true);
		}
		if($html !== file_get_contents($this->html_doc_dir.'/'.$this->html_doc_file)) {
			file_put_contents($this->html_doc_dir.'/'.$this->html_doc_file, $html);
			file_put_contents($this->base_dir.'/'.$this->last_update_file, date('Y-m-d'));
		}

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
			[\'template_name\' => $template_name],
			[\'last_update\' => $_this->get_scss_parser(__DIR__)->get_last_update_file()]
		);
	}, [\'__DIR__\', __DIR__]);';

		file_put_contents($this->php_doc_file, $php);
	}
}