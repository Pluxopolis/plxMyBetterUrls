<?php if(!defined('PLX_ROOT')) exit; ?>
<?php

# Control du token du formulaire
plxToken::validateFormToken($_POST);

if(!empty($_POST)) {
	if($_POST['ext_url'][0]!='.' AND $_POST['ext_url'][0]!='')
		plxMsg::Error('Bad url extension');
	else {
		$plxPlugin->setParam('ext_url', $_POST['ext_url'], 'string');
		$plxPlugin->saveParams();
	}
	header('Location: parametres_plugin.php?p=plxMyBetterUrls');
	exit;
}
?>

<form id="form_config_plugin" action="parametres_plugin.php?p=plxMyBetterUrls" method="post">
	<fieldset>
		<p class="field"><label for="id_ext_url"><?php $plxPlugin->lang('L_URLS_EXTENSION') ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('ext_url',$plxPlugin->getParam('ext_url'),'text','10-11') ?>&nbsp;ex: <strong>.htm</strong>, .html, .php
		<p class="in-action-bar">
			<?php echo plxToken::getTokenPostMethod() ?>
			<input type="submit" name="submit" value="<?php $plxPlugin->lang('L_SAVE') ?>" />
		</p>
	</fieldset>
</form>
