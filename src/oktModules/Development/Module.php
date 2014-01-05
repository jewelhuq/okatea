<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Module\Development;

use Tao\Admin\Page;
use Tao\Admin\Menu as AdminMenu;
use Tao\Modules\Module as BaseModule;

class Module extends BaseModule
{
	protected function prepend()
	{
		# Autoload
		$this->okt->autoloader->addClassMap(array(
			'Okatea\Module\Development\DebugBar' 					=> __DIR__.'/DebugBar.php'
		));

		# permissions
		$this->okt->addPermGroup('development', 		__('m_development_perm_group'));
			$this->okt->addPerm('development_usage', 		__('m_development_perm_usage'), 'development');
			$this->okt->addPerm('development_debug_bar', 	__('m_development_perm_debug_bar'), 'development');
			$this->okt->addPerm('development_bootstrap', 	__('m_development_perm_bootstrap'), 'development');
			$this->okt->addPerm('development_counting', 	__('m_development_perm_counting'), 'development');

		# Config
		$this->config = $this->okt->newConfig('conf_development');

		# Initialisation debug bar
		$this->debugBar = new DebugBar($this->okt, $this->config->debug_bar);
	}

	protected function prepend_admin()
	{
		# autoload
		$this->okt->autoloader->addClassMap(array(
			'Okatea\Module\Development\Admin\Controller\Index' 		=> __DIR__.'/Admin/Controller/Index.php',
			'Okatea\Module\Development\Admin\Controller\Debugbar' 	=> __DIR__.'/Admin/Controller/Debugbar.php',
			'Okatea\Module\Development\Admin\Controller\Bootstrap' 	=> __DIR__.'/Admin/Controller/Bootstrap.php',
			'Okatea\Module\Development\Admin\Controller\Counting' 	=> __DIR__.'/Admin/Controller/Counting.php',
			'Okatea\Module\Development\Bootstrap\Module\Module' 	=> __DIR__.'/Bootstrap/Module/Module.php',
			'Okatea\Module\Development\Bootstrap\Module\Advanced' 	=> __DIR__.'/Bootstrap/Module/Advanced.php',
			'Okatea\Module\Development\Bootstrap\Module\Simple' 	=> __DIR__.'/Bootstrap/Module/Simple.php',
			'Okatea\Module\Development\CountingFilesAndLines' 		=> __DIR__.'/CountingFilesAndLines.php'
		));

		# On ajoutent un item au menu
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->DevelopmentSubMenu = new AdminMenu(null, Page::$formatHtmlSubMenu);
			$this->okt->page->mainMenu->add(
				__('m_development_menu_development'),
				$this->okt->adminRouter->generate('Development_index'),
				$this->okt->request->attributes->get('_route') === 'Development_index',
				10000001,
				$this->okt->checkPerm('development_usage'),
				null,
				$this->okt->page->DevelopmentSubMenu,
				$this->url().'/icon.png'
			);
				$this->okt->page->DevelopmentSubMenu->add(
					__('m_development_menu_development'),
					$this->okt->adminRouter->generate('Development_index'),
					$this->okt->request->attributes->get('_route') === 'Development_index',
					1,
					$this->okt->checkPerm('development_usage')
				);
				$this->okt->page->DevelopmentSubMenu->add(
					__('m_development_menu_debugbar'),
					$this->okt->adminRouter->generate('Development_debugbar'),
					$this->okt->request->attributes->get('_route') === 'Development_debugbar',
					2,
					$this->okt->checkPerm('development_debug_bar')
				);
				$this->okt->page->DevelopmentSubMenu->add(
					__('m_development_menu_bootstrap'),
					$this->okt->adminRouter->generate('Development_bootstrap'),
					$this->okt->request->attributes->get('_route') === 'Development_bootstrap',
					3,
					$this->okt->checkPerm('development_bootstrap')
				);
				$this->okt->page->DevelopmentSubMenu->add(
					__('m_development_menu_counting'),
					$this->okt->adminRouter->generate('Development_counting'),
					$this->okt->request->attributes->get('_route') === 'Development_counting',
					4,
					$this->okt->checkPerm('development_counting')
				);
		}

		# Message admin home
		if ($this->okt->user->is_superadmin)
		{
			$this->okt->triggers->registerTrigger('adminIndexHtmlContent',
				array('module_development','adminIndexHtmlContent'));
		}

		# Add admin debug bar
		$this->debugBar->loadInAdminPart();
	}

	protected function prepend_public()
	{
		# Add public debug bar
		$this->debugBar->loadInPublicPart();

		# Ajout d'éléments à la barre admin
		if ($this->okt->user->is_superadmin)
		{
			$this->okt->triggers->registerTrigger('publicAdminBarItems',
				array('module_development', 'publicAdminBarItems'));
		}
	}

	/**
	 * Ajout d'un avertissement sur la page d'accueil de l'admin.
	 *
	 * @param oktCore $okt
	 * @return void
	 */
	public static function adminIndexHtmlContent($okt)
	{
		echo
		'<div class="ui-widget" style="width: 700px; margin:0 auto 20px auto;">'.
			'<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 5px 8px;">'.
				'<p class="ui-helper-clearfix "><span class="ui-icon ui-icon-info" style="float: left; margin: 2px 8px 0 0;"></span>'.
				'<span style="float: left;">'.__('m_development_adminIndexHtmlContent').'</span></p>'.
			'</div>'.
		'</div>';
	}

	/**
	 * Ajout d'un avertissement sur la barre admin côté publique.
	 *
	 * @param oktCore $okt
	 * @param arrayObject $aPrimaryAdminBar
	 * @param arrayObject $aSecondaryAdminBar
	 * @param arrayObject $aBasesUrl
	 * @return void
	 */
	public static function publicAdminBarItems($okt, $aPrimaryAdminBar, $aSecondaryAdminBar, $aBasesUrl)
	{
		$aPrimaryAdminBar[10]['items'][100] = array(
			'href' => $aBasesUrl['admin'].'/configuration.php?action=modules',
			'title' => __('m_development_ab_module_enable_title'),
			'intitle' => __('m_development_ab_module_enable')
		);
	}


}
