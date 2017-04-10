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

		# droits pour accéder à la page config.php du plugin
		$this->setConfigProfil(PROFIL_ADMIN);

		# initialisation des variables de la classe
		$this->article = $this->getParam('format_article')!='' ? $this->getParam('format_article').'/' : '';
		$this->category = $this->getParam('format_category')!='' ? $this->getParam('format_category').'/' : '';
		$this->static = $this->getParam('format_static')!='' ? $this->getParam('format_static').'/' : '';

		# déclaration des hooks
		$this->addHook('plxMotorConstruct', 'plxMotorConstruct');
		$this->addHook('plxMotorDemarrageNewCommentaire', 'plxMotorDemarrageNewCommentaire');
		$this->addHook('plxMotorConstructLoadPlugins', 'Redirect301');
		$this->addHook('IndexEnd', 'RewriteUrls');
		$this->addHook('FeedEnd', 'RewriteUrls');
		$this->addHook('SitemapEnd', 'RewriteUrls');
		$this->addHook('plxFeedPreChauffageBegin', 'plxFeedPreChauffageBegin');
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
				header("Location: ".$this->urlRewrite($capture[2]."'.$this->getParam('ext_url').'".$page));
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
			$url = $this->urlRewrite("?'.$this->lang.$this->article.'".$this->plxRecord_arts->f("url")."'.$this->getParam('ext_url').'");
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
			$link = "'.$this->lang.$this->static.'".$stat["url"]."'.$this->getParam('ext_url').'";
			if($get==$stat["url"]) {
				$get = "'.$this->lang.$this->static.'".$get;
			}
			if($link==$get) {
				$this->get = "'.$this->lang.'static".intval($numstat)."/".$stat["url"];
				return;
			}
		}

		# categories
		foreach($this->aCats as $numcat => $cat) {
			$link = "'.$this->lang.$this->category.'".$cat["url"]."'.$this->getParam('ext_url').'";
			if($link==$get) {
				$this->get = "'.$this->lang.'categorie".intval($numcat)."/".$cat["url"].$page;
				return;
			}
		}

		# articles
		foreach($this->plxGlob_arts->aFiles as $numart => $filename) {
			if(preg_match("/^[0-9]{4}.([0-9,|home|draft]*).[0-9]{3}.[0-9]{12}.([a-z0-9-]+).xml$/", $filename,$capture)) {
				$link = "'.$this->lang.$this->article.'".$capture[2]."'.$this->getParam('ext_url').'";
				if($link==$get) {
					$this->get = "'.$this->lang.'article".intval($numart)."/".$capture[2];
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
			$output = preg_replace("/article[0-9]+\/([a-z0-9-]+)/", "'.$this->article.'$1'.$this->getParam('ext_url').'", $output);
			$output = preg_replace("/categorie[0-9]+\/([a-z0-9-]+)/", "'.$this->category.'$1'.$this->getParam('ext_url').'", $output);
			$output = preg_replace("/static[0-9]+\/([a-z0-9-]+)/", "'.$this->static.'$1'.$this->getParam('ext_url').'", $output);
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
?>