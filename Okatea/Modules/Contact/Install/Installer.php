<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Contact\Install;

use Okatea\Tao\Extensions\Modules\Manage\Installer as BaseInstaller;

class Installer extends BaseInstaller
{

	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'contact_usage',
			'contact_recipients'
		));
	}

	public function update()
	{
	}
}
