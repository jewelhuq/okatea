<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Misc\Utilities;

$view->extend('Layout');

# Module title tag
$okt->page->addTitleTag(__('Development'));

# Start breadcrumb
$okt->page->addAriane(__('Development'), $view->generateAdminUrl('Development_index'));

?>

<p><?php _e('m_development_counting_desc') ?></p>

<?php if (isset($oCountig)) : ?>
<ul>
	<li><?php printf(__('m_development_counting_total_folders'), Utilities::formatNumber($oCountig->getNumFolders(),0)) ?></li>
	<li><?php printf(__('m_development_counting_total_files'), Utilities::formatNumber($oCountig->getNumFiles(),0)) ?></li>
	<li><?php printf(__('m_development_counting_total_lines'), Utilities::formatNumber($oCountig->getNumLines(),0)) ?></li>
</ul>
<?php endif; ?>

<form action="<?php echo $view->generateAdminUrl('Development_counting') ?>"
	method="post">
	<p>
		<input type="hidden" name="form_sent" value="1" />
	<?php echo $okt->page->formtoken()?>
	<input type="submit" class="lazy-load"
			value="<?php _e('m_development_counting_action') ?>" />
	</p>
</form>