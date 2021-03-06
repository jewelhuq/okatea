<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('Layout');

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_config_l10n'));

# button set
$okt->page->setButtonset('l10nBtSt', array(
	'id' => 'l10n-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' 	=> true,
			'title' 		=> __('c_a_config_l10n_add_language'),
			'url' 			=> $view->generateAdminUrl('config_l10n_add_language'),
			'ui-icon' 		=> 'plusthick'
		)
	)
));

# Sortable
$okt->page->js->addReady('
	$("#sortable").sortable({
		placeholder: "ui-state-highlight",
		axis: "y",
		revert: true,
		cursor: "move",
		change: function(event, ui) {
			$("#page,#sortable").css("cursor", "progress");
		},
		update: function(event, ui) {
			var result = $("#sortable").sortable("serialize");

			$.ajax({
				data: result,
				url: "' . $view->generateAdminUrl('config_l10n') . '?ajax_update_order=1",
				success: function(data) {
					$("#page").css("cursor", "default");
					$("#sortable").css("cursor", "move");
				},
				error: function(data) {
					$("#page").css("cursor", "default");
					$("#sortable").css("cursor", "move");
				}
			});
		}
	});

	$("#sortable").find("input").hide();
	$("#save_order").hide();
	$("#sortable").css("cursor", "move");
');

# Javascript
$okt->page->tabs();

# Buttons
$okt->page->js->addReady('

	$("#p_admin_lang_switcher").button({
		icons: {
			primary: "ui-icon-flag"
		}
	});
');

?>

<?php echo $okt->page->getButtonSet('l10nBtSt'); ?>

<div id="tabered">
	<ul>
		<li><a href="#tab-list"><span><?php _e('c_a_config_l10n_tab_list') ?></span></a></li>
		<li><a href="#tab-config"><span><?php _e('c_a_config_l10n_tab_config') ?></span></a></li>
	</ul>

	<div id="tab-list">
		<h3><?php _e('c_a_config_l10n_tab_list') ?></h3>

		<form action="<?php echo $view->generateAdminUrl('config_l10n') ?>"
			method="post" id="ordering">
			<ul id="sortable" class="ui-sortable">
			<?php foreach ($aLanguages as $i => $aLanguage) : ?>
			<li id="ord_<?php echo $aLanguage['id'] ?>" class="ui-state-default"><label
				for="p_order_<?php echo $aLanguage['id'] ?>"> <span class="ui-icon ui-icon-arrowthick-2-n-s"></span>

				<?php if (file_exists($okt['public_path'].'/img/flags/'.$aLanguage['img'])) : ?>
				<img src="<?php echo $okt['public_url'].'/img/flags/'.$aLanguage['img'] ?>" alt="" />
				<?php endif; ?>

				<?php echo $view->escape($aLanguage['title']) ?></label>

				<?php echo form::text(array('p_order['.$aLanguage['id'].']','p_order_'.$aLanguage['id']), 5, 10, $i+1)?>

				- <?php echo $aLanguage['code']?>

				<?php if ($aLanguage['active']) : ?>
				- <a
					href="<?php echo $view->generateAdminUrl('config_l10n') ?>?disable=<?php echo $aLanguage['id'] ?>"
					title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Disable_%s'), $aLanguage['title'])) ?>"
					class="icon tick"><?php _e('c_c_action_Disable') ?></a>
				<?php else : ?>
				- <a
					href="<?php echo $view->generateAdminUrl('config_l10n') ?>?enable=<?php echo $aLanguage['id'] ?>"
					title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Enable_%s'), $aLanguage['title'])) ?>"
					class="icon cross"><?php _e('c_c_action_Enable') ?></a>
				<?php endif; ?>

				- <a
					href="<?php echo $view->generateAdminUrl('config_l10n_edit_language', array('language_id'=>$aLanguage['id'])) ?>"
					title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Edit_%s'), $aLanguage['title'])) ?>"
					class="icon pencil"><?php _e('c_c_action_Edit') ?></a> - <a
					href="<?php echo $view->generateAdminUrl('config_l10n') ?>?delete=<?php echo $aLanguage['id'] ?>"
					onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_config_l10n_confirm_delete')) ?>')"
					title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Delete_%s'), $aLanguage['title'])) ?>"
					class="icon delete"><?php _e('c_c_action_Delete') ?></a></li>
			<?php endforeach; ?>
			</ul>
			<p><?php echo form::hidden('ordered', 1); ?>
			<?php echo form::hidden('order_languages', 1); ?>
			<?php echo $okt->page->formtoken(); ?>
			<input type="submit" id="save_order" value="<?php _e('c_c_action_save_order') ?>" />
			</p>
		</form>
	</div><!-- #tab-list -->

	<div id="tab-config">
		<form action="<?php echo $view->generateAdminUrl('config_l10n') ?>"
			method="post">
			<h3><?php _e('c_a_config_l10n_tab_config') ?></h3>

			<div class="three-cols">

				<p class="field col">
					<label for="p_language"><?php _e('c_a_config_l10n_default_language') ?></label>
				<?php echo form::select('p_language', $aLanguagesForSelect, $okt['config']->language) ?></p>

				<p class="field col">
					<label for="p_timezone"><?php _e('c_a_config_l10n_default_timezone') ?></label>
				<?php echo form::select('p_timezone', $aTimezones, $okt['config']->timezone) ?></p>

				<p class="col"><?php echo form::checkbox('p_admin_lang_switcher', 1, $okt['config']->admin_lang_switcher)?>
				<label for="p_admin_lang_switcher"><?php _e('c_a_config_l10n_enable_switcher') ?></label>
				</p>

			</div>

			<p><?php echo form::hidden('config_sent', 1)?>
			<?php echo $okt->page->formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_save') ?>" />
			</p>
		</form>
	</div><!-- #tab-config -->

</div><!-- #tabered -->
