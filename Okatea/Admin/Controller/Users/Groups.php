<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Admin\Controller\Users;

use ArrayObject;
use Okatea\Admin\Controller;
use Okatea\Tao\Users\Authentification;
use Okatea\Tao\Users\Groups as UsersGroups;

class Groups extends Controller
{
	public function index()
	{
		if (!$this->okt->checkPerm('users_groups')) {
			return $this->serve401();
		}

		$this->okt->l10n->loadFile($this->okt->options->get('locales_dir').'/%s/admin/users');

		if ($this->okt->request->query->has('delete_id'))
		{
			$iGroupIdToDelete = $this->okt->request->query->get('delete_id');

			if ($this->okt->getGroups()->deleteGroup($iGroupIdToDelete))
			{
				$this->okt->page->flash->success(__('c_a_users_group_deleted'));

				return $this->redirect($this->generateUrl('Users_groups'));
			}
		}

		$aParams = array(
			'language' => $this->okt->user->language
		);

		if (!$this->okt->user->is_superadmin) {
			$aParams['group_id_not'][] = UsersGroups::SUPERADMIN;
		}

		if (!$this->okt->user->is_admin) {
			$aParams['group_id_not'][] = UsersGroups::ADMIN;
		}

		$rsGroups = $this->okt->getGroups()->getGroups($aParams);

		return $this->render('Users/Groups/Index', array(
			'rsGroups' 	=> $rsGroups
		));
	}

	public function add()
	{
		if (!$this->okt->checkPerm('users_groups')) {
			return $this->serve401();
		}

		$this->okt->l10n->loadFile($this->okt->options->get('locales_dir').'/%s/admin/users');

		$aGroupData = new ArrayObject();

		$aGroupData['locales'] = array();

		foreach ($this->okt->languages->list as $aLanguage)
		{
			$aGroupData['locales'][$aLanguage['code']] = array();
			$aGroupData['locales'][$aLanguage['code']]['title'] = '';
		}

		$aGroupData['perms'] = array();

		if ($this->okt->request->request->has('form_sent'))
		{
			foreach ($this->okt->languages->list as $aLanguage)
			{
				$aGroupData['locales'][$aLanguage['code']]['title'] = $this->request->request->get('p_title['.$aLanguage['code'].']', '', true);

				if (empty($aGroupData['locales'][$aLanguage['code']]['title']))
				{
					if ($this->okt->languages->unique) {
						$this->okt->error->set(__('c_a_users_must_enter_group_title'));
					}
					else {
						$this->okt->error->set(sprintf(__('c_a_users_must_enter_group_title_in_%s'), $aLanguage['title']));
					}
				}
			}

			if ($this->okt->request->request->has('perms')) {
				$aGroupData['perms'] = array_keys($this->okt->request->request->get('perms'));
			}

			if ($this->okt->error->isEmpty())
			{
				if (($iGroupId = $this->okt->getGroups()->addGroup($aGroupData)) !== false)
				{
					$this->okt->page->flash->success(__('c_a_users_group_added'));

					return $this->redirect($this->generateUrl('Users_groups_edit', array('group_id' => $iGroupId)));
				}
			}
		}

		return $this->render('Users/Groups/Add', array(
			'aGroupData'     => $aGroupData,
			'aPermissions'   => $this->okt->getPermsForDisplay()
		));
	}

	public function edit()
	{
		if (!$this->okt->checkPerm('users_groups')) {
			return $this->serve401();
		}

		$this->okt->l10n->loadFile($this->okt->options->get('locales_dir').'/%s/admin/users');

		$iGroupId = $this->request->attributes->getInt('group_id');

		if (empty($iGroupId)) {
			return $this->serve404();
		}

		# @TODO : write warnings message for editing system groups
		if ($iGroupId === UsersGroups::SUPERADMIN) {
			$this->okt->page->warnings->set(__('SUPERADMIN'));
		}
		elseif ($iGroupId === UsersGroups::ADMIN) {
			$this->okt->page->warnings->set(__('ADMIN'));
		}
		elseif ($iGroupId === UsersGroups::GUEST) {
			$this->okt->page->warnings->set(__('GUEST'));
		}

		$rsGroup = $this->okt->getGroups()->getGroup($iGroupId);
		$rsGroupL10n = $this->okt->getGroups()->getGroupL10n($iGroupId);

		$aGroupData = new ArrayObject();

		$aGroupData['locales'] = array();

		while ($rsGroupL10n->fetch())
		{
			if (isset($this->okt->languages->list[$rsGroupL10n->language]))
			{
				$aGroupData['locales'][$rsGroupL10n->language] = array();
				$aGroupData['locales'][$rsGroupL10n->language]['title'] = $rsGroupL10n->title;
			}
		}

		$aGroupData['perms'] = $rsGroup->perms ? json_decode($rsGroup->perms) : array();

		if ($this->okt->request->request->has('form_sent'))
		{
			foreach ($this->okt->languages->list as $aLanguage)
			{
				$aGroupData['locales'][$aLanguage['code']]['title'] = $this->request->request->get('p_title['.$aLanguage['code'].']', '', true);

				if (empty($aGroupData['locales'][$aLanguage['code']]['title']))
				{
					if ($this->okt->languages->unique) {
						$this->okt->error->set(__('c_a_users_must_enter_group_title'));
					}
					else {
						$this->okt->error->set(sprintf(__('c_a_users_must_enter_group_title_in_%s'), $aLanguage['title']));
					}
				}
			}

			if ($this->okt->request->request->has('perms')) {
				$aGroupData['perms'] = array_keys($this->okt->request->request->get('perms'));
			}

			if ($this->okt->error->isEmpty())
			{
				if ($this->okt->getGroups()->updGroup($iGroupId, $aGroupData))
				{
					$this->okt->page->flash->success(__('c_a_users_group_edited'));

					return $this->redirect($this->generateUrl('Users_groups_edit', array('group_id' => $iGroupId)));
				}
			}
		}

		return $this->render('Users/Groups/Edit', array(
			'iGroupId'       => $iGroupId,
			'aGroupData'     => $aGroupData,
			'aPermissions'   => $this->okt->getPermsForDisplay()
		));
	}
}
