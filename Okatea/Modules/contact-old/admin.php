<?php
/**
 * @ingroup okt_module_contact
 * @brief La page d'administration du module contact
 *
 */


# Accès direct interdit
if (!defined('ON_MODULE')) die;

# title tag
$okt->page->addTitleTag($okt->contact->getTitle());

# fil d'ariane
$okt->page->addAriane($okt->contact->getName(),'module.php?m=contact');


# inclusion du fichier requis en fonction de l'action demandée
if ((!$okt->page->action || $okt->page->action === 'index') && $okt->checkPerm('contact_recipients')) {
	require __DIR__.'/admin/index.php';
}
elseif ($okt->page->action === 'fields' && $okt->checkPerm('contact_fields')) {
	require __DIR__.'/admin/fields.php';
}
elseif ($okt->page->action === 'field' && $okt->checkPerm('contact_fields')) {
	require __DIR__.'/admin/field.php';
}
elseif ($okt->page->action === 'config' && $okt->checkPerm('contact_config')) {
	require __DIR__.'/admin/config.php';
}
else {
	http::redirect('index.php');
}