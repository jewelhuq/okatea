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

<h3><?php _e('m_pages_post_tab_title_files')?></h3>

<div class="two-cols">
<?php for ($i=1; $i<=$okt->module('Pages')->config->files['number']; $i++) : ?>
	<div class="col">
		<p class="field">
			<label for="p_files_<?php echo $i ?>"><?php printf(__('m_pages_post_file_%s'), $i)?> </label>
		<?php echo form::file('p_files_'.$i) ?></p>

		<?php
	# il y a un fichier ?
	if (!empty($aPageData['files'][$i]))
	:
		
		$aCurFileTitle = isset($aPageData['files'][$i]['title']) ? $aPageData['files'][$i]['title'] : [];
		?>

			<?php foreach ($okt['languages']->getList() as $aLanguage) : ?>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>">
			<label
				for="p_files_title_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->hasUniqueLanguage() ? printf(__('m_pages_post_file_title_%s'), $i) : printf(__('m_pages_post_file_title_%s_in_%s'), $i, $aLanguage['title']) ?> <span
				class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_files_title_'.$i.'['.$aLanguage['code'].']','p_files_title_'.$i.'_'.$aLanguage['code']), 40, 255, (isset($aCurFileTitle[$aLanguage['code']]) ? $view->escape($aCurFileTitle[$aLanguage['code']]) : '')) ?></p>

			<?php endforeach; ?>

			<p>
			<a href="<?php echo $aPageData['files'][$i]['url'] ?>"><img
				src="<?php echo $okt['public_url'].'/img/media/'.$aPageData['files'][$i]['type'].'.png' ?>"
				alt="" /></a>
			<?php echo $aPageData['files'][$i]['type'] ?> (<?php echo $aPageData['files'][$i]['mime'] ?>)
			- <?php echo Utilities::l10nFileSize($aPageData['files'][$i]['size']) ?></p>

			<?php if ($aPermissions['bCanEditPost']) : ?>
			<p>
			<a
				href="<?php echo $view->generateAdminUrl('Pages_post', array('page_id' => $aPageData['post']['id'])) ?>?delete_file=<?php echo $i ?>"
				onclick="return window.confirm('<?php echo $view->escapeJs(_e('m_pages_post_delete_file_confirm')) ?>')"
				class="icon delete"><?php _e('m_pages_post_delete_file')?></a>
		</p>
			<?php endif; ?>

		<?php else : ?>

			<?php foreach ($okt['languages']->getList() as $aLanguage) : ?>
			<p class="field" lang="<?php echo $aLanguage['code'] ?>">
			<label
				for="p_files_title_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->hasUniqueLanguage() ? printf(__('m_pages_post_file_title_%s'), $i) : printf(__('m_pages_post_file_title_%s_in_%s'), $i, $aLanguage['title']) ?> <span
				class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_files_title_'.$i.'['.$aLanguage['code'].']','p_files_title_'.$i.'_'.$aLanguage['code']), 40, 255, '') ?></p>
			<?php endforeach; ?>

		<?php endif; ?>
	</div>
<?php endfor; ?>
</div>

<p class="note"><?php echo Utilities::getMaxUploadSizeNotice() ?></p>
