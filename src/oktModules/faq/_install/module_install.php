<?php
/**
 * @ingroup okt_module_faq
 * @brief La classe d'installation du module faq.
 *
 */

class moduleInstall_faq extends oktModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'faq',
			'faq_add',
			'faq_remove',
			'faq_categories',
			'faq_display'
		));
	}

	public function installTestSet()
	{
		$this->checklist->addItem(
			'regenerate_thumbnails',
			$this->okt->faq->regenMinImages(),
			'Regeneration of thumbnails',
			'Cannot regenerate thumbnails'
		);
	}

	public function update()
	{
	}

} # class