<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Extensions;

use Okatea\Tao\Database\Recordset;
use Symfony\Component\Finder\Finder;

class Manager extends Collection
{
	/**
	 * List of all extensions in the file system.
	 * @var array
	 */
	protected $aAll;

	/**
	 * Temporary extension identifier.
	 * @var string
	 */
	protected $sTempId;


	/**
	 * Returns a list of extensions registered in the database.
	 *
	 * @param array $aParams
	 * @return object Recordset
	 */
	public function getFromDatabase(array $aParams = array())
	{
		$reqPlus = 'WHERE 1 ';

		if (!empty($aParams['id'])) {
			$reqPlus .= 'AND id=\''.$this->db->escapeStr($aParams['id']).'\' ';
		}

		if (!empty($aParams['status'])) {
			$reqPlus .= 'AND status='.(integer)$aParams['status'].' ';
		}

		if (!empty($aParams['type'])) {
			$reqPlus .= 'AND type=\''.$this->db->escapeStr($aParams['type']).'\' ';
		}

		$strReq =
		'SELECT id, name, description, author, version, priority, updatable, status, type '.
		'FROM '.$this->t_extensions.' '.
		$reqPlus.
		'ORDER BY priority ASC, id ASC ';

		if (($rs = $this->db->select($strReq)) === false) {
			return new Recordset(array());
		}

		return $rs;
	}

	/**
	 * Returns a list of extensions from the file system.
	 *
	 * @return array
	 */
	public function getFromFileSystem()
	{
		$finder = (new Finder())
			->files()
			->in($this->path)
			->depth('== 1')
			->name('_define.php');

		foreach ($finder as $file)
		{
			$this->sTempId = $file->getRelativePath();

			require $file->getRealpath();

			$this->id = null;
			$this->mroot = null;
		}

		return $this->aAll;
	}

	/**
	 * Returns a list of all the extensions in the file system.
	 *
	 * @return array
	 */
	public function getAll()
	{
		if (null === $this->aAll) {
			$this->getFromFileSystem();
		}

		return $this->aAll;
	}

	/**
	 * Resets the list of all the extensions in the file system.
	 *
	 * @return void
	 */
	public function resetAll()
	{
		$this->aAll = array();
	}

	/**
	 * Cette fonction est utilisée dans les fichiers _define.php
	 * des extensions pour qu'elles soient prises en compte par le système.
	 *
	 * Cette méthode reçoit en argument un tableau de paramètres,
	 * les paramètres possibles sont les suivants :
	 * 	- name 		Le nom de l'extension
	 * 	- desc 		La description de l'extension
	 * 	- version 	Le numero de version de l'extension
	 * 	- author 	L'auteur de l'extension ('')
	 * 	- priority 	Priorité de l'extension (1000)
	 * 	- updatable	Blocage de mise à jour (true)
	 *
	 * @param array $aParams Le tableau de paramètres
	 * @return void
	 */
	public function register(array $aParams = array())
	{
		if (null !== $this->sTempId)
		{
			$this->aAll[$this->sTempId] = array(
				'id' 			=> $this->sTempId,
				'root'			=> $this->path.'/'.$this->sTempId,
				'name' 			=> (!empty($aParams['name']) 		? $aParams['name'] 					: $this->id),
				'desc' 			=> (!empty($aParams['desc']) 		? $aParams['desc'] 					: null),
				'version' 		=> (!empty($aParams['version']) 	? $aParams['version'] 				: null),
				'author' 		=> (!empty($aParams['author']) 		? $aParams['author'] 				: null),
				'priority' 		=> (!empty($aParams['priority']) 	? (integer)$aParams['priority'] 	: 1000),
				'updatable' 	=> (!empty($aParams['updatable']) 	? (boolean)$aParams['updatable'] 	: true)
			);
		}
	}

	/**
	 * Returns the list of installed extensions.
	 *
	 * @return array
	 */
	public function getInstalled()
	{
		$rsInstalled = $this->getFromDatabase();

		$aInstalled = array();

		while ($rsInstalled->fetch())
		{
			$aInstalled[$rsInstalled->id] = array(
				'id' 			=> $rsInstalled->id,
				'root' 			=> $this->path.'/'.$rsInstalled->id,
				'name' 			=> $rsInstalled->name,
				'name_l10n' 	=> __($rsInstalled->name),
				'desc' 			=> $rsInstalled->description,
				'desc_l10n' 	=> __($rsInstalled->description),
				'author' 		=> $rsInstalled->author,
				'version' 		=> $rsInstalled->version,
				'priority' 		=> $rsInstalled->priority,
				'status' 		=> $rsInstalled->status,
				'updatable' 	=> $rsInstalled->updatable
			);
		}

		return $aInstalled;
	}
}
