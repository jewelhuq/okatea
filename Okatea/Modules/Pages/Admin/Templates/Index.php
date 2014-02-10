<?php

use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('layout');

# Module title tag
$okt->page->addTitleTag($okt->Pages->getTitle());

# Start breadcrumb
$okt->page->addAriane($okt->Pages->getName(), $view->generateUrl('Pages_index'));


# Autocomplétion du formulaire de recherche
$okt->page->js->addReady('
	$("#search").autocomplete({
		source: "'.$view->generateurl('Pages_index').'?json=1",
		minLength: 2
	});
');

if (!empty($sSearch))
{
	$okt->page->js->addFile($okt->options->public_url.'/plugins/putCursorAtEnd/jquery.putCursorAtEnd.min.js');
	$okt->page->js->addReady('
		$("#search").putCursorAtEnd();
	');
}

# button set
$okt->page->setButtonset('pagesBtSt',array(
	'id' => 'pages-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission'    => $okt->checkPerm('pages_add'),
			'title'         => __('m_pages_menu_add_page'),
			'url'           => $view->generateUrl('Pages_post_add'),
			'ui-icon'       => 'plusthick'
		),
		array(
			'permission' 	=> true,
			'title' 		=> __('c_c_display_filters'),
			'url' 			=> '#',
			'ui-icon' 		=> 'search',
			'active' 		=> $okt->Pages->filters->params->show_filters,
			'id'			=> 'filter-control',
			'class'			=> 'button-toggleable'
		),
		array(
			'permission' 	=> true,
			'title' 		=> __('c_c_action_show'),
			'url' 			=> $okt->router->generateFromAdmin('pagesList'),
			'ui-icon' 		=> 'extlink'
		)
	)
));


# Filters control
if ($okt->Pages->config->admin_filters_style == 'slide')
{
	# Slide down
	$okt->page->filterControl($okt->Pages->filters->params->show_filters);
}
elseif ($okt->Pages->config->admin_filters_style == 'dialog')
{
	# Display a UI dialog box
	$okt->page->js->addReady("
		$('#filters-form').dialog({
			title:'".$view->escapeJs(__('c_c_display_filters'))."',
			autoOpen: false,
			modal: true,
			width: 500,
			height: 300
		});

		$('#filter-control').click(function() {
			$('#filters-form').dialog('open');
		})
	");
}


# Un peu de CSS
$okt->page->css->addCss('
.ui-autocomplete {
	max-height: 150px;
	overflow-y: auto;
	overflow-x: hidden;
}
.search_form p {
	margin: 0;
}
#search {
	background: transparent url('.$okt->options->public_url.'/img/admin/preview.png) no-repeat center right;
}
#post-count {
	margin-top: 0;
}
');

?>

<div class="double-buttonset">
	<div class="buttonsetA">
		<?php echo $okt->page->getButtonSet('pagesBtSt'); ?>
	</div>
	<div class="buttonsetB">
		<form action="<?php echo $view->generateurl('Pages_index') ?>" method="get" id="search_form" class="search_form">
			<p><label for="search"><?php _e('m_pages_list_Search') ?></label>
			<?php echo form::text('search', 20, 255, $view->escape((isset($sSearch) ? $sSearch : ''))); ?>

			<?php echo form::hidden('m','pages') ?>
			<?php echo form::hidden('action','index') ?>
			<input type="submit" name="search_submit" id="search_submit" value="<?php _e('c_c_action_ok') ?>" /></p>
		</form>
	</div>
</div>

<?php # formulaire des filtres ?>
<form action="<?php echo $view->generateurl('Pages_index') ?>" method="get" id="filters-form">
	<fieldset>
		<legend><?php _e('m_pages_display_filters') ?></legend>

		<?php echo $okt->Pages->filters->getFiltersFields('<div class="three-cols">%s</div>'); ?>

		<p><input type="submit" name="<?php echo $okt->Pages->filters->getFilterSubmitName() ?>" value="<?php _e('c_c_action_display') ?>" />
		<a href="<?php echo $view->generateurl('Pages_index') ?>?init_filters=1"><?php _e('c_c_reset_filters') ?></a></p>

	</fieldset>
</form>

<div id="pagesList">

<?php # Affichage du compte de pages
if ($iNumFilteredPosts == 0) : ?>
<p id="post-count"><?php _e('m_pages_list_no_page') ?></p>
<?php elseif ($iNumFilteredPosts == 1) : ?>
<p id="post-count"><?php _e('m_pages_list_one_page') ?></p>
<?php else : ?>
	<?php if ($iNumPages > 1) : ?>
		<p id="post-count"><?php printf(__('m_pages_list_%s_pages_on_%s_pages'), $iNumFilteredPosts, $iNumPages) ?></p>
	<?php else : ?>
		<p id="post-count"><?php printf(__('m_pages_list_%s_pages'), $iNumFilteredPosts) ?></p>
	<?php endif; ?>
<?php endif; ?>

<?php # Si on as des pages à afficher
if (!$rsPages->isEmpty()) : ?>

<form action="<?php echo $view->generateurl('Pages_index') ?>" method="post" id="pages-list">

	<table class="common">
		<caption><?php _e('m_pages_list_table_caption') ?></caption>
		<thead><tr>
			<th scope="col"><?php _e('m_pages_list_table_th_title') ?></th>
			<?php if ($okt->Pages->config->categories['enable']) : ?>
			<th scope="col"><?php _e('m_pages_list_table_th_category') ?></th>
			<?php endif; ?>
			<?php if ($okt->Pages->config->enable_group_perms) : ?>
			<th scope="col"><?php _e('m_pages_list_table_th_access') ?></th>
			<?php endif; ?>
			<th scope="col"><?php _e('c_c_Actions') ?></th>
		</tr></thead>
		<tbody>
		<?php $count_line = 0;
		while ($rsPages->fetch()) :
			$td_class = $count_line%2 == 0 ? 'even' : 'odd';
			$count_line++;
		?>
		<tr>
			<th scope="row" class="<?php echo $td_class ?> fake-td">
				<?php echo form::checkbox(array('pages[]'),$rsPages->id) ?>
				<a href="<?php echo $view->generateUrl('Pages_post', array('page_id' => $rsPages->id)) ?>"><?php
				echo $view->escape($rsPages->title) ?></a>
			</th>

			<?php if ($okt->Pages->config->categories['enable']) : ?>
			<td class="<?php echo $td_class ?>"><?php echo $view->escape($rsPages->category_title) ?></td>
			<?php endif; ?>

			<?php # droits d'accès
			if ($okt->Pages->config->enable_group_perms) :

				$aGroupsAccess = array();
				$aPerms = $okt->Pages->getPagePermissions($rsPages->id);
				foreach ($aPerms as $iPerm) {
					$aGroupsAccess[] = $view->escape($aGroups[$iPerm]);
				}
				unset($aPerms);
			?>
			<td class="<?php echo $td_class ?>">
				<?php if (!empty($aGroupsAccess)) : ?>
				<ul>
					<li><?php echo implode('</li><li>',$aGroupsAccess) ?></li>
				</ul>
				<?php endif; ?>
			</td>
			<?php endif; ?>

			<td class="<?php echo $td_class ?> small">
				<ul class="actions">
					<?php if ($rsPages->active) : ?>
					<li><a href="<?php echo $view->generateUrl('Pages_index') ?>?switch_status=<?php echo $rsPages->id ?>"
					title="<?php echo $view->escapeHtmlAttr(sprintf(__('m_pages_list_switch_visibility_%s'), $rsPages->title)) ?>"
					class="icon tick"><?php _e('c_c_action_visible') ?></a></li>
					<?php else : ?>
					<li><a href="<?php echo $view->generateUrl('Pages_index') ?>?switch_status=<?php echo $rsPages->id ?>"
					title="<?php echo $view->escapeHtmlAttr(sprintf(__('m_pages_list_switch_visibility_%s'), $rsPages->title)) ?>"
					class="icon cross"><?php _e('c_c_action_hidden_fem') ?></a></li>
					<?php endif; ?>

					<li><a href="<?php echo $view->generateUrl('Pages_post', array('page_id' => $rsPages->id)) ?>"
					title="<?php echo $view->escapeHtmlAttr(sprintf(__('m_pages_list_edit_%s'), $rsPages->title)) ?>"
					class="icon pencil"><?php _e('c_c_action_edit') ?></a></li>

					<?php if ($okt->checkPerm('pages_remove')) : ?>
					<li><a href="<?php echo $view->generateurl('Pages_index') ?>?delete=<?php echo $rsPages->id ?>"
					onclick="return window.confirm('<?php echo $view->escapeJs(__('m_pages_list_page_delete_confirm')) ?>')"
					title="<?php echo $view->escapeHtmlAttr(sprintf(__('m_pages_list_delete_%s'), $rsPages->title)) ?>"
					class="icon delete"><?php _e('c_c_action_delete') ?></a></li>
					<?php endif; ?>
				</ul>
			</td>
		</tr>
		<?php endwhile; ?>
		</tbody>
	</table>
	<?php echo $view->render('Common/FormListBatches', array(
		'sFormId'             => 'pages-list',
		'sActionsLabel'       => __('m_pages_list_pages_action'),
		'aActionsChoices'     => $aActionsChoices
	)); ?>
</form>

<?php if ($iNumPages > 1) : ?>
<ul class="pagination"><?php echo $oPager->getLinks(); ?></ul>
<?php endif; ?>

<?php endif; ?>

</div><!-- #pagesList -->

