<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Modules\Manage\Component;

use Okatea\Tao\Modules\Manage\Component\ComponentBase;

class TemplatesFiles extends ComponentBase
{
	/**
	 * Copy/replace templates files.
	 *
	 * @return void
	 */
	public function process()
	{
		$sTemplatesDir = $this->module->root().'/install/templates';

		if (!is_dir($sTemplatesDir)) {
			return null;
		}

		$oFiles = $this->getFiles();

		if (empty($oFiles)) {
			return null;
		}

		$this->checklist->addItem(
			'templates_files',
			$this->mirror(
				$sTemplatesDir,
				$this->okt->options->get('themes_dir').'/default/templates/'.$this->module->id(),
				$oFiles
			),
			'Create templates files',
			'Cannot create templates files'
		);
	}

	/**
	 * Delete assets directory.
	 *
	 */
	public function delete()
	{
		$sPath = $this->okt->options->get('themes_dir').'/default/templates/'.$this->module->id();

		if (!is_dir($sPath)) {
			return null;
		}

		$this->checklist->addItem(
			'remove_templates',
			$this->getFs()->remove($sPath),
			'Remove templates files',
			'Cannot remove templates files'
		);
	}

	protected function getFiles()
	{
		$sPath = $this->module->root().'/install/templates';

		if (is_dir($sPath))
		{
			$finder = $this->getFinder();
			$finder->in($sPath);

			return $finder;
		}

		return null;
	}

	protected function mirror($src, $dest, $oFiles)
	{
		return $this->getFs()->mirror($src, $dest, $oFiles, array(
			'override' 			=> true,
			'copy_on_windows' 	=> true,
			'delete' 			=> false
		));
	}
}
