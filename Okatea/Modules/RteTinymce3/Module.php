<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\RteTinymce3;

use Okatea\Tao\Modules\Module as BaseModule;

class Module extends BaseModule
{
	public $config = null;

	protected function prepend()
	{
		# permissions
		$this->okt->addPerm('rte_tinymce_3_config', __('m_rte_tinymce_3_perm_config'), 'configuration');

		# configuration
		$this->config = $this->okt->newConfig('conf_rte_tinymce_3');
	}

	protected function prepend_admin()
	{
		# autoload
		$this->okt->autoloader->addClassMap(array(
			'Okatea\Modules\RteTinymce3\Admin\Controller\Config' => __DIR__.'/Admin/Controller/Config.php'
		));

		$this->okt->page->addRte('tinymce_4','tinyMCE 4', array('Okatea\Modules\RteTinymce3\Module','tinyMCE'));

		# on ajoutent un item au menu configuration
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->configSubMenu->add(
				__('TinyMCE 4'),
				$this->okt->adminRouter->generate('RteTinymce3_config'),
				$this->okt->request->attributes->get('_route') === 'RteTinymce3_config',
				40,
				$this->okt->checkPerm('rte_tinymce_3_config'),
				null
			);
		}
	}

	public static function tinyMCE($element='textarea', $user_options=array())
	{
		global $okt;

		$aOptions = array();

		# selector
		$aOptions[] = 'selector: "'.$element.'"';

		# theme
		$aOptions[] = 'theme: "modern"';

		# language
		$sLanguageCode = strtolower($okt->user->language);
		$sSpecificLanguageCode = strtolower($okt->user->language).'_'.strtoupper($okt->user->language);

		if (file_exists($okt->options->get('modules_dir').'/RteTinymce3/tinymce/langs/'.$sLanguageCode.'.js')) {
			$aOptions[] = 'language: "'.$sLanguageCode.'"';
		}
		elseif (file_exists($okt->options->get('modules_dir').'/RteTinymce3/tinymce/langs/'.$sSpecificLanguageCode.'.js')) {
			$aOptions[] = 'language: "'.$sSpecificLanguageCode.'"';
		}

		# plugins
		$aOptions[] = 'plugins: "advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table contextmenu paste"';

		# toolbar
		$aOptions[] = 'toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"';

		# content CSS
		if ($okt->RteTinymce3->config->content_css != '') {
			$aOptions[] = 'content_css: "'.$okt->RteTinymce3->config->content_css.'"';
		}

		# editor width
		if ($okt->RteTinymce3->config->width != '') {
			$aOptions[] = 'width: "'.$okt->RteTinymce3->config->width.'"';
		}

		# editor height
		if ($okt->RteTinymce3->config->height != '') {
			$aOptions[] = 'height: "'.$okt->RteTinymce3->config->height.'"';
		}

		# gestionnaire de media
		if ($okt->modules->moduleExists('media_manager'))
		{
			$aOptions[] = 'file_browser_callback: function (field_name, url, type, win) {
					tinymce.activeEditor.windowManager.open({
						title: "Media manager",
						url: "'.$okt->config->app_path.'admin/module.php?m=media_manager&popup=1&editor=1&type=" + type,
						width: 700,
						height: 450
					}, {
					oninsert: function(url) {
						var fieldElm = win.document.getElementById(field_name);

						fieldElm.value = url;
						if ("createEvent" in document) {
							var evt = document.createEvent("HTMLEvents");
							evt.initEvent("change", false, true);
							fieldElm.dispatchEvent(evt);
						} else {
							fieldElm.fireEvent("onchange");
						}
					}
				});
			}';
		}

		$okt->page->js->addFile($okt->options->get('modules_url').'/RteTinymce3/tinymce/tinymce.min.js');

		$okt->page->js->addScript('

			tinymce.init({'.
				implode(',', $aOptions).
			'});

		');
	}

}
