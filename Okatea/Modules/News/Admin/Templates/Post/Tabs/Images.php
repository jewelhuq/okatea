<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Misc\Utilities;

?>

<h3><?php _e('m_news_post_tab_title_images')?></h3>
<div class="two-cols modal-box">
<?php for ($i=1; $i<=$okt->module('News')->config->images['number']; $i++) : ?>
	<div class="col">
		<fieldset>
			<legend><?php printf(__('m_news_post_image_%s'), $i) ?></legend>

			<p class="field">
				<label for="p_images_<?php echo $i ?>"><?php printf(__('m_news_post_image_%s'), $i) ?></label>
			<?php echo form::file('p_images_'.$i) ?></p>

			<?php
	# il y a une image ?
	if (! empty($aPostData['images'][$i]))
	:
		
		# affichage square ou icon ?
		if (isset($aPostData['images'][$i]['min_url']))
		{
			$sCurImageUrl = $aPostData['images'][$i]['min_url'];
			$sCurImageAttr = $aPostData['images'][$i]['min_attr'];
		}
		elseif (isset($aPostData['images'][$i]['square_url']))
		{
			$sCurImageUrl = $aPostData['images'][$i]['square_url'];
			$sCurImageAttr = $aPostData['images'][$i]['square_attr'];
		}
		else
		{
			$sCurImageUrl = $okt['public_url'] . '/img/media/image.png';
			$sCurImageAttr = ' width="48" height="48" ';
		}
		
		$aCurImageAlt = isset($aPostData['images'][$i]['alt']) ? $aPostData['images'][$i]['alt'] : array();
		$aCurImageTitle = isset($aPostData['images'][$i]['title']) ? $aPostData['images'][$i]['title'] : array();
		
		?>

				<?php foreach ($okt['languages']->list as $aLanguage) : ?>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>">
				<label
					for="p_images_title_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->unique ? printf(__('m_news_post_image_title_%s'), $i) : printf(__('m_news_post_image_title_%s_in_%s'), $i, $aLanguage['title']) ?> <span
					class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_images_title_'.$i.'['.$aLanguage['code'].']','p_images_title_'.$i.'_'.$aLanguage['code']), 40, 255, (isset($aCurImageTitle[$aLanguage['code']]) ? $view->escape($aCurImageTitle[$aLanguage['code']]) : '')) ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>">
				<label
					for="p_images_alt_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->unique ? printf(__('m_news_post_image_alt_text_%s'), $i) : printf(__('m_news_post_image_alt_text_%s_in_%s'), $i, $aLanguage['title']) ?> <span
					class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_images_alt_'.$i.'['.$aLanguage['code'].']','p_images_alt_'.$i.'_'.$aLanguage['code']), 40, 255, (isset($aCurImageAlt[$aLanguage['code']]) ? $view->escape($aCurImageAlt[$aLanguage['code']]) : '')) ?></p>

				<?php endforeach; ?>

				<p>
				<a href="<?php echo $aPostData['images'][$i]['img_url']?>"
					rel="post_images"
					title="<?php echo $view->escapeHtmlAttr(sprintf(__('m_news_post_image_title_attr_%s'),$aPostData['locales'][$okt['visitor']->language]['title'], $i)) ?>"
					class="modal"><img src="<?php echo $sCurImageUrl ?>"
					<?php echo $sCurImageAttr ?> alt="" /></a>
			</p>

				<?php if ($aPermissions['bCanEditPost']) : ?>
				<p>
				<a
					href="<?php echo $view->generateUrl('News_post', array('post_id' => $aPostData['post']['id'])) ?>?delete_image=<?php echo $i ?>"
					onclick="return window.confirm('<?php echo $view->escapeJs(_e('m_news_post_delete_image_confirm')) ?>')"
					class="icon delete"><?php _e('m_news_post_delete_image') ?></a>
			</p>
				<?php endif; ?>

			<?php else : ?>

				<?php foreach ($okt['languages']->list as $aLanguage) : ?>
				<p class="field" lang="<?php echo $aLanguage['code'] ?>">
				<label
					for="p_images_title_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->unique ? printf(__('m_news_post_image_title_%s'), $i) : printf(__('m_news_post_image_title_%s_in_%s'), $i,$aLanguage['title']) ?> <span
					class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_images_title_'.$i.'['.$aLanguage['code'].']','p_images_title_'.$i.'_'.$aLanguage['code']), 40, 255, '') ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>">
				<label
					for="p_images_alt_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->unique ? printf(__('m_news_post_image_alt_text_%s'), $i) : printf(__('m_news_post_image_alt_text_%s_in_%s'), $i,$aLanguage['title']) ?> <span
					class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_images_alt_'.$i.'['.$aLanguage['code'].']','p_images_alt_'.$i.'_'.$aLanguage['code']), 40, 255, '') ?></p>
				<?php endforeach; ?>

			<?php endif; ?>

		</fieldset>
	</div>
<?php endfor; ?>
</div>
<p class="note"><?php echo Utilities::getMaxUploadSizeNotice() ?></p>
