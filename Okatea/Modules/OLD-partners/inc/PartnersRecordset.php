<?php
/**
 * @ingroup okt_module_partners
 * @brief Extension du recordset pour les partenaires
 *
 */
use Okatea\Tao\Database\Recordset;

class PartnersRecordset extends Recordset
{

	/**
	 * Okatea application instance.
	 * 
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * Défini l'instance de l'application qui sera passée à l'objet après
	 * qu'il ait été instancié.
	 *
	 * @param
	 *        	Okatea\Tao\Application okt Okatea application instance.
	 * @return void
	 */
	public function setCore($okt)
	{
		$this->okt = $okt;
	}

	/**
	 * Retourne les informations des images d'un partenaire en fonction des données de la BDD
	 *
	 * @return array
	 */
	public function getImagesInfo()
	{
		if (!$this->okt->partners->config->images['enable'])
		{
			return [];
		}
		
		return $this->getImagesArray();
	}

	/**
	 * Retourne les informations de la première image d'un partenaire
	 * en fonction des données de la BDD
	 *
	 * @return array
	 */
	public function getFirstImageInfo()
	{
		if (!$this->okt->partners->config->images['enable'])
		{
			return [];
		}
		
		$a = $this->getImagesArray();
		
		return isset($a[1]) ? $a[1] : [];
	}

	public function getImagesArray()
	{
		return array_filter((array) unserialize($this->logo));
	}

	public function getCurrentImagesDir()
	{
		return $this->okt->partners->upload_dir . '/img/' . $this->id;
	}

	public function getCurrentImagesUrl()
	{
		return $this->okt->partners->upload_url . '/img/' . $this->id;
	}
}
