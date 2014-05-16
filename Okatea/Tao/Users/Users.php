<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Users;

use Okatea\Tao\Database\Recordset;
use Okatea\Tao\Html\Escaper;
use Okatea\Tao\Html\Modifiers;
use Okatea\Tao\Misc\Mailer;
use Okatea\Tao\Misc\Utilities;

class Users
{

	/**
	 * Okatea application instance.
	 * 
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * The database manager instance.
	 * 
	 * @var object
	 */
	protected $oDb;

	/**
	 * The errors manager instance.
	 * 
	 * @var object
	 */
	protected $oError;

	/**
	 * Core users table.
	 * 
	 * @var string
	 */
	protected $sUsersTable;

	/**
	 * Core users groups table.
	 * 
	 * @var string
	 */
	protected $sGroupsTable;

	/**
	 * Core users groups locales table.
	 * 
	 * @var string
	 */
	protected $sGroupsL10nTable;

	public function __construct($okt)
	{
		$this->okt = $okt;
		$this->oDb = $okt->db;
		$this->oError = $okt->error;
		
		$this->sUsersTable = $this->oDb->prefix . 'core_users';
		$this->sGroupsTable = $this->oDb->prefix . 'core_users_groups';
		$this->sGroupsL10nTable = $this->oDb->prefix . 'core_users_groups_locales';
	}

	/**
	 * Renvoie les données concernant un ou plusieurs utilisateurs.
	 * La méthode renvoie false si elle échoue.
	 *
	 * @param array $aParams        	
	 * @param boolean $bCountOnly
	 *        	de ne retourner que le compte
	 * @return Recordset
	 */
	public function getUsers(array $aParams = array(), $bCountOnly = false)
	{
		$sReqPlus = 'WHERE 1 ';
		
		if (! empty($aParams['id']))
		{
			$sReqPlus .= 'AND u.id=' . (integer) $aParams['id'] . ' ';
		}
		
		if (! empty($aParams['username']))
		{
			$sReqPlus .= 'AND u.username=\'' . $this->oDb->escapeStr($aParams['username']) . '\' ';
		}
		
		if (! empty($aParams['email']))
		{
			$sReqPlus .= 'AND u.email=\'' . $this->oDb->escapeStr($aParams['email']) . '\' ';
		}
		
		if (isset($aParams['status']))
		{
			if ($aParams['status'] == 0)
			{
				$sReqPlus .= 'AND u.status=0 ';
			}
			elseif ($aParams['status'] == 1)
			{
				$sReqPlus .= 'AND u.status=1 ';
			}
			elseif ($aParams['status'] == 2)
			{
				$sReqPlus .= '';
			}
		}
		
		if (isset($aParams['group_id']))
		{
			if (is_array($aParams['group_id']))
			{
				$aParams['group_id'] = array_map('intval', $aParams['group_id']);
				$sReqPlus .= 'AND u.group_id IN (' . implode(',', $aParams['group_id']) . ') ';
			}
			else
			{
				$sReqPlus .= 'AND u.group_id=' . (integer) $aParams['group_id'] . ' ';
			}
		}
		
		if (! empty($aParams['group_id_not']))
		{
			if (is_array($aParams['group_id_not']))
			{
				$aParams['group_id_not'] = array_map('intval', $aParams['group_id_not']);
				$sReqPlus .= 'AND u.group_id NOT IN (' . implode(',', $aParams['group_id_not']) . ') ';
			}
			else
			{
				$sReqPlus .= 'AND u.group_id<>' . (integer) $aParams['group_id_not'] . ' ';
			}
		}
		
		if (! empty($aParams['search']))
		{
			$aWords = Modifiers::splitWords($aParams['search']);
			
			if (! empty($aWords))
			{
				foreach ($aWords as $i => $w)
				{
					$aWords[$i] = 'u.username LIKE \'%' . $this->oDb->escapeStr($w) . '%\' OR ' . 'u.lastname LIKE \'%' . $this->oDb->escapeStr($w) . '%\' OR ' . 'u.firstname LIKE \'%' . $this->oDb->escapeStr($w) . '%\' OR ' . 'u.email LIKE \'%' . $this->oDb->escapeStr($w) . '%\' ';
				}
				$sReqPlus .= ' AND ' . implode(' AND ', $aWords) . ' ';
			}
		}
		
		if ($bCountOnly)
		{
			$sQuery = 'SELECT COUNT(u.id) AS num_users ' . 'FROM ' . $this->sUsersTable . ' AS u ' . $sReqPlus;
		}
		else
		{
			$sQuery = 'SELECT u.*, g.* ' . 'FROM ' . $this->sUsersTable . ' AS u ' . 'LEFT JOIN ' . $this->sGroupsTable . ' AS g ON g.group_id=u.group_id ' . $sReqPlus;
			
			if (isset($aParams['order']))
			{
				$sQuery .= 'ORDER BY ' . $aParams['order'] . ' ';
			}
			else
			{
				$sQuery .= 'ORDER BY u.username DESC ';
			}
			
			if (isset($aParams['limit']))
			{
				$sQuery .= 'LIMIT ' . $aParams['limit'] . ' ';
			}
		}
		
		if (($rs = $this->oDb->select($sQuery)) === false)
		{
			return new Recordset(array());
		}
		
		if ($bCountOnly)
		{
			return (integer) $rs->num_users;
		}
		else
		{
			return $rs;
		}
	}

	/**
	 * Retourne les infos d'un utilisateur donné.
	 * Le paramètre user peut être l'identifiant numérique
	 * ou le nom d'utilisateur.
	 *
	 * @param
	 *        	$mUserId
	 * @return Recordset
	 */
	public function getUser($mUserId)
	{
		$aParams = array();
		
		if (Utilities::isInt($mUserId))
		{
			$aParams['id'] = $mUserId;
		}
		else
		{
			$aParams['username'] = $mUserId;
		}
		
		return $this->getUsers($aParams);
	}

	/**
	 * Vérifie l'existence d'un utilisateur
	 *
	 * @param
	 *        	$mUserId
	 * @return boolean
	 */
	public function userExists($mUserId)
	{
		if ($this->getUser($mUserId)->isEmpty())
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Vérifie qu'il n'y a pas de flood à l'inscription en vérifiant l'IP.
	 * 
	 * @return boolean
	 */
	public function checkRegistrationFlood()
	{
		$sQuery = 'SELECT 1 FROM ' . $this->sUsersTable . ' AS u ' . 'WHERE u.registration_ip=\'' . $this->oDb->escapeStr($this->okt->request->getClientIp()) . '\' ' . 'AND u.registered>' . (time() - 3600);
		
		if (($rs = $this->oDb->select($sQuery)) === false)
		{
			return false;
		}
		
		if (! $rs->isEmpty())
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Vérifie la validité d'un nom d'utilisateur.
	 */
	public function checkUsername(array $aParams = array())
	{
		$username = ! empty($aParams['username']) ? $aParams['username'] : null;
		$username = preg_replace('#\s+#s', ' ', $username);
		
		if (mb_strlen($username) < 2)
		{
			$this->oError->set(__('c_c_users_error_username_too_short'));
		}
		elseif (mb_strlen($username) > 255)
		{
			$this->oError->set(__('c_c_users_error_username_too_long'));
		}
		elseif (mb_strtolower($username) == 'guest')
		{
			$this->oError->set(__('c_c_users_error_reserved_username'));
		}
		elseif (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $username) || preg_match('/((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))/', $username))
		{
			$this->oError->set(__('c_c_users_error_reserved_username'));
		}
		elseif ((strpos($username, '[') !== false || strpos($username, ']') !== false) && strpos($username, '\'') !== false && strpos($username, '"') !== false)
		{
			$this->oError->set(__('c_c_users_error_forbidden_characters'));
		}
		elseif ($this->userExists($username))
		{
			$dupe = true;
			
			if (! empty($aParams['id']))
			{
				$user = $this->getUser($aParams['id']);
				
				if ($user->username == $username)
				{
					$dupe = false;
				}
			}
			
			if ($dupe)
			{
				if ($this->okt->config->users['registration']['merge_username_email'])
				{
					$this->oError->set(__('c_c_users_error_email_already_exist'));
				}
				else
				{
					$this->oError->set(__('c_c_users_error_username_already_exist'));
				}
			}
		}
	}

	/**
	 * Vérifie l'email
	 *
	 * @param
	 *        	$aParams
	 * @return void
	 */
	public function checkEmail(array $aParams = array())
	{
		if (empty($aParams['email']))
		{
			$this->oError->set(__('c_c_users_must_enter_email_address'));
		}
		
		$this->isEmail($aParams['email']);
	}

	/**
	 * Vérifie si l'email est valide
	 *
	 * @param
	 *        	$sEmail
	 * @return void
	 */
	public function isEmail($sEmail)
	{
		if (! Utilities::isEmail($sEmail))
		{
			$this->oError->set(sprintf(__('c_c_error_invalid_email'), Escaper::html($sEmail)));
		}
	}

	/**
	 * Vérifie le mot de passe et la confirmation du mot de passe.
	 *
	 * @param
	 *        	$aParams
	 * @return void
	 */
	public function checkPassword(array $aParams = array())
	{
		if (empty($aParams['password']))
		{
			$this->oError->set(__('c_c_users_must_enter_password'));
		}
		elseif (mb_strlen($aParams['password']) < 4)
		{
			$this->oError->set(__('c_c_users_must_enter_password_of_at_least_4_characters'));
		}
		elseif (empty($aParams['password_confirm']))
		{
			$this->oError->set(__('c_c_users_must_confirm_password'));
		}
		elseif ($aParams['password'] != $aParams['password_confirm'])
		{
			$this->oError->set(__('c_c_users_error_passwords_do_not_match'));
		}
	}

	/**
	 * Ajout d'un utilisateur
	 *
	 * @param
	 *        	$aParams
	 * @return integer
	 */
	public function addUser(array $aParams = array())
	{
		$this->checkUsername($aParams);
		
		$this->checkPassword($aParams);
		
		$this->checkEmail($aParams);
		
		if (! $this->oError->isEmpty())
		{
			return false;
		}
		
		if ($this->okt->config->users['registration']['validation_admin'])
		{
			$aParams['group_id'] = 0;
		}
		elseif (empty($aParams['group_id']) || ! $this->okt->getGroups()->groupExists($aParams['group_id']))
		{
			$aParams['group_id'] = $this->okt->config->users['registration']['default_group'];
		}
		
		$sPasswordHash = password_hash($aParams['password'], PASSWORD_DEFAULT);
		
		if ($this->okt->config->users['registration']['validation_email'])
		{
			$aParams['activate_string'] = $sPasswordHash;
			$aParams['activate_key'] = Utilities::random_key(8);
		}
		
		$aParams['status'] = 0;
		
		$iTime = time();
		
		$sQuery = 'INSERT INTO ' . $this->sUsersTable . ' ( ' . 'group_id, civility, status, username, lastname, firstname, displayname, ' . 'password, email, timezone, language, registered, registration_ip, last_visit, ' . 'activate_string, activate_key' . ') VALUES ( ' . (integer) $aParams['group_id'] . ', ' . (integer) $aParams['civility'] . ', ' . (integer) $aParams['status'] . ', ' . '\'' . $this->oDb->escapeStr($aParams['username']) . '\', ' . (! empty($aParams['lastname']) ? '\'' . $this->oDb->escapeStr($aParams['lastname']) . '\', ' : 'null,') . (! empty($aParams['firstname']) ? '\'' . $this->oDb->escapeStr($aParams['firstname']) . '\', ' : 'null,') . (! empty($aParams['displayname']) ? '\'' . $this->oDb->escapeStr($aParams['displayname']) . '\', ' : 'null,') . '\'' . $this->oDb->escapeStr($sPasswordHash) . '\', ' . '\'' . $this->oDb->escapeStr($aParams['email']) . '\', ' . (! empty($aParams['timezone']) ? '\'' . $this->oDb->escapeStr($aParams['timezone']) . '\', ' : 'null,') . (! empty($aParams['language']) ? '\'' . $this->oDb->escapeStr($aParams['language']) . '\', ' : 'null,') . $iTime . ', ' . (! empty($aParams['registration_ip']) ? '\'' . $this->oDb->escapeStr($aParams['registration_ip']) . '\', ' : '\'0.0.0.0\', ') . $iTime . ', ' . (! empty($aParams['activate_string']) ? '\'' . $this->oDb->escapeStr($aParams['activate_string']) . '\', ' : 'null,') . (! empty($aParams['activate_key']) ? '\'' . $this->oDb->escapeStr($aParams['activate_key']) . '\' ' : 'null') . '); ';
		
		if (! $this->oDb->execute($sQuery))
		{
			throw new \Exception('Unable to insert user into database');
		}
		
		$iNewId = $this->oDb->getLastID();
		
		return $iNewId;
	}

	/**
	 * Mise à jour d'une page
	 *
	 * @return boolean
	 */
	public function updUser(array $aParams = array())
	{
		$rsUser = $this->getUsers(array(
			'id' => $aParams['id']
		));
		
		if ($rsUser->isEmpty())
		{
			$this->oError->set(sprintf(__('c_c_users_error_user_%s_not_exists'), $aParams['id']));
			return false;
		}
		
		if ($rsUser->group_id == Groups::SUPERADMIN)
		{
			# si on veut désactiver un super-admin alors il faut vérifier qu'il y en as d'autres
			if ($aParams['status'] == 0)
			{
				$iCountSudo = $this->getUsers(array(
					'group_id' => Groups::SUPERADMIN,
					'status' => 1
				), true);
				
				if ($iCountSudo < 2)
				{
					$this->oError->set(__('c_c_users_error_cannot_disable_last_super_administrator'));
					return false;
				}
			}
			
			# si on veut changer le groupe d'un super-admin alors il faut vérifier qu'il y en as d'autres
			if ($aParams['group_id'] != Groups::SUPERADMIN)
			{
				$iCountSudo = $this->getUsers(array(
					'group_id' => Groups::SUPERADMIN,
					'status' => 1
				), true);
				
				if ($iCountSudo < 2)
				{
					$this->oError->set(__('c_c_users_error_cannot_change_group_last_super_administrator'));
					return false;
				}
			}
		}
		
		$sql = array();
		
		$this->checkUsername($aParams);
		$sql[] = 'username=\'' . $this->oDb->escapeStr($aParams['username']) . '\'';
		
		$this->checkEmail($aParams);
		$sql[] = 'email=\'' . $this->oDb->escapeStr($aParams['email']) . '\'';
		
		if (isset($aParams['group_id']))
		{
			$sql[] = 'group_id=' . (integer) $aParams['group_id'];
		}
		
		if (isset($aParams['civility']))
		{
			$sql[] = 'civility=' . (integer) $aParams['civility'];
		}
		
		if (isset($aParams['status']))
		{
			$sql[] = 'status=' . (integer) $aParams['status'];
		}
		
		if (isset($aParams['lastname']))
		{
			$sql[] = 'lastname=\'' . $this->oDb->escapeStr($aParams['lastname']) . '\'';
		}
		
		if (isset($aParams['firstname']))
		{
			$sql[] = 'firstname=\'' . $this->oDb->escapeStr($aParams['firstname']) . '\'';
		}
		
		if (isset($aParams['displayname']))
		{
			$sql[] = 'displayname=\'' . $this->oDb->escapeStr($aParams['displayname']) . '\'';
		}
		
		if (isset($aParams['language']))
		{
			$sql[] = 'language=\'' . $this->oDb->escapeStr($aParams['language']) . '\'';
		}
		
		if (isset($aParams['timezone']))
		{
			$sql[] = 'timezone=\'' . $this->oDb->escapeStr($aParams['timezone']) . '\'';
		}
		
		if (! $this->oError->isEmpty())
		{
			return false;
		}
		
		$sQuery = 'UPDATE ' . $this->sUsersTable . ' SET ' . implode(', ', $sql) . ' ' . 'WHERE id=' . (integer) $aParams['id'];
		
		if (! $this->oDb->execute($sQuery))
		{
			throw new \Exception('Unable to update user in database.');
		}
		
		return true;
	}

	/**
	 * Modification du mot de passe d'un utilisateur
	 *
	 * @param
	 *        	$aParams
	 * @return boolean
	 */
	public function changeUserPassword(array $aParams = array())
	{
		$this->checkPassword($aParams);
		
		if (! $this->oError->isEmpty())
		{
			return false;
		}
		
		$sPasswordHash = password_hash($aParams['password'], PASSWORD_DEFAULT);
		
		$sQuery = 'UPDATE ' . $this->sUsersTable . ' SET ' . 'password=\'' . $this->oDb->escapeStr($sPasswordHash) . '\' ' . 'WHERE id=' . (integer) $aParams['id'];
		
		if (! $this->oDb->execute($sQuery))
		{
			throw new \Exception('Unable to update user in database.');
		}
		
		return true;
	}

	/**
	 * Delete a user.
	 *
	 * @param integer $id        	
	 * @throws \Exception
	 * @return boolean
	 */
	public function deleteUser($iUserId)
	{
		$rsUser = $this->getUsers(array(
			'id' => $iUserId
		));
		
		if ($rsUser->isEmpty())
		{
			$this->oError->set(sprintf(__('c_c_users_error_user_%s_not_exists'), $iUserId));
			return false;
		}
		
		# si on veut supprimer un super-admin alors il faut vérifier qu'il y en as d'autres
		if ($rsUser->group_id == Groups::SUPERADMIN)
		{
			$iCountSudo = $this->getUsers(array(
				'group_id' => Groups::SUPERADMIN,
				'status' => 1
			), true);
			
			if ($iCountSudo < 2)
			{
				$this->oError->set(__('c_c_users_error_cannot_remove_last_super_administrator'));
				return false;
			}
		}
		
		$sQuery = 'DELETE FROM ' . $this->sUsersTable . ' ' . 'WHERE id=' . (integer) $iUserId;
		
		if (! $this->oDb->execute($sQuery))
		{
			throw new \Exception('Unable to remove user from database.');
		}
		
		$this->oDb->optimize($this->sUsersTable);
		
		# delete user custom fields
		if ($this->okt->config->users['custom_fields_enabled'])
		{
			$this->fields->delUserValue($iUserId);
		}
		
		return true;
	}

	/**
	 * Valide un utilisateur (le place dans le groupe par défaut)
	 *
	 * @param integer $iUserId        	
	 * @return boolean
	 */
	public function validateUser($iUserId)
	{
		if (! $this->userExists($iUserId))
		{
			$this->oError->set(sprintf(__('c_c_users_error_user_%s_not_exists'), $iUserId));
			return false;
		}
		
		$sSqlQuery = 'UPDATE ' . $this->sUsersTable . ' SET ' . 'group_id = ' . (integer) $this->okt->config->users['registration']['default_group'] . ' ' . 'WHERE id=' . (integer) $iUserId;
		
		if (! $this->oDb->execute($sSqlQuery))
		{
			throw new \Exception('Unable to update user in database.');
		}
		
		return true;
	}

	/**
	 * Switch le statut d'un utilisateur donné
	 *
	 * @param integer $iUserId        	
	 * @return boolean
	 */
	public function switchUserStatus($iUserId)
	{
		if (! $this->userExists($iUserId))
		{
			$this->oError->set(sprintf(__('c_c_users_error_user_%s_not_exists'), $iUserId));
			return false;
		}
		
		$sSqlQuery = 'UPDATE ' . $this->sUsersTable . ' SET ' . 'status = 1-status ' . 'WHERE id=' . (integer) $iUserId;
		
		if (! $this->oDb->execute($sSqlQuery))
		{
			throw new \Exception('Unable to update user in database.');
		}
		
		return true;
	}

	/**
	 * Définit le statut d'un utilisateur donné
	 *
	 * @param integer $iUserId        	
	 * @param integer $iStatus        	
	 * @return boolean
	 */
	public function setUserStatus($iUserId, $iStatus)
	{
		$iStatus = intval($iStatus);
		
		$rsUser = $this->getUsers(array(
			'id' => $iUserId
		));
		
		if ($rsUser->isEmpty())
		{
			$this->oError->set(sprintf(__('c_c_users_error_user_%s_not_exists'), $iUserId));
			return false;
		}
		
		# si on veut désactiver un super-admin alors il faut vérifier qu'il y en as d'autres
		if ($iStatus == 0 && $rsUser->group_id == Groups::SUPERADMIN)
		{
			$iCountSudo = $this->getUsers(array(
				'group_id' => Groups::SUPERADMIN,
				'status' => 1
			), true);
			
			if ($iCountSudo < 2)
			{
				$this->oError->set(__('c_c_users_error_cannot_disable_last_super_administrator'));
				return false;
			}
		}
		
		$sSqlQuery = 'UPDATE ' . $this->sUsersTable . ' SET ' . 'status = ' . ($iStatus == 1 ? 1 : 0) . ' ' . 'WHERE id=' . (integer) $iUserId;
		
		if (! $this->oDb->execute($sSqlQuery))
		{
			throw new \Exception('Unable to update user in database.');
		}
		
		return true;
	}

	/**
	 * Envoi un email avec un nouveau mot de passe.
	 *
	 * @param string $sEmail
	 *        	L'adresse email où envoyer le nouveau mot de passe
	 * @param string $sActivateUrl
	 *        	de la page de validation
	 * @return boolean
	 */
	public function forgetPassword($sEmail, $sActivateUrl)
	{
		# validation de l'adresse fournie
		if (! Utilities::isEmail($sEmail))
		{
			$this->oError->set(__('c_c_auth_invalid_email'));
			return false;
		}
		
		# récupération des infos de l'utilisateur
		$rsUser = $this->getUsers(array(
			'email' => $sEmail
		));
		
		if ($rsUser === false || $rsUser->isEmpty())
		{
			$this->oError->set(__('c_c_auth_unknown_email'));
			return false;
		}
		
		# génération du nouveau mot de passe et du code d'activation
		$sNewPassword = Utilities::random_key(8, true);
		$sNewPasswordKey = Utilities::random_key(8);
		
		$sPasswordHash = password_hash($sNewPassword, PASSWORD_DEFAULT);
		
		$sQuery = 'UPDATE ' . $this->sUsersTable . ' SET ' . 'activate_string=\'' . $this->oDb->escapeStr($sPasswordHash) . '\', ' . 'activate_key=\'' . $this->oDb->escapeStr($sNewPasswordKey) . '\' ' . 'WHERE id=' . (integer) $rsUser->id;
		
		if (! $this->oDb->execute($sQuery))
		{
			return false;
		}
		
		# Initialisation du mailer et envoi du mail
		$oMail = new Mailer($this->okt);
		
		$oMail->setFrom();
		
		$this->okt->l10n->loadFile($this->okt->options->get('locales_dir') . '/%s/emails', $rsUser->language);
		
		$aMailParams = array(
			'site_title' => $this->okt->page->getSiteTitle($rsUser->language),
			'site_url' => $this->okt->request->getSchemeAndHttpHost() . $this->okt->config->app_path,
			'user' => Users::getUserDisplayName($rsUser->username, $rsUser->lastname, $rsUser->firstname, $rsUser->displayname),
			'password' => $sNewPassword,
			'validate_url' => $sActivateUrl . '?uid=' . $rsUser->id . '&key=' . rawurlencode($sNewPasswordKey)
		);
		
		$oMail->setSubject(__('c_c_emails_request_new_password'));
		$oMail->setBody($this->renderView('emails/newPassword/text', $aMailParams), 'text/plain');
		
		if ($this->viewExists('emails/newPassword/html'))
		{
			$oMail->addPart($this->renderView('emails/newPassword/html', $aMailParams), 'text/html');
		}
		
		$oMail->message->setTo($rsUser->email);
		
		$oMail->send();
		
		return true;
	}

	/**
	 * Validate a password key for a given user id.
	 *
	 * @param integer $iUserId        	
	 * @param string $sKey        	
	 * @return boolean
	 */
	public function validatePasswordKey($iUserId, $sKey)
	{
		$rsUser = $this->getUser($iUserId);
		
		if ($rsUser === false || $rsUser->isEmpty())
		{
			$this->oError->set(__('c_c_auth_unknown_email'));
			return false;
		}
		
		if (rawurldecode($sKey) != $rsUser->activate_key)
		{
			$this->oError->set(__('c_c_auth_validation_key_not_match'));
			return false;
		}
		
		$sQuery = 'UPDATE ' . $this->sUsersTable . ' SET ' . 'password=\'' . $rsUser->activate_string . '\', ' . 'activate_string=NULL, ' . 'activate_key=NULL ' . 'WHERE id=' . (integer) $iUserId . ' ';
		
		if (! $this->oDb->execute($sQuery))
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Static function that returns user's common name given to his
	 * username, lastname, firstname and displayname.
	 *
	 * @param string $sUsername
	 *        	name
	 * @param string $sLastname
	 *        	last name
	 * @param string $sFirstname
	 *        	first name
	 * @param string $sDisplayName
	 *        	display name
	 * @return string
	 */
	public static function getUserDisplayName($sUsername, $sLastname = null, $sFirstname = null, $sDisplayName = null)
	{
		if (! empty($sDisplayName))
		{
			return $sDisplayName;
		}
		
		if (! empty($sLastname))
		{
			if (! empty($sFirstname))
			{
				return $sFirstname . ' ' . $sLastname;
			}
			
			return $sLastname;
		}
		elseif (! empty($sFirstname))
		{
			return $sFirstname;
		}
		
		return $sUsername;
	}

	/**
	 * Retourne la liste des civilités.
	 *
	 * @param boolean $flip        	
	 * @return array
	 */
	public static function getCivilities($flip = false)
	{
		$a = array(
			1 => __('c_c_user_civility_1'),
			2 => __('c_c_user_civility_2'),
			3 => __('c_c_user_civility_3')
		);
		
		if ($flip)
		{
			$a = array_flip($a);
		}
		
		return $a;
	}
}
