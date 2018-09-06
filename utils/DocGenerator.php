<?php

namespace project\utils;


use project\extended\classes\util;

class DocGenerator extends util {
	private $root_dir = null;
	private static $static_root_dir;
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

	public function get_scss_parser(...$root_dirs) {
		/**
		 * @var ScssParser $scss_parser
		 */
		$scss_parser = $this->get_util('ScssParser', $root_dirs[0]);
		if (count($root_dirs) === 2) {
			$scss_parser->set_root_dir('core', $root_dirs[0]);
			$scss_parser->set_root_dir('custom', $root_dirs[1]);
		}
		return $scss_parser;
	}

	/**
	 * @throws \Exception
	 * @return ScssParser
	 */
	public function css_doc() {
		/**
		 * @var ScssParser $scss_parser
		 */
		$scss_parser = $this->get_scss_parser($this->root_dir);
		$scss_parser->parse()->get_scss_array();
		$scss_parser->genere_scss_file()->genere_scss_doc_array()->genere_doc_file();
		return $scss_parser;
	}

	public function php_doc() {}
}