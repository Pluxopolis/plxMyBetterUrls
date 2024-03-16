<?php
	/**
		* Plugin plxMyBetterUrls
		*
		* @author	Stephane F
	**/
	class plxMyBetterUrls extends plxPlugin {
		
		/**
			* Constructeur de la classe
			*
			* @param	default_lang	langue par défaut
			* @return	stdio
			* @author	Stephane F
		**/
		public function __construct($default_lang) {
			
			# appel du constructeur de la classe plxPlugin (obligatoire)
			parent::__construct($default_lang);
		
		//include_once(PLX_ROOT.'core/lang/'.$this->default_lang.'/core.php');	
			
			# droits pour accéder à la page config.php du plugin
			$this->setConfigProfil(PROFIL_ADMIN);
			
			# initialisation des variables de la classe
			$this->article = $this->getParam('format_article')!='' ? $this->getParam('format_article').'/' : '';
			$this->category = $this->getParam('format_category')!='' ? $this->getParam('format_category').'/' : '';
			$this->static = $this->getParam('format_static')!='' ? $this->getParam('format_static').'/' : '';
			$this->author = $this->getParam('format_author')!='' ? $this->getParam('format_author').'/' : '';
			$this->tag = $this->getParam('format_tag')!='' ? $this->getParam('format_tag').'/' : '';
			
			# configuration extensions des pages 		
			$this->ext_art='';
			if($this->getParam('ext_url_article') =='checked') $this->ext_art=$this->getParam('ext_url');
			$this->ext_static='';
			if($this->getParam('ext_url_static') =='checked') $this->ext_static=$this->getParam('ext_url');
			$this->ext_category='/';
			if($this->getParam('ext_url_category') =='checked') $this->ext_category=$this->getParam('ext_url');		
			$this->ext_tag='/';
			if($this->getParam('ext_url_tag') =='checked') $this->ext_tag=$this->getParam('ext_url');
			$this->ext_auteur='';
			if($this->getParam('ext_url_author') =='checked') $this->ext_auteur=$this->getParam('ext_url');	
		
			# déclaration des hooks uniquement si l'urlrewriting est actif
			$xmlConfig = simplexml_load_file(PLX_ROOT . PLX_CONFIG_PATH . 'parametres.xml') or die("Error: L_MEDIAS_NO_FILE");;
			$configrewrite = $xmlConfig->xpath('//document/parametre[@name="urlrewriting"]');
			if($configrewrite[0] == 1) {	
				$this->addHook('plxMotorPreChauffageBegin', 'plxMotorConstruct');
				$this->addHook('plxMotorDemarrageNewCommentaire', 'plxMotorDemarrageNewCommentaire');
				$this->addHook('plxMotorConstructLoadPlugins', 'Redirect301');
				$this->addHook('IndexEnd', 'RewriteUrls');
				$this->addHook('FeedEnd', 'RewriteUrls');
				$this->addHook('SitemapEnd', 'RewriteUrls');
				$this->addHook('plxFeedPreChauffageBegin', 'plxFeedPreChauffageBegin');
			}else {
				loadLang('../lang/'.$default_lang.'/admin.php');
				if(defined('PLX_ADMIN'))	$this->aInfos['title'] = L_PLUGINS_REQUIREMENTS.': '.L_CONFIG_ADVANCED_URL_REWRITE.'
				'.$this->aInfos['title'];
				$this->addHook('AdminSettingsPluginsTop','AdminSettingsPluginsTop');
			}
		}
		
		/** alerte visuelle **/
		public function    AdminSettingsPluginsTop(){
			echo '
			<style> tr.top:has( td.right a[href="parametres_plugin.php?p=plxMyBetterUrls"]) {
				background:#00000011;
				color:gray
			}
			table tr.top:has( td.right a[href="parametres_plugin.php?p=plxMyBetterUrls"]) strong.title {
				white-space:pre;
				display:block;
				background:linear-gradient(to bottom, white 0 1.6em, transparent 1.2em) 
				transparent!important;
				max-width:max-content
			}
			tr.top:has( td.right a[href="parametres_plugin.php?p=plxMyBetterUrls"]) .title:first-line {
				color:red;
			}
			a[href="parametres_plugin.php?p=plxMyBetterUrls"] {
				pointer-events:none;text-decoration:line-through
			}
			</style>';			
		}
		
		/**
			* Méthode qui fait une redirection 301 si accès à PluXml à partir des anciennes url
			*
			* @author	Stephane F
		**/
		public function Redirect301() {

			echo '<?php
			if(!defined("PLX_ADMIN") AND substr(str_replace($_SERVER["QUERY_STRING"], "", $_SERVER["REQUEST_URI"]),-1)=="?") {
				# redirection si lien http://server.com/?contenu vers http://server.com/contenu
				header("Status: 301 Moved Permanently", false, 301);
				header("Location: ".$this->urlRewrite($_SERVER["QUERY_STRING"]));
				exit();
			}
			if(preg_match("/^(article|static|categorie)[0-9]+\/([a-z0-9-]+)(\/page[0-9]+)?/", $this->get, $capture)) {
				if($capture[1]!="'.$this->getParam('format_article').'") {
					$page=isset($capture[3])?$capture[3]:"";
					header("Status: 301 Moved Permanently", false, 301);
					header("Location: ".$this->urlRewrite($capture[2]."'.$this->ext_art.'".$page));
					exit();
				}
			}
			if(preg_match("/index.php\?(tag|archives)\/(.*)/", $_SERVER["REQUEST_URI"], $capture)) {
				header("Status: 301 Moved Permanently", false, 301);
				header("Location: ".$this->urlRewrite($capture[1]."/".$capture[2]));
				exit();
			}
			?>';
			
		}
		
		/**
			* Méthode qui rédirige vers la bonne url après soumission d'un commentaire
			*
			* @author	Stephane F
		**/
		public function plxMotorDemarrageNewCommentaire() {
			echo '<?php
			$url = $this->urlRewrite("?'.$this->lang.$this->article.'".$this->plxRecord_arts->f("url")."'.$this->ext_art.'");
		?>';
		}
		
		/**
		* Méthode qui recrée l'url de l'article, page statique ou catégorie au format natif de PluXml
		*
		* @author	Stephane F
		**/
		public function plxMotorConstruct() {	
		# récupération de la langue si plugin plxMyMultilingue présent
		$this->lang="";
		if(defined('PLX_MYMULTILINGUE')) {
			$lang = plxMyMultiLingue::_Lang();
			if(!empty($lang)) {
				if(isset($_SESSION['default_lang']) AND $_SESSION['default_lang']!=$lang) {
					$this->lang = $lang.'/';
				}
			}
		}
		
		# configuration extensions des pages 

		echo '<?php
		if(empty($this->get))
		return;
		

		# récupération url
		$get = $_SERVER["QUERY_STRING"];
		
		# récupération de la pagination si présente
		$page="";
		if(preg_match("/(page[0-9]+)$/", $this->get, $capture)) {
			$page = "/".$capture[0];
		}
		
		# suppression de la page dans url
		$get = str_replace($page, "", $get);
		
		# pages statiques
		foreach($this->aStats as $numstat => $stat) {
			$link = "'.$this->lang.$this->static.'".$stat["url"]."'.$this->ext_static.'";
			if($get==$stat["url"]) {
				$get = "'.$this->lang.$this->static.'".$get;
			}
			if($link==$get) {
				$this->get = "'.$this->lang.L_STATIC_URL.'".intval($numstat)."/".$stat["url"];
				return;
			}
		}
		
		# categories
		foreach($this->aCats as $numcat => $cat) {
			$link = "'.$this->lang.$this->category.'".$cat["url"]."'.$this->ext_category.'";
			if($link==$get) {
				$this->get = "'.$this->lang.L_CATEGORY_URL.'".intval($numcat)."/".$cat["url"].$page;
				return;
			}
		}
		
		# auteurs
		foreach($this->aUsers as $numUser => $user) {
			$link = "'.$this->lang.$this->author.'".$user["name"]."'.$this->ext_auteur.'";
			if($link==$get) {
				$this->get = "'.$this->lang.L_USER_URL.'".intval($numUser)."/".$user["name"].$page;
				return;
			}
		}		
		# tags
		$tagList=array();
		foreach($this->aTags as $numArt => $tags) {	
			if($tags["active"]=="1"){
				$activeTags = explode(",", $tags["tags"]);
				foreach($activeTags as $key => $val) {
					$tagList[trim($val)]=trim($val);
				}		
			}
		}
		foreach($tagList as  $tag) {
			$link = "'.$this->lang.$this->tag.'".$tag."'.$this->ext_tag.'";
			if($link==$get) {
				$this->get = "'.$this->lang.L_TAG_URL.'/".$tag.$page;
				return;
			}
		}
		

		# articles
		foreach($this->plxGlob_arts->aFiles as $numart => $filename) {
			if(preg_match("/^[0-9]{4}.([0-9,|home|draft]*).[0-9]{3}.[0-9]{12}.([a-z0-9-]+).xml$/", $filename,$capture)) {
				$link = "'.$this->lang.$this->article.'".$capture[2]."'.$this->ext_art.'";
				if($link==$get) {
					$this->get = "'.$this->lang.L_ARTICLE_URL.'".intval($numart)."/".$capture[2];
					return;
				}
			}
		}

		?>';
		}
		
		/**
		* Méthode qui nettoie les urls des articles, pages statiques et catégories
		*
		* @author	Stephane F
		**/
		public function RewriteUrls() {

		echo '<?php

		$output = preg_replace("/'.L_ARTICLE_URL.'[0-9]+\/([a-z0-9-]+)/", "'.$this->article.'$1'.$this->ext_art.'", $output);
		$output = preg_replace("/'.L_CATEGORY_URL.'[0-9]+\/([a-z0-9-]+)/", "'.$this->category.'$1'.$this->ext_category.'", $output);
		$output = preg_replace("/'.L_STATIC_URL.'[0-9]+\/([a-z0-9-]+)/", "'.$this->static.'$1'.$this->ext_static.'", $output);
		$output = preg_replace("/'.L_USER_URL.'[0-9]+\/([a-z0-9-]+)/", "'.$this->author.'$1'.$this->ext_auteur.'", $output);
		$output = preg_replace("/'.L_TAG_URL.'+\/([a-z0-9-]+)/", "'.$this->tag.'$1'.$this->ext_tag.'", $output);
		?>';
		
		}
		
		public function plxFeedPreChauffageBegin() {
		# flux rss des articles d'une categorie
		echo '<?php
		if(preg_match("#^rss/'.$this->category.'([a-z0-9-]+)#", $this->get, $capture)) {
			foreach($this->aCats as $numcat => $cat) {
				if($cat["url"]==$capture[1]) {
					$this->get = "rss/categorie".intval($numcat);
				}
			}
		}
		?>';
		}
	}						