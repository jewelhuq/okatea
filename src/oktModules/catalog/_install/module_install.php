<?php
/**
 * @ingroup okt_module_catalog
 * @brief La classe d'installation du module.
 *
 */

class moduleInstall_catalog extends oktModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'catalog',
			'catalog_categories',
			'catalog_add',
			'catalog_remove',
			'catalog_display'
		));
	}

	public function installTestSet()
	{
		$this->checklist->addItem(
			'regenerate_thumbnails',
			$this->okt->catalog->regenMinImages(),
			'Regeneration of thumbnails',
			'Cannot regenerate thumbnails'
		);
	}

	public function update()
	{
	}

} # class