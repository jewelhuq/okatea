<?php
/**
 * @ingroup okt_module_lbl_fancybox
 * @brief La page d'administration.
 *
 */

# Accès direct interdit
if (!defined('ON_LBL_FANCYBOX_MODULE')) die;


# inclusion du fichier requis en fonction de l'action demandée
if ($okt->page->action === 'config' && $okt->checkPerm('fancybox_config')) {
	require __DIR__.'/inc/admin/config.php';
}
else {
	$okt->redirect('index.php');
}
