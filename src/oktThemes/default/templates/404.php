
<?php $view->extend('layout'); ?>

<?php # Title tag
$okt->page->addTitleTag($okt->page->getSiteTitleTag(null, $okt->page->getSiteTitle()));
$okt->page->addTitleTag(__('c_c_doc_not_fount')); ?>

<h1><?php _e('c_c_doc_not_fount') ?></h1>

<p><?php _e('c_c_doc_not_exists') ?></p>
