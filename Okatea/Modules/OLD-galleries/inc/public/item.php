<?php
/**
 * @ingroup okt_module_galleries
 * @brief "controller" pour l'affichage public d'un élément d'une galerie
 *
 */
use Okatea\Tao\Html\Modifiers;

# inclusion du preprend public général
require_once __DIR__ . '/../../../../oktInc/public/prepend.php';

# récupération de la galerie en fonction du slug
$slug = !empty($_GET['slug']) ? $_GET['slug'] : null;

# Récupération des éléments de la galerie
$rsItem = $okt->galleries->getItems(array(
	'slug' => $slug,
	'visibility' => 1
));

if ($rsItem->isEmpty())
{
	$okt->page->serve404();
}

# module actuel
$okt->page->module = 'galleries';
$okt->page->action = 'item';

# début du fil d'ariane
$okt->page->breadcrumb->add($okt->galleries->getName(), $okt->galleries->config->url);

# title tag du module
$okt->page->addTitleTag($okt->galleries->getTitle());

# Ajout de la hiérarchie des rubriques au fil d'ariane et au title tag
$rsPath = $okt->galleries->getPath($rsItem->gallery_id, true);
while ($rsPath->fetch())
{
	$okt->page->addTitleTag($rsPath->name);
	
	$okt->page->breadcrumb->add($rsPath->name, $okt->page->getBaseUrl() . $okt->galleries->config->public_gallery_url . '/' . $rsPath->slug);
}
unset($rsPath);

if ($rsItem->title_tag == '')
{
	$rsItem->title_tag = $rsItem->title;
}

$okt->page->addTitleTag($rsItem->title_tag);

$okt->page->breadcrumb->add($rsItem->title, $rsItem->getItemUrl());

$rsItem->image = $rsItem->getImagesInfo();

if ($okt->galleries->config->enable_rte == '' && $rsItem->legend != '')
{
	$rsItem->legend = Modifiers::nlToP($rsItem->legend);
}

# meta description
if ($rsItem->meta_description != '')
{
	$okt->page->meta_description = $rsItem->meta_description;
}
elseif ($okt->galleries->config->meta_description != '')
{
	$okt->page->meta_description = $okt->galleries->config->meta_description;
}
else
{
	$okt->page->meta_description = Utilities::getSiteMetaDesc();
}

# meta keywords
if ($rsItem->meta_keywords != '')
{
	$okt->page->meta_keywords = $rsItem->meta_keywords;
}
elseif ($okt->galleries->config->meta_keywords != '')
{
	$okt->page->meta_keywords = $okt->galleries->config->meta_keywords;
}
else
{
	$okt->page->meta_keywords = Utilities::getSiteMetaKeywords();
}
