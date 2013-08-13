<?php
/**
 * @ingroup okt_module_antispam
 * @brief La classe principale du module Antispam.
 *
 */

class module_antispam extends oktModule
{
	protected function prepend()
	{
		global $oktAutoloadPaths;

		# chargement des principales locales
		l10n::set(dirname(__FILE__).'/locales/'.$this->okt->user->language.'/main');

		# autoload
		$oktAutoloadPaths['oktSpamFilter'] = dirname(__FILE__).'/inc/class.spamfilter.php';
		$oktAutoloadPaths['oktSpamFilters'] = dirname(__FILE__).'/inc/class.spamfilters.php';
		$oktAutoloadPaths['oktAntispam'] = dirname(__FILE__).'/inc/lib.antispam.php';

		$oktAutoloadPaths['oktFilterIP'] = dirname(__FILE__).'/filters/class.filter.ip.php';
		$oktAutoloadPaths['oktFilterIpLookup'] = dirname(__FILE__).'/filters/class.filter.iplookup.php';
		$oktAutoloadPaths['oktFilterLinksLookup'] = dirname(__FILE__).'/filters/class.filter.linkslookup.php';
		$oktAutoloadPaths['oktFilterWords'] = dirname(__FILE__).'/filters/class.filter.words.php';

		# permissions
		$this->okt->addPerm('antispam',__('m_antispam_perm_global'), 'configuration');

		$this->okt->spamfilters = array('oktFilterIP','oktFilterWords','oktFilterIpLookup','oktFilterLinksLookup');
	}

	protected function prepend_admin()
	{
		# on détermine si on est actuellement sur ce module
		$this->onThisModule();

		# chargement des locales admin
		l10n::set(dirname(__FILE__).'/locales/'.$this->okt->user->language.'/admin');

		# on ajoutent un item au menu admin
		if (!defined('OKT_DISABLE_MENU'))
		{
			$this->okt->page->configSubMenu->add(
				__('Antispam'),
				'module.php?m=antispam',
				ON_ANTISPAM_MODULE,
				25,
				$this->okt->checkPerm('antispam'),
				null
			);
		}
	}

} # class
