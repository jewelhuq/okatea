
<?php # début Okatea : ce template étend le layout
$this->extend('layout');
# fin Okatea : ce template étend le layout ?>


<?php # début Okatea : ajout de la CSS spécifique au module
$okt->page->css->addFile(OKT_THEME.'/modules/faq/styles.css');
# fin Okatea : ajout de la CSS spécifique au module ?>


<?php # début Okatea : ajout de jQuery
$okt->page->js->addFile(OKT_COMMON_URL.'/js/jquery/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php # début Okatea : ajout du JS de scrollToTopOfPage
$okt->page->js->addFile(OKT_COMMON_URL.'/js/jquery/easing/jquery.easing.min.js');
$okt->page->js->addFile(OKT_COMMON_URL.'/js/jquery/scrollToTopOfPage/jquery.scrollToTopOfPage.min.js');
$okt->page->js->addReady('
	$("a.scrollTop").scrollToTopOfPage({
		"top": 300,					// hauteur avant affichage du lien
		"duration": 1700,			// durée animation retour en haut
		"easing": "easeOutQuint"	// type animation retour en haut
	});
');
# fin Okatea : ajout du JS de scrollToTopOfPage ?>


<?php # début Okatea : ajout du modal
$okt->page->applyLbl($okt->faq->config->lightbox_type);
# fin Okatea : ajout du modal ?>


<?php # début Okatea : affichage du fil d'ariane
$okt->page->breadcrumb->setHtmlSeparator(' &rsaquo; ');
$okt->page->breadcrumb->display('<p id="ariane"><em>'.__('c_c_user_you_are_here').'</em> %s</p>');
# fin Okatea : affichage du fil d'ariane ?>


<div id="question">
	<div id="question-header">

		<?php # début Okatea : affichage du titre de la question ?>
		<h2 id="question-title"><?php echo html::escapeHTML($faqQuestion->title) ?></h2>
		<?php # fin Okatea : affichage du titre ?>

	</div><!-- #question-header -->

	<div id="question-body">

		<?php # début Okatea : si les images sont activées
		if ($okt->faq->config->images['enable'] && !empty($faqQuestion->images)) : ?>
		<p id="question-images" class="modal-box">

			<?php # début Okatea : boucle sur les images
			foreach ($faqQuestion->images as $i=>$image) : ?>

				<?php # si c'est la première image on affiche la miniature
				if ($i == 1 && isset($image['min_url'])) : ?>

				<a href="<?php echo $image['img_url'] ?>"
				title="<?php printf(__('m_faq_%s_image_%s'), util::escapeAttrHTML($faqQuestion->title), $i) ?>"
				class="modal center" rel="questions-images"><img src="<?php echo $image['min_url'] ?>"
				<?php echo $image['min_attr'] ?>
				alt="<?php echo util::escapeAttrHTML((isset($image['alt']) ? $image['alt'] : $faqQuestion->title)) ?>" /></a>

				<br />

				<?php # si c'est pas la première image on affiche le square
				elseif (isset($image['square_url'])) : ?>

				<a href="<?php echo $image['img_url'] ?>"
				title="<?php printf(__('m_faq_%s_image_%s'), util::escapeAttrHTML($faqQuestion->title), $i) ?>"
				class="modal" rel="questions-images"><img src="<?php echo $image['square_url'] ?>"
				<?php echo $image['square_attr'] ?>
				alt="<?php echo util::escapeAttrHTML((isset($image['alt']) ? $image['alt'] : $faqQuestion->title)) ?>" /></a>

				<?php endif; ?>

			<?php endforeach; # fin Okatea : boucle sur les images ?>

		</p><!-- #post-images -->
		<?php endif; # fin Okatea : si les images sont activées ?>


		<?php # début Okatea : affichage du contenu ?>
		<div id="question-content">
			<?php echo $faqQuestion->content ?>
		</div><!-- #question-content -->
		<?php # fin Okatea : affichage du contenu ?>

	</div><!-- #question-body -->

	<div id="question-footer">

		<?php # début Okatea : si les fichiers sont activées
		if ($okt->faq->config->files['enable'] && !empty($faqQuestion->files[$okt->user->language])) : ?>
		<div id="question-files" class="three-cols">

			<?php # début Okatea : boucle sur les fichiers
			foreach ($faqQuestion->files[$okt->user->language] as $i=>$file) : ?>

			<p class="col"><a href="<?php echo $file['url'] ?>"><img src="<?php echo OKT_COMMON_URL.'/img/media/'.$file['type'].'.png' ?>" alt="<?php echo $file['filename'] ?>" /></a>
			<?php echo $file['type'] ?> (<?php echo $file['mime'] ?>)
			- <?php echo util::l10nFileSize($file['size']) ?></p>

			<?php endforeach; # fin Okatea : boucle sur les fichiers ?>

		</div><!-- #question-files -->
		<?php endif; # fin Okatea : si les fichiers sont activées ?>

	</div><!-- #question-footer -->

</div><!-- #question -->

<p class="scrollTop-wrapper"><a href="#" class="scrollTop"><?php _e('c_c_action_Go_top') ?></a></p>


