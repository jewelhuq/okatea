<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * La page d'identification au backend
 *
 */

use Tao\Admin\Page;
use Tao\Forms\Statics\FormElements as form;

# no menu on admin login page
define('OKT_DISABLE_MENU', true);

# no admin check on admin login page
define('OKT_SKIP_USER_ADMIN_CHECK', true);

# no CSRF token on admin login page
define('OKT_SKIP_CSRF_CONFIRM', true);

require __DIR__.'/../oktInc/admin/prepend.php';

$okt->page->pageId('connexion');

$okt->page->breadcrumb->reset();


# déjà logué
if (!$okt->user->is_guest) {
	http::redirect('index.php');
}


# Mot de passe oublié
if ($okt->page->action == 'validate_password' && $okt->request->query->has('key') && $okt->request->query->has('uid'))
{
	if ($okt->user->validatePasswordKey($okt->request->query->getInt('key'), $okt->request->query->get('key')))
	{
		$okt->page->addGlobalTitle(__('c_c_auth_request_password'));
		require OKT_ADMIN_HEADER_FILE; ?>

		<p><?php _e('c_c_auth_password_updated') ?></p>
		<p><a href="<?php echo OKT_ADMIN_LOGIN_PAGE ?>"><?php _e('c_c_auth_login') ?></a></p>

		<?php # Pied-de-page
		require OKT_ADMIN_FOOTER_FILE;
		exit;
	}
}
elseif ($okt->page->action == 'forget' || $okt->page->action == 'forget_2')
{
	if ($okt->request->request->has('email'))
	{
		if ($okt->user->forgetPassword($okt->request->request->filter('email', null, false, FILTER_SANITIZE_EMAIL), $okt->request->getSchemeAndHttpHost().$okt->request->getBaseUrl()))
		{
			$okt->page->addGlobalTitle(__('c_c_auth_request_password'));
			require OKT_ADMIN_HEADER_FILE; ?>

			<p><?php _e('c_c_auth_email_sent_with_instructions') ?></p>
			<p><a href="<?php echo OKT_ADMIN_LOGIN_PAGE ?>"><?php _e('c_c_auth_login') ?></a></p>

			<?php # Pied-de-page
			require OKT_ADMIN_FOOTER_FILE;
			exit;
		}
	}

	# sinon affichage du formulaire de demande de mot de passe
	$okt->page->addGlobalTitle(__('c_c_auth_request_password'));
	require OKT_ADMIN_HEADER_FILE; ?>

	<form action="<?php echo OKT_ADMIN_LOGIN_PAGE ?>?action=forget_2" method="post">
		<p class="field"><label for="email"><?php _e('c_c_auth_give_account_email') ?></label>
		<?php echo form::text('email', 30, 255) ?></p>
		<p class="note"><?php _e('c_c_auth_new_password_link_activate_will_be_sent') ?></p>

		<p><?php //echo Page::formtoken(); ?>
		<input type="hidden" name="form_sent" value="1" />
		<input type="submit" value="<?php _e('c_c_action_Send') ?>" />
		<a href="<?php echo OKT_ADMIN_LOGIN_PAGE ?>"><?php _e('c_c_action_Go_back') ?></a></p>
	</form>

	<?php # Pied-de-page
	require OKT_ADMIN_FOOTER_FILE;
	exit;
}


# identification
$sUserId = $okt->request->request->get('user_id', $okt->request->query->get('user_id'));
$sUserPwd = $okt->request->request->get('user_pwd', $okt->request->query->get('user_pwd'));

if (!empty($sUserId) && !empty($sUserPwd))
{
	$bUserRemember = $okt->request->request->has('user_remember') ? true : false;

	if ($okt->user->login($sUserId, $sUserPwd, $bUserRemember))
	{
		$redir = 'index.php';

		if ($okt->request->cookies->has(OKT_COOKIE_AUTH_FROM))
		{
			if ($okt->request->cookies->get(OKT_COOKIE_AUTH_FROM) != $okt->request->getUri()) {
				$redir = $okt->request->cookies->get(OKT_COOKIE_AUTH_FROM);
			}

			$okt->user->setAuthFromCookie('', 0);
		}

		http::redirect($redir);
	}
}


# Titre de la page
$okt->page->addGlobalTitle(__('c_c_auth_login'));

$okt->page->js->addReady('
	$("#user_id").focus();
');


require OKT_ADMIN_HEADER_FILE; ?>

<form action="<?php echo OKT_ADMIN_LOGIN_PAGE ?>" method="post">

	<p class="field"><label for="user_id" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_user_Username') ?></label>
	<?php echo form::text('user_id', 30, 255, $sUserId) ?></p>

	<p class="field"><label for="user_pwd" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_user_Password') ?></label>
	<?php echo form::password('user_pwd', 30, 255, '') ?></p>

	<p><?php echo form::checkbox('user_remember', 1) ?>
	<label class="inline" for="user_remember"><?php _e('c_c_auth_remember_me') ?></label></p>

	<p><?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_auth_login_action') ?>" /></p>

	<p class="note"><?php _e('c_c_auth_must_accept_cookies_private_area') ?></p>

	<p><a href="<?php echo OKT_ADMIN_LOGIN_PAGE ?>?action=forget"><?php _e('c_c_auth_forgot_password') ?></a></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
