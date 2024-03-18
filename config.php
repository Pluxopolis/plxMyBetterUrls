<?php if(!defined('PLX_ROOT')) exit; ?>
<?php
	
	# Control du token du formulaire
	plxToken::validateFormToken($_POST);
	
	if(!empty($_POST)) {
		if($_POST['ext_url'][0]!='.' AND $_POST['ext_url'][0]!='')
		plxMsg::Error($plxPlugin->getLang('L_BAD_URL_EXTENSION'));
		else {
			if(isset($_POST['ext_url_article'])) $plxPlugin->setParam('ext_url_article', 'checked', 'string');
			else $plxPlugin->setParam('ext_url_article', '', 'string');
			if(isset($_POST['ext_url_author'])) $plxPlugin->setParam('ext_url_author', 'checked', 'string');
			else $plxPlugin->setParam('ext_url_author', '', 'string');
			if(isset($_POST['ext_url_category'])) $plxPlugin->setParam('ext_url_category', 'checked', 'string');
			else $plxPlugin->setParam('ext_url_category', '', 'string');
			if(isset($_POST['ext_url_static'])) $plxPlugin->setParam('ext_url_static', 'checked', 'string');
			else $plxPlugin->setParam('ext_url_static', '', 'string');
			if(isset($_POST['ext_url_tag'])) $plxPlugin->setParam('ext_url_tag', 'checked', 'string');
			else $plxPlugin->setParam('ext_url_tag', '', 'string');
			$ext = ($_POST['ext_url']!=''? '.'.plxUtils::title2url($_POST['ext_url']):'');
			$plxPlugin->setParam('ext_url', $ext, 'string');
			$plxPlugin->setParam('format_article', plxUtils::title2url($_POST['format_article']), 'string');
			$plxPlugin->setParam('format_category', plxUtils::title2url($_POST['format_category']), 'string');
			$plxPlugin->setParam('format_static', plxUtils::title2url($_POST['format_static']), 'string');
			$plxPlugin->setParam('format_author', plxUtils::title2url($_POST['format_author']), 'string');
			$plxPlugin->setParam('format_tag', plxUtils::title2url($_POST['format_tag']), 'string');
			$plxPlugin->saveParams();
		}
		header('Location: parametres_plugin.php?p=plxMyBetterUrls');
		exit;
	}
	
	$format_article = $plxPlugin->getParam('format_article');
	$format_category = $plxPlugin->getParam('format_category');
	$format_static = $plxPlugin->getParam('format_static');
	$format_author = $plxPlugin->getParam('format_author');
	$format_tag = $plxPlugin->getParam('format_tag');
?>
<form id="form_config_plugin" action="parametres_plugin.php?p=plxMyBetterUrls" method="post" class="grid-2-col">
	<fieldset>
		<legend><?= L_OPTIONS . ': '.$plxPlugin->getLang('L_URLS_EXTENSION') ?>&nbsp;:</legend>
		<input onkeyup="upd_spans(this.value)" type="text" id="id_ext_url" name="ext_url" size="10" maxlength="11" value="<?php echo $plxPlugin->getParam('ext_url') ?>" />&nbsp;ex: <strong>.htm</strong>, .html, .php
	</fieldset>
	<fieldset class="grid-2-col">
		<legend><?php $plxPlugin->lang('L_URLS_FORMAT') ?></legend>
		<p class="alert green"><?php $plxPlugin->lang('L_URLS_FORMAT_DESC') ?></p>		

		<label for="id_format_article"><?= L_PAGE_URL .' ' .L_ARTICLE_URL ?> :</label>
		<label><input type="checkbox" name="ext_url_article" id="ext_url_article" <?php echo $plxPlugin->getParam('ext_url_article') ?>  ><?php $plxPlugin->lang('L_ADD') ?> <?php $plxPlugin->lang('L_URLS_EXTENSION') ?></label>
		<p><?php echo $plxAdmin->aConf['racine'] ?><?php plxUtils::printInput('format_article',$format_article,'text','5-255') ?>/mon-article<span class="ext_url_article"><?php if($plxPlugin->getParam('ext_url_article') =='checked')echo $plxPlugin->getParam('ext_url') ?></span></p>

		<label for="id_format_article"><?= L_PAGE_URL .' ' .L_CATEGORY_URL ?> :</label>
		<label><input type="checkbox" name="ext_url_category" id="ext_url_category" <?php echo $plxPlugin->getParam('ext_url_category') ?>  ><?php $plxPlugin->lang('L_ADD') ?> <?php $plxPlugin->lang('L_URLS_EXTENSION') ?></label>
		<p><?php echo $plxAdmin->aConf['racine'] ?><?php plxUtils::printInput('format_category',$format_category,'text','5-255') ?>/ma-categorie<span class="ext_url_category"><?php if($plxPlugin->getParam('ext_url_category') =='checked')echo $plxPlugin->getParam('ext_url') ?></span></p>

		<label for="id_format_static"><?= L_PAGE_URL .' ' .L_STATIC_URL ?> :</label>
		<label><input type="checkbox" name="ext_url_static" id="ext_url_static" <?php echo $plxPlugin->getParam('ext_url_static') ?>  ><?php $plxPlugin->lang('L_ADD') ?> <?php $plxPlugin->lang('L_URLS_EXTENSION') ?></label>
		<p><?php echo $plxAdmin->aConf['racine'] ?><?php plxUtils::printInput('format_static',$format_static,'text','5-255') ?>/ma-page-statique<span class="ext_url_static"><?php if($plxPlugin->getParam('ext_url_static') =='checked')echo $plxPlugin->getParam('ext_url') ?></span></p>

		<label for="id_format_author"><?= L_PAGE_URL .' ' .L_USER_URL ?> :</label>
		<label><input type="checkbox" name="ext_url_author" id="ext_url_author" <?php echo $plxPlugin->getParam('ext_url_author') ?>  ><?php $plxPlugin->lang('L_ADD') ?> <?php $plxPlugin->lang('L_URLS_EXTENSION') ?></label>
		<p><?php echo $plxAdmin->aConf['racine'] ?><?php plxUtils::printInput('format_author',$format_author,'text','5-255') ?>/auteur<span class="ext_url_author"><?php if($plxPlugin->getParam('ext_url_author') =='checked') echo $plxPlugin->getParam('ext_url') ?></span></p>

		<label for="id_format_tag"><?= L_PAGE_URL .' ' .L_TAG_URL ?> :</label>
		<label><input type="checkbox" name="ext_url_tag" id="ext_url_tag" <?php echo $plxPlugin->getParam('ext_url_tag') ?>  ><?php $plxPlugin->lang('L_ADD') ?> <?php $plxPlugin->lang('L_URLS_EXTENSION') ?></label>
		<p><?php echo $plxAdmin->aConf['racine'] ?><?php plxUtils::printInput('format_tag',$format_tag,'text','5-255') ?>/mot-cle<span class="ext_url_tag"><?php if($plxPlugin->getParam('ext_url_tag') =='checked')echo $plxPlugin->getParam('ext_url') ?></span></p>

		<p class="in-action-bar">
			<?php echo plxToken::getTokenPostMethod() ?>
			<input type="submit" name="submit" value="<?php $plxPlugin->lang('L_SAVE') ?>" />
		</p>
	</fieldset>
</form>
<script>
	function upd_spans(value) {
		for (let e of document.querySelectorAll(
		"fieldset.grid-2-col p span[class]"
		)) {
			if (
			document.querySelector(
			"fieldset.grid-2-col label input[id=" + e.className + "]:checked"
		) != null
		)
		e.innerHTML = value;
		else 
		e.innerHTML = '';
		}
	}
	for (let e of document.querySelectorAll('[type="checkbox"][name^="ext_url"]')) {
		e.addEventListener('change', function(){
		let span =  document.querySelector('span.'+this.getAttribute('name'));
		if(!this.checked){
			span.innerHTML ='';
		}  else {
			span.innerHTML = document.querySelector('#id_ext_url').value;
		}
		});  
	}
</script>
<style>
.grid-2-col {
  display:grid;
  grid-template-columns: auto 1fr;
  gap: 0 1em;
}
.grid-2-col p {
  grid-column: 1/-1;
}
 p:not(.alert) {
  margin:0;
  padding:0 0 0.5em;
   border-bottom:solid 1px lightgray
}
p:nth-last-child(2){
  border-bottom:none;
  padding:0
}
fieldset {
  all:revert;
}
span[class^=ext_] {
  color:blue;
  font-weight: bold;
}
</style>
