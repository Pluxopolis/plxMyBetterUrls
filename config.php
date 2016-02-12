<?php if(!defined('PLX_ROOT')) exit; ?>
<?php

# Control du token du formulaire
plxToken::validateFormToken($_POST);

if(!empty($_POST)) {
	if($_POST['ext_url'][0]!='.' AND $_POST['ext_url'][0]!='')
		plxMsg::Error($plxPlugin->getLang('L_BAD_URL_EXTENSION'));
	else {
		$ext = ($_POST['ext_url']!=''? '.'.plxUtils::title2url($_POST['ext_url']):'');
		$plxPlugin->setParam('ext_url', $ext, 'string');
		$plxPlugin->setParam('format_article', plxUtils::title2url($_POST['format_article']), 'string');
		$plxPlugin->setParam('format_category', plxUtils::title2url($_POST['format_category']), 'string');
		$plxPlugin->setParam('format_static', plxUtils::title2url($_POST['format_static']), 'string');
		$plxPlugin->saveParams();
	}
	header('Location: parametres_plugin.php?p=plxMyBetterUrls');
	exit;
}

$format_article = $plxPlugin->getParam('format_article');
$format_category = $plxPlugin->getParam('format_category');
$format_static = $plxPlugin->getParam('format_static');
?>
<form id="form_config_plugin" action="parametres_plugin.php?p=plxMyBetterUrls" method="post">
	<fieldset>
		<p class="field"><label for="id_ext_url"><?php $plxPlugin->lang('L_URLS_EXTENSION') ?>&nbsp;:</label></p>
		<input onkeyup="upd_spans(this.value)" type="text" id="id_ext_url" name="ext_url" size="10" maxlength="11" value="<?php echo $plxPlugin->getParam('ext_url') ?>" />&nbsp;ex: <strong>.htm</strong>, .html, .php

		<p><?php $plxPlugin->lang('L_URLS_FORMAT') ?></p>

		<p class="field">
			<label for="id_format_article"><?php $plxPlugin->lang('L_ARTICLE') ?> :</label>
			<?php echo $plxAdmin->aConf['racine'] ?><?php plxUtils::printInput('format_article',$format_article,'text','5-255') ?>/mon-article<span id="ext_article"><?php echo $plxPlugin->getParam('ext_url') ?></span>

			<label for="id_format_article"><?php $plxPlugin->lang('L_CATEGORY') ?> :</label>
			<?php echo $plxAdmin->aConf['racine'] ?><?php plxUtils::printInput('format_category',$format_category,'text','5-255') ?>/ma-categorie<span id="ext_category"><?php echo $plxPlugin->getParam('ext_url') ?></span>

			<label for="id_format_static"><?php $plxPlugin->lang('L_STATIC') ?> :</label>
			<?php echo $plxAdmin->aConf['racine'] ?><?php plxUtils::printInput('format_static',$format_static,'text','5-255') ?>/ma-page-statique<span id="ext_static"><?php echo $plxPlugin->getParam('ext_url') ?></span>
		</p>

		<p class="in-action-bar">
			<?php echo plxToken::getTokenPostMethod() ?>
			<input type="submit" name="submit" value="<?php $plxPlugin->lang('L_SAVE') ?>" />
		</p>
	</fieldset>
</form>
<script>
function upd_spans(value) {
	document.getElementById('ext_article').innerHTML = value;
	document.getElementById('ext_category').innerHTML = value;
	document.getElementById('ext_static').innerHTML = value;
}
</script>