<?php
/**
 * @ingroup okt_module_pages
 * @brief La classe d'installation du Module Pages.
 *
 */

class moduleInstall_pages extends oktModuleInstall
{
	public function installTestSet()
	{
		$this->checklist->addItem(
			'regenerate_thumbnails',
			$this->okt->pages->regenMinImages(),
			'Regeneration of thumbnails',
			'Cannot regenerate thumbnails'
		);
	}

	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'pages',
			'pages_categories',
			'pages_add',
			'pages_remove',
			'pages_display'
		));
	}

	public function update()
	{
	}

} # class