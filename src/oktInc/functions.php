<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Les fonctions
 *
 * @addtogroup Okatea
 *
 */

use Monolog\Logger;
use Monolog\Handler\FirePHPHandler;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;


if (!function_exists('console'))
{
	function console($mData)
	{
		$console = new Logger('console',array(
			new FirePHPHandler()
		),array(
			new IntrospectionProcessor(),
			new WebProcessor(),
			new MemoryUsageProcessor(),
			new MemoryPeakUsageProcessor()
		));

		$console->addDebug(var_export($mData, true));
	}
}

if (!function_exists('debug'))
{
	/**
	 * Utilitaire de debug rapide.
	 *
	 * @param mixed $mData 				La variable à déboguer
	 * @param boolean $bDie 			Kill the script after
	 * @return void
	 */
	function debug($mData, $bDie=false)
	{
		echo '<pre class="debug">';
		var_export($mData);
		echo '</pre>';

		if ($bDie) {
			die;
		}
	}
}

if (!function_exists('_e'))
{
	/**
	 * Translated and display string
	 *
	 * @see l10n::trans()
	 *
	 * @param string $singular Singular form of the string
	 * @param string $pural Plural form of the string (optionnal)
	 * @param integer $count Context number for plural form (optionnal)
	 * @return string translated string
	 */
	function _e($singular, $plural=null, $count=null)
	{
		echo __($singular, $plural, $count);
	}
}
