<?php

namespace project\utils;


use project\extended\classes\util;

class ScssParser extends util {
	private $root_dir = null, $base_dir = '/scss/', $scss_suffix = 'scss', $css_file = 'main', $css_suffix = 'css', $last_update_file = 'last_update.txt',
			$html_doc_dir = 'layouts/CssDoc', $html_doc_file = 'index.view.html', $php_doc_file = 'doc.php';
	private $scss_array = [], $docs = [];
	private $scss_reg_exp, $css_reg_exp;

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
		foreach (new \DirectoryIterator($this->base_dir) as $fileInfo) {
			if($fileInfo->isDir() && strstr($fileInfo->getBasename(), '_')) {
				foreach (new \DirectoryIterator($this->base_dir.$fileInfo->getBasename()) as $_fileInfo) {
					if($_fileInfo->isDir()
					   && strstr($_fileInfo->getBasename(), '_')) {
						$path = $this->base_dir.$fileInfo->getBasename().'/'.$_fileInfo->getBasename();
						foreach (new \DirectoryIterator($path) as $__fileInfo) {
							if($__fileInfo->isFile()
							   && (strstr($__fileInfo->getFilename(), '.'.$this->scss_suffix)
										||
									strstr($__fileInfo->getFilename(), '.'.$this->css_suffix))
							   && $__fileInfo->getFilename() !== 'main.'.$this->scss_suffix
							   && $__fileInfo->getFilename() !== 'main.'.$this->css_suffix
							   && $filepath = $__fileInfo->getPathname()) {
								// Fichiers à l'étage 2
								$basename = basename($filepath);
								$directory = str_replace($basename, '', $filepath);
								if (preg_match($this->scss_reg_exp, $basename, $matches) || preg_match($this->css_reg_exp, $basename, $matches)) {
									$directory_name  		= basename(dirname($filepath));
									$sub_directory_name 	= basename(dirname(str_replace($directory_name.'/'.$basename, '', $directory)));
									$directory_order		= (int)strtok($sub_directory_name, '_');
									$directory_ident 		= strtok('');

									$order       			= (int)$matches[1];
									$this->scss_array[$directory_ident][$directory_name][$basename]
														  	= $filepath;
									$files_order[$directory_ident][$directory_name][$basename]
														  	= $order;
									$directories_order[$directory_ident]
														  	= $directory_order;
								}
							}
						}
					}
					elseif ($_fileInfo->isFile()
							&& (strstr($_fileInfo->getFilename(), '.'.$this->scss_suffix)
								||
								strstr($_fileInfo->getFilename(), '.'.$this->css_suffix))
							&& $_fileInfo->getFilename() !== 'main.'.$this->scss_suffix
							&& $_fileInfo->getFilename() !== 'main.'.$this->css_suffix
							&& $filepath = $_fileInfo->getPathname()) {
						// Fichiers à l'étage 1

						$basename = basename($filepath);
						if (preg_match($this->scss_reg_exp, $basename, $matches) || preg_match($this->css_reg_exp, $basename, $matches)) {
							$directory_name  	= basename(dirname($filepath));
							$directory_order 	= (int)strtok($directory_name, '_');
							$directory_ident 	= strtok('');

							$order       		= (int)$matches[1];
							$this->scss_array[$directory_ident][$basename]
												= $filepath;
							$files_order[$directory_ident][$directory_name][$basename]
												= $order;
							$directories_order[$directory_ident]
												= $directory_order;
						}
					}
				}
			}
			elseif ($fileInfo->isFile()
					&& (strstr($fileInfo->getFilename(), '.'.$this->scss_suffix)
						||
						strstr($fileInfo->getFilename(), '.'.$this->css_suffix))
					&& $fileInfo->getFilename() !== 'main.'.$this->scss_suffix
					&& $fileInfo->getFilename() !== 'main.'.$this->css_suffix
					&& $filepath = $fileInfo->getPathname()) {
				// fichiers à la racine
				$basename = basename($filepath);
				if (preg_match($this->scss_reg_exp, $basename, $matches) || preg_match($this->css_reg_exp, $basename, $matches)) {
					$directory_name  	= basename(dirname($filepath));
					$directory_order 	= (int)strtok($directory_name, '_');
					$directory_ident 	= strtok('');

					$order       		= (int)$matches[1];
					$this->scss_array[$directory_ident][$basename]
									  	= $filepath;
					$files_order[$directory_ident][$basename]
									  	= $order;
					$directories_order[$directory_ident]
									  	= $directory_order;
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
		$css_file_content = str_replace(['../', '@import "node_modules'], ['', '@import "../node_modules'], $css_file_content);
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

		$html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Documentation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
    <!-- Font Awesome CSS-->
    <link rel="stylesheet" href="node_modules/font-awesome/css/font-awesome.min.css">
    <!-- Fontastic Custom icon font-->
    <link rel="stylesheet" href="css/fontastic.css">
    <!-- Google fonts - Roboto -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700">
    <!-- jQuery Circle-->
    <link rel="stylesheet" href="css/grasp_mobile_progress_circle-1.0.0.min.css">
    <!-- Custom Scrollbar-->
    <link rel="stylesheet" href="node_modules/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css">
    <!-- theme stylesheet-->
    <link rel="stylesheet" href="css/style.default.css" id="theme-stylesheet">

    <link rel="stylesheet" href="scss/hightlight/styles/default.css">

    <link rel="stylesheet" href="scss/main.css">
    <!-- Custom stylesheet - for your changes-->
    <link rel="stylesheet" href="css/custom.css">
    <!-- Favicon-->
    <link rel="shortcut icon" href="img/css3.png">
    <!-- Tweaks for older IEs--><!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="node_modules/jquery/dist/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
    <script src="node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="scss/hightlight/highlight.pack.js"></script>
    <script>hljs.initHighlightingOnLoad();</script>
    <script>
        $(document).ready(function() {
            $(".js-scrollTo").on("click", function() { // Au clic sur un élément
                let page = $(this).attr("href"); // Page cible
                let speed = 750; // Durée de l\'animation (en ms)
                $("html, body").animate( { scrollTop: $(page).offset().top }, speed ); // Go
                return false;
            });
        });
    </script>
</head>
<body>
<!-- Side Navbar -->
<nav class="side-navbar">
    <div class="side-navbar-wrapper">
        <!-- Sidebar Header    -->
        <div class="sidenav-header d-flex align-items-center justify-content-center">
            <!-- User Info-->
            <div class="sidenav-header-inner text-center">
                <div class="img-fluid rounded-circle">
                    <span style="border: 1px solid white; padding: 15px; font-size: 20px; -webkit-border-radius: 35px;-moz-border-radius: 35px;border-radius: 35px;">
                        NC
                    </span>
                </div>
                <h2 class="h5">Nicolas Choquet</h2>
                <span>Web Developer</span>
            </div>
            <!-- Small Brand information, appears on minimized sidebar-->
            <div class="sidenav-header-logo">
                <a href="home.php" class="brand-small text-center">
                    <strong>N</strong>
                    <strong class="text-primary">C</strong>
                </a>
            </div>
        </div>
        <!-- Sidebar Navigation Menus-->
        <div class="main-menu">
            <h5 class="sidenav-heading">Doc.</h5>
            <ul id="side-main-menu" class="side-menu list-unstyled">
                <li>
                    <a href="#doc-css" aria-expanded="false" data-toggle="collapse">
                        <i>
                            <img src="img/css3.png" style="height: 25px; width: 25px;">
                        </i>
                        CSS
                        <div class="badge badge-info">SASS</div>
                    </a>
                    [nav_menu]
                </li>
                <!--<li>
                    <a href="#doc-php" aria-expanded="false" data-toggle="collapse">
                        <i>
                            <img src="img/php7.png" style="height: 25px; width: 18px; margin-left: 5px;">
                        </i>
                        PHP
                        <div class="badge badge-info">MVC</div>
                    </a>
                    <ul id="doc-php" class="collapse list-unstyled ">
                        <li>
                            <a href="#">
                                <i class="fa fa-folder"></i> Strates
                            </a>
                        </li>
                    </ul>
                </li>-->
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
                        <a href="home.php" class="navbar-brand">
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
        <div class="container-fluid">';
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

			$html .= '
<div class="row">
	<div class="col-12 card" id="'.$id.'">
         <div class="card-header">
              <i class="fa fa-file font-italic" style="font-size: 15px;"> Fichier source: '.$doc['source'].'</i>
              <h2 class="card-title">'.$doc['title'].'</h2>
         </div>
         <div class="card-body">
               <p>
                   '.$doc['description'].'
               </p>
               <div>
                   <b>EXEMPLES</b>
                   <br/>
                   <div class="exemples-code" style="margin-bottom: 50px; margin-top: 15px;;">
                       '.$doc['Markup:'].'
                   </div>
               </div>
               <div>
                    <b>CODE SOURCE</b>
                    <br/>
                    <div class="source-code">
                         <pre><code class="html">'.htmlentities($doc['Markup:']).'</code></pre>
                    </div>
               </div>
         </div>
    </div>
</div>
';
		}

    $html .= '        </div>
    </section>
    <footer class="main-footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <p>base_object &copy; 2017-2019</p>
                    <p>Dernières modification: [last_update]</p>
                </div>
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
<script src="js/grasp_mobile_progress_circle-1.0.0.min.js"></script>
<script src="node_modules/jquery.cookie/jquery.cookie.js"></script>
<script src="node_modules/jquery-validation/jquery.validate.min.js"></script>
<script src="node_modules/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js"></script>
<!-- Main File-->
<script src="js/front.js"></script>
</body>
</html>';

		$nav = '<ul id="doc-css" class="collapse list-unstyled ">';
		foreach ($stylesgide as $categorie => $sub_cat) {
			$nav .= '	<li>'."\n";
			$nav .= '<a href="#'.strtolower($categorie).'" aria-expanded="false" data-toggle="collapse">
						<i class="fa fa-folder"></i> '.$categorie.'
					 </a>
					 <ul id="'.strtolower($categorie).'" class="collapse list-unstyled">'."\n";
			foreach ($sub_cat as $class => $sub_class) {
				$nav .= '		<li>'."\n";
				if($this->is_array($sub_class)) {
					$nav .= '	<a href="#'.strtolower($categorie).'-'.$class.'" aria-expanded="false" data-toggle="collapse">
		<i class="fa fa-folder"></i> '.$class.'
	</a>
					 <ul id="'.strtolower($categorie).'-'.$class.'" class="collapse list-unstyled">'."\n";
					foreach ($sub_class as $sub_sub_class => $id_div) {
						$nav .= '			<li>
				<a class="js-scrollTo" href="#'.$id_div.'"><i class="fa fa-css3"></i> '.$sub_sub_class.'</a>
			</li>'."\n";
					}
					$nav .= '			</ul>'."\n";
				}
				else {
					$nav .= '<a class="js-scrollTo" href="#'.$sub_class.'"><i class="fa fa-css3"></i> '.$class.'</a>'."\n";
				}
				$nav .= '		</li>'."\n";
			}
			$nav .= '		</ul>
						</li>'."\n";
		}
        $nav .= '</ul>
		';

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

	public function prepare_main_for_sass_compilation() {
		$sass = file_get_contents($this->css_file);
		preg_replace_callback('`(\/\/\ SOURCE\ [a-zA-Z0-9\_\-\.\/]+\n)(\/\*[^*]+\*\/)`', function ($matches) use (&$sass) {
			$sass = str_replace($matches[1].$matches[2], '', $sass);
		}, $sass);
		file_put_contents($this->base_dir.'/ready-to-compile.'.$this->scss_suffix, $sass);
		return $this;
	}

	public function compile() {
		$main_file = str_replace('.'.$this->scss_suffix, '.'.$this->css_suffix, $this->css_file);
		$output = null;
		exec('node-sass '.$this->base_dir.'/ready-to-compile.scss '.$main_file, $output);
		unlink($this->base_dir.'/ready-to-compile.scss');
		unlink($this->css_file);
		$main_file_content = file_get_contents($main_file);
		$main_file_content = str_replace("\n", '', $main_file_content);
		file_put_contents($main_file, $main_file_content);
		return $output;
	}
}