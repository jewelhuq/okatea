<?php
/**
 * @ingroup okt_module_galleries
 * @brief La page d'administration.
 *
 */

# Accès direct interdit
if (!defined('ON_GALLERIES_MODULE')) die;

if (!$okt->checkPerm('galleries')) {
	$okt->redirect(OKT_ADMIN_LOGIN_PAGE);
}


# suppression d'un élément
if ($okt->page->action === 'delete' && !empty($_GET['item_id']) && $okt->checkPerm('galleries_remove'))
{
	if ($okt->galleries->items->deleteItem($_GET['item_id'])) {
		$okt->redirect('module.php?m=galleries&amp;action=index&amp;deleted=1');
	}
	else {
		$okt->page->action = 'index';
	}
}


# button set
$okt->page->setButtonset('galleriesBtSt',array(
	'id' => 'galleries-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array()
));

# title tag
$okt->page->addTitleTag($okt->galleries->getTitle());

# fil d'ariane
$okt->page->addAriane($okt->galleries->getName(),'module.php?m=galleries');

# inclusion du fichier requis en fonction de l'action demandée
if (!$okt->page->action || $okt->page->action === 'index') {
	require dirname(__FILE__).'/inc/admin/index.php';
}
elseif ($okt->page->action === 'gallery') {
	require dirname(__FILE__).'/inc/admin/gallery.php';
}
elseif ($okt->page->action === 'items') {
	require dirname(__FILE__).'/inc/admin/items.php';
}
elseif ($okt->page->action === 'edit') {
	require dirname(__FILE__).'/inc/admin/item.php';
}
elseif ($okt->page->action === 'add' && $okt->checkPerm('galleries_add')) {
	require dirname(__FILE__).'/inc/admin/item.php';
}
elseif ($okt->page->action === 'add_zip' && $okt->galleries->config->enable_zip_upload && $okt->checkPerm('galleries_add')) {
	require dirname(__FILE__).'/inc/admin/add_zip.php';
}
elseif ($okt->page->action === 'add_multiples' && $okt->galleries->config->enable_multiple_upload && $okt->checkPerm('galleries_add') && file_exists(dirname(__FILE__).'/inc/admin/add_multiples/'.$okt->galleries->config->multiple_upload_type.'.php')) {
	require dirname(__FILE__).'/inc/admin/add_multiples/'.$okt->galleries->config->multiple_upload_type.'.php';
}
elseif ($okt->page->action === 'display' && $okt->checkPerm('galleries_display')) {
	require dirname(__FILE__).'/inc/admin/display.php';
}
elseif ($okt->page->action === 'config' && $okt->checkPerm('galleries_config')) {
	require dirname(__FILE__).'/inc/admin/config.php';
}
else {
	$okt->redirect('index.php');
}
