<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$view->extend('Layout');

$okt->page->addGlobalTitle(__('c_a_themes_management'), $view->generateAdminUrl('config_themes'));
$okt->page->addGlobalTitle(sprintf(__('c_a_themes_file_comparison_theme_%s'), $oInstallTheme->name()));

$okt->page->css->addCss('
	.Differences {
		width: 100%;
		border-collapse: collapse;
		border-spacing: 0;
		empty-cells: show;
	}

	.Differences thead th {
		text-align: left;
		border-bottom: 1px solid #000;
		background: #aaa;
		color: #000;
		padding: 4px;
	}
	.Differences tbody th {
		text-align: right;
		background: #ccc;
		width: 4em;
		padding: 1px 2px;
		border-right: 1px solid #000;
		vertical-align: top;
		font-size: 13px;
	}

	.Differences td {
		padding: 1px 2px;
		font-family: Consolas, monospace;
		font-size: 13px;
	}

	.DifferencesSideBySide .ChangeInsert td.Left {
		background: #dfd;
	}

	.DifferencesSideBySide .ChangeInsert td.Right {
		background: #cfc;
	}

	.DifferencesSideBySide .ChangeDelete td.Left {
		background: #f88;
	}

	.DifferencesSideBySide .ChangeDelete td.Right {
		background: #faa;
	}

	.DifferencesSideBySide .ChangeReplace .Left {
		background: #fe9;
	}

	.DifferencesSideBySide .ChangeReplace .Right {
		background: #fd8;
	}

	.Differences ins, .Differences del {
		text-decoration: none;
	}

	.DifferencesSideBySide .ChangeReplace ins, .DifferencesSideBySide .ChangeReplace del {
		background: #fc0;
	}

	.Differences .Skipped {
		background: #f7f7f7;
	}

	.DifferencesInline .ChangeReplace .Left,
	.DifferencesInline .ChangeDelete .Left {
		background: #fdd;
	}

	.DifferencesInline .ChangeReplace .Right,
	.DifferencesInline .ChangeInsert .Right {
		background: #dfd;
	}

	.DifferencesInline .ChangeReplace ins {
		background: #9e9;
	}

	.DifferencesInline .ChangeReplace del {
		background: #e99;
	}
');
?>

<?php echo $oInstallTheme->checklist->getHTML(); ?>

<div class="checklistlegend">
	<p><?php _e('c_c_checklist_legend') ?></p>
	<?php echo $oInstallTheme->checklist->getLegend(); ?>
</div>

<p class="ui-helper-clearfix">
	<a class="button"
		href="<?php echo $view->generateAdminUrl('config_themes') ?>"><?php _e('Continue') ?></a>
</p>
