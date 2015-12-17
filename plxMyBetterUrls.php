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

		# déclaration des hooks
		$this->addHook('plxMotorConstruct', 'plxMotorConstruct');
		$this->addHook('plxMotorConstructLoadPlugins', 'Redirect301');
		$this->addHook('IndexEnd', 'RewriteUrls');
		$this->addHook('FeedEnd', 'RewriteUrls');
		$this->addHook('SitemapEnd', 'RewriteUrls');
		$this->addHook('AdminPrepend', 'AdminPrepend');
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
			$page=isset($capture[3])?$capture[3]:"";
			header("Status: 301 Moved Permanently", false, 301);
			header("Location: ".$this->urlRewrite($capture[2]."'.$this->getParam('ext_url').'".$page));
			exit();
		}
		if(preg_match("/index.php\?(tag|archives)\/(.*)/", $_SERVER["REQUEST_URI"], $capture)) {
			header("Status: 301 Moved Permanently", false, 301);
			header("Location: ".$this->urlRewrite($capture[1]."/".$capture[2]));
			exit();
		}
		?>';

	}

	/**
	 * Méthode qui recrée l'url de l'article, page statique ou catégorie au format natif de PluXml
	 *
	 * @author	Stephane F
	 **/
	public function plxMotorConstruct() {

		echo '<?php
		if(empty($this->get))
			return;

		# récupération url
		$url = explode("/", $_SERVER["QUERY_STRING"]);

		# pour compatibilité avec le plugin plxMyMultLingue
		if(!defined("PLX_MYMULTILINGUE"))
			$get = $url[0];
		else {
			$array =  explode(",", PLX_MYMULTILINGUE);
			$get = in_array($url[0], $array) ? $url[1] : $url[0];
		}

		# récupération pagination si présente
		$page="";
		if(preg_match("/(page[0-9]+)/", $this->get, $capture)) {
			$page = "/".$capture[0];
		}

		# pages statiques
		foreach($this->aStats as $numstat => $stat) {
			if($stat["url"]."'.$this->getParam('ext_url').'"==$get) {
				$this->get = "static".intval($numstat)."/".$stat["url"];
				return;
			}
		}

		# categories
		foreach($this->aCats as $numcat => $cat) {
			if($cat["url"]."'.$this->getParam('ext_url').'"==$get) {
				$this->get = "categorie".intval($numcat)."/".$cat["url"].$page;
				return;
			}
		}

		# articles
		foreach($this->plxGlob_arts->aFiles as $numart => $filename) {
			if(preg_match("/^[0-9]{4}.([0-9,|home|draft]*).[0-9]{3}.[0-9]{12}.([a-z0-9-]+).xml$/", $filename,$capture)) {
				if($capture[2]."'.$this->getParam('ext_url').'"==$get) {
					$this->get = "article".intval($numart)."/".$get;
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
			$output = preg_replace("/(article|static|categorie)[0-9]+\/([a-z0-9-]+)/", "$2'.$this->getParam('ext_url').'", $output);
		 ?>';
	}

}
?>