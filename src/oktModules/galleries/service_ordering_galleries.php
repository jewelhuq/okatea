<?php
/**
 * @ingroup okt_module_galleries
 * @brief Service pour la requete AJAX ordering galleries
 *
 */


# inclusion du preprend public général
require_once dirname(__FILE__).'/../../oktInc/admin/prepend.php';

if (!$okt->checkPerm('galleries')) {
	exit;
}

$order = !empty($_GET['ord']) ? $_GET['ord'] : array();

foreach ($order as $ord=>$id)
{
	$ord = ((integer) $ord)+1;
	$okt->galleries->setGalleryPosition($id, $ord);
}

