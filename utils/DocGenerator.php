<?php

namespace project\utils;


use project\extended\classes\util;
use project\extended\traits\http;
use const project\ROOT_PATH;

class DocGenerator extends util {
	use http;
	private $root_dir = null;
	private static $static_root_dir;
	private $scss_doc_enabled = true;
	private $php_doc_enabled = true;
	private $scss_parser = null;
	private $php_parser = null;
	private $doc_dir = 'doc/';
	private $doc_file = 'index.php';

	public function __construct($root_dir = null) {
		parent::__construct();
		if(is_array($root_dir) && !empty($root_dir)) {
			$this->root_dir = $root_dir[0];
			self::$static_root_dir = $root_dir[0];
		}
	}

	public static function menu($guide, $language) {
		$nav = '<ul id="doc-'.$language.'" class="collapse list-unstyled show">';
		foreach ($guide as $categorie => $sub_cat) {
			$nav .= '	<li>'."\n";
			$nav .= '<a href="#doc-'.$language.'_'.strtolower($categorie).'" aria-expanded="false" data-toggle="collapse">
						<i class="fa fa-folder"></i> '.$categorie.'
					 </a>
					 <ul id="doc-'.$language.'_'.strtolower($categorie).'" class="collapse list-unstyled">'."\n";
			foreach ($sub_cat as $class => $sub_class) {
				$nav .= '		<li>'."\n";
				if(self::is_array($sub_class)) {
					$nav .= '	<a href="#doc-'.$language.'_'.strtolower($categorie).'-'.str_replace('.', '_', $class).'" aria-expanded="false" data-toggle="collapse">
		<i class="fa fa-folder"></i> '.$class.'
	</a>
					 <ul id="doc-'.$language.'_'.strtolower($categorie).'-'.str_replace('.', '_', $class).'" class="collapse list-unstyled">'."\n";
					foreach ($sub_class as $sub_sub_class => $id_div) {
						$nav .= '			<li>
				<a class="js-scrollTo" href="#doc-'.$language.'_'.$id_div.'"><i class="fa fa-css3"></i> '.$sub_sub_class.'</a>
			</li>'."\n";
					}
					$nav .= '			</ul>'."\n";
				}
				else {
					$nav .= '<a class="js-scrollTo" href="#doc-'.$language.'_'.$sub_class.'"><i class="fa fa-css3"></i> '.$class.'</a>'."\n";
				}
				$nav .= '		</li>'."\n";
			}
			$nav .= '		</ul>
						</li>'."\n";
		}
		$nav .= '</ul>
		';
		return $nav;
	}

	public static function code_card($id, $doc, $language, $interpretation = true) {
		$title = $doc['title'];
		$source = $doc['source'];
		$description = $doc['description'];
		$card_markup = '';

		if(isset($doc['markup']) && $doc['markup'] !== '') {
			$tmp_markup = $doc['markup'];

			if(!isset($doc['modifiers']) || !$interpretation) {
				$card_markup = '
					<div>
					   <b>EXEMPLES</b>
					   <br/>
					   <div class="exemples-code" style="margin-bottom: 50px; margin-top: 15px;">
						   '.$doc['markup'].'
					   </div>
				   </div>';
			}
			else {
				$modifiers = explode("\n", $doc['modifiers']);
				$card_markup = '<div>
						<b>EXEMPLES</b>
						<br/>';
				$card_markup .= '<div class="exemples-code" style="margin-bottom: 50px; margin-top: 15px;">';
				$card_markup .= '<div class="mb-3">
							<h2>default</h2>
							'.str_replace('[class_modifier]', '', $doc['markup']).'
						</div>';
				foreach ($modifiers as $modifier) {
					if($modifier !== '') {
						$class_name  = explode(' - ', $modifier)[0];
						$class_title = explode(' - ', $modifier)[1];
						$card_markup .= '<div class="mb-3">
								<h2>'.ucfirst($class_title).'</h2>
								<p>'.$class_name.'</p>
								'.str_replace('[class_modifier]', str_replace('.', '', $class_name), $doc['markup']).'
							</div>';
					}
				}
				$card_markup .= '</div>';
				$card_markup .= '</div>';
			}
			$card_markup .= '<div>
						<b>CODE SOURCE</b>
						<br/>
						<div class="source-code">
							 <pre class="brush: xml;">'.htmlentities($tmp_markup).'</pre>
						</div>
				   </div>';
		}

		$html = '
<div class="row">
	<div class="col-12 card" id="doc-'.$language.'_'.$id.'">
         <div class="card-header">
              <i class="fa fa-file font-italic text-truncate" style="font-size: 15px; width: 100%;"> Fichier source: '.$source.'</i>
              <h2 class="card-title">'.$title.'</h2>
         </div>
         <div class="card-body">
               <p>
                   '.$description.'
               </p>
               '.$card_markup.'
         </div>
    </div>
</div>
';
		return $html;
	}

	public static function genere_template_file($last_update_enabled = false) {
		$html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Documentation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../node_modules/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700">
	<link href="../scss/syntax_hightlighter/shCore.css" rel="stylesheet" type="text/css">
	<link href="../scss/syntax_hightlighter/shThemeDefault.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../scss/concat/main.css">
    <link rel="shortcut icon" href="../img/css3.png">
    
    <!-- Tweaks for older IEs--><!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->

    <script src="../node_modules/jquery/dist/jquery.min.js"></script>
    <script src="../node_modules/jquery.cookie/jquery.cookie.js"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
    <script src="../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
	<script src="../js/syntax_hightlighter/shCore.js" type="text/javascript"></script>
	<script src="../js/syntax_hightlighter/shBrushXml.js" type="text/javascript"></script>
	<script src="../js/syntax_hightlighter/shBrushJScript.js" type="text/javascript"></script>
	<script src="../js/syntax_hightlighter/shBrushPhp.js" type="text/javascript"></script>
	<script src="../node_modules/jquery-circle-progress/dist/circle-progress.min.js" type="text/javascript"></script>
    <script>
        $(document).ready(function() {
            SyntaxHighlighter.all();
            $(".js-scrollTo").on("click", function() { // Au clic sur un élément
                let page = $(this).attr("href"); // Page cible
                let speed = 750; // Durée de l\'animation (en ms)
                $("html, body").animate( { scrollTop: $(page).offset().top }, speed ); // Go
                return false;
            });
        });
    </script>
    <style>
		.syntaxhighlighter.xml .toolbar {
			display: none !important;
		}
	</style>
</head>
<body class="cssdoc">
<!-- Side Navbar -->
<nav class="side-navbar">
    <div class="side-navbar-wrapper">
        <!-- Sidebar Header    -->
        <div class="sidenav-header d-flex align-items-center justify-content-center">
            <!-- User Info-->
            <div class="sidenav-header-inner text-center">
                <div class="img-fluid rounded-circle" style="cursor: default;">
                    <span style="border: 1px solid white; padding: 15px; font-size: 20px; -webkit-border-radius: 35px;-moz-border-radius: 35px;border-radius: 35px;">
                        NC
                    </span>
                </div>
                <h2 class="h5">Nicolas Choquet</h2>
                <span>Web Developer</span>
            </div>
            <!-- Small Brand information, appears on minimized sidebar-->
            <div class="sidenav-header-logo">
                <a href="//'.self::get_complete_current_url().'" class="brand-small text-center">
                    <strong class="text-primary">N</strong>
                    <strong class="text-primary">C</strong>
                </a>
            </div>
        </div>
        <!-- Sidebar Navigation Menus-->
        <div class="main-menu">
            <h5 class="sidenav-heading">Doc.</h5>
            <ul id="side-main-menu" class="side-menu list-unstyled">
                <li>
                    <a href="#doc-css" aria-expanded="true" data-toggle="collapse">
                        <i>
                            <img src="../img/css3.png" style="height: 25px; width: 25px;">
                        </i>
                        CSS
                        <div class="badge badge-info">SASS</div>
                    </a>
                    [css_nav_menu]
                </li>
                <li>
                    <a href="#">
                        <i>
                            <img src="../img/php7.png" style="height: 25px; width: 18px; margin-left: 5px;">
                        </i>
                        PHP
                        <div class="badge badge-info">MVC</div>
                        <div class="badge badge-info">NOT IMPLEMENTED</div>
                    </a>
                    [php_nav_menu]
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="page">
    <!-- navbar-->
    <header class="header">
        <nav class="navbar">
            <div class="container-fluid">
                <div class="navbar-holder d-flex align-items-center justify-content-between">
                    <div class="navbar-header">
                        <a id="toggle-btn" href="#" class="menu-btn">
                            <i class="icon-bars"> </i>
                        </a>
                        <a href="//'.self::get_complete_current_url().'" class="navbar-brand">
                            <div class="brand-text d-none d-md-inline-block">
                                <strong class="text-primary">Documentation</strong>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <!-- Updates Section -->
    <section class="mt-30px mb-30px">
        <div class="container-fluid">
        	[css_doc_page]
        	[php_doc_page]
        </div>
    </section>
    <footer class="main-footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <p>base_object &copy; 2017-2019</p>';
		if($last_update_enabled) {
			$html .= '	<p>Dernières modification: [last_update]</p>';
		}
		$html .= '</div>
        	</div>
        </div>
    </footer>
    <div class="row">
        <div class="col-12">
            [debug]
        </div>
    </div>
</div>
<!-- JavaScript files-->
<script src="../node_modules/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js"></script>
<!-- Main File-->
<script src="../js/front.js"></script>
</body>
</html>';

		return $html;
	}

	public static function create_php_page($path, $function_name, $logs_array = []) {
		if(self::is_string($function_name)) {
			$function_name = [$function_name];
			$logs_array = [$logs_array];
		}
		$default_function = $function_name[0];
		$php_header = '<?php
			
	namespace project;
			
	use project\extended\classes\view;
	use project\utils\DocGenerator;
	use project\utils\PhpParser;
	use project\utils\Project;

	require_once \'../autoload.php\';';

		$php = $php_header;

		foreach ($function_name as $id => $func_name) {
			$php .= '
	function '.$func_name.'() {
		return Project::'.$func_name.'(function ($_this, $metas, $args) {
			$page_name     = $args[\'page_name\'];
			$template_name = $args[\'template_name\'];
			
			';
			if(isset($logs_array[$id])) {
				foreach ($logs_array[$id] as $key => $log) {
					if (self::is_array($log)) {
						$php .= '			debug::log([\''.implode("', '", $log).'\'], \''.$key.'\');';
					} else {
						$php .= '			debug::log(\''.$log.'\', \''.$key.'\');';
					}
				}
			}
			$php .= '

			/** @var Project $_this */
			/** @var DocGenerator $doc_generator */
			/** @var PhpParser $php_parser */
			$doc_generator = $_this->get_util(\'DocGenerator\');
			$php_parser    = $doc_generator->get_php_parser();
			$php_parser->genere_php_doc_array();

			return view::get(
				[\'page_name\' => $page_name],
				[\'template_name\' => $template_name],
				[\'last_update\' => $_this->get_scss_parser(ROOT_PATH)->get_last_update_file()]
			);
		});
	}
	';
		}
		$php .= '$dir = str_replace(dirname(__DIR__), \'\', __DIR__).\'/\';'."\n";
		$max = count($function_name);
		foreach ($function_name as $id => $func_name) {
			if($id === 0) {
				$uri = str_replace('Doc', '', $func_name);
				$uri = strtolower($uri);
				$php .= '
	if(Project::http_server(\'REQUEST_URI\') === $dir.\''.$uri.'\') {
		echo '.$func_name.'();
	}';
			}
			elseif ($id < $max && $id !== 0) {
				$uri = str_replace('Doc', '', $func_name);
				$uri = strtolower($uri);
				$php .= '
	elseif(Project::http_server(\'REQUEST_URI\') === $dir.\''.$uri.'\') {
		echo '.$func_name.'();
	}';
			}
		}

		$php .= '
	else {
		echo '.$default_function.'();
	}';

		if (!file_exists($path) || (file_exists($path) && $php !== file_get_contents($path))) {
			file_put_contents($path, $php);
		}
	}

	/**
	 * @param array ...$root_dirs
	 * @return DocGenerator
	 */
	public function get_scss_parser(...$root_dirs) {
		/**
		 * @var ScssParser $scss_parser
		 */
		$scss_parser = $this->get_util('ScssParser', $root_dirs[0]);
		if (count($root_dirs) === 2) {
			$scss_parser->set_root_dir('core', $root_dirs[0]);
			$scss_parser->set_root_dir('custom', $root_dirs[1]);
		}
		$this->scss_parser = $scss_parser;
		return $this;
	}

	/**
	 * @param array ...$root_dirs
	 * @return DocGenerator
	 */
	public function get_php_parser(...$root_dirs) {
		$php_parser = null;
		if($this->php_doc_enabled) {
			/**
			 * @var PhpParser $php_parser
			 */
			$php_parser = empty($root_dirs) ? $this->get_util('PhpParser') : $this->get_util('PhpParser', $root_dirs[0]);
			if (count($root_dirs) === 2) {
				$php_parser->set_root_dir('core', $root_dirs[0]);
				$php_parser->set_root_dir('custom', $root_dirs[1]);
			}
			$this->php_parser = $php_parser;
		}
		return $this;
	}

	/**
	 * @throws \Exception
	 */
	public function compile_scss() {
		/**
		 * @var ScssParser $scss_parser
		 */
		$scss_parser = $this->scss_parser;
		$scss_parser->prepare_main_for_sass_compilation()
					->compile();

		return $this;
	}

	/**
	 * @param array $root_dirs
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function genere_scss_doc(...$root_dirs) {
		if($this->scss_doc_enabled) {
			/**
			 * @var ScssParser $scss_parser
			 * @var PhpParser $php_parser
			 */
			if(empty($root_dirs)) {
				$this->get_scss_parser(ROOT_PATH);
			}
			elseif(count($root_dirs) === 1) {
				$this->get_scss_parser($root_dirs[0]);
			}
			else {
				$this->get_scss_parser(ROOT_PATH.'scss', ROOT_PATH.'scss2');
			}
			if ($scss_parser = $this->scss_parser) {
				$scss_parser->genere_scss_file()
							->genere_scss_doc_array()
							->set_php_file('index.php');
				list($path, $html) = $scss_parser->genere_doc_file();
				if(!$this->php_doc_enabled) {
					$html = str_replace('[php_nav_menu]', '', $html);
					$html = str_replace('[php_doc_page]', '', $html);
					file_put_contents($path, $html);
					self::create_php_page(ROOT_PATH.$this->doc_dir.$this->doc_file, 'CssDoc');
				}
				else {
					if($php_parser = $this->php_parser
									 && (strpos('[php_nav_menu]', $html) !== false || strpos('[php_doc_page]', $html) !== false)) {
						$php_parser->genere_php_doc_array()
								   ->set_php_file('index.php')
								   ->genere_doc_file($html);
					}
					self::create_php_page(ROOT_PATH.$this->doc_dir.$this->doc_file, ['CssDoc', 'PhpDoc']);
				}
			}
		}
		return $this;
	}

	/**
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function genere_php_doc() {
		if($this->php_doc_enabled) {
			/**
			 * @var PhpParser $php_parser
			 * @var ScssParser $scss_parser
			 */
			$this->get_php_parser();
			if ($php_parser = $this->php_parser) {
				$php_parser->genere_php_doc_array()
						   ->set_php_file('index.php');
				list($path, $html) = $php_parser->genere_doc_file();
				if(!$this->scss_doc_enabled) {
					$html = str_replace('[css_nav_menu]', '', $html);
					$html = str_replace('[css_doc_page]', '', $html);
					file_put_contents($path, $html);
					self::create_php_page(ROOT_PATH.$this->doc_dir.$this->php_doc_file, 'PhpDoc');
				}
				else {
					if($scss_parser = $this->scss_parser
									  && (strpos('[css_nav_menu]', $html) !== false || strpos('[css_doc_page]', $html) !== false)) {
						$scss_parser->genere_scss_file()
									->genere_scss_doc_array()
									->set_php_file('index.php')
									->genere_doc_file($html);
					}
					self::create_php_page(ROOT_PATH.$this->doc_dir.$this->php_doc_file, ['CssDoc', 'PhpDoc']);
				}
			}
		}
		return $this;
	}

	public function active_scss_doc($active = true) {
		$this->scss_doc_enabled = $active;
		return $this;
	}
	public function active_php_doc($active = true) {
		$this->php_doc_enabled = $active;
		return $this;
	}
}