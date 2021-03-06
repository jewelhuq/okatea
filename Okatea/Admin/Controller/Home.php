<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Admin\Controller;

use ArrayObject;
use Okatea\Admin\Controller;
use Okatea\Tao\Update as Updater;
use Symfony\Component\Filesystem\Filesystem;

class Home extends Controller
{
	protected $sNewVersion;

	protected $aRoundAboutItems;

	protected $bFeedSuccess = false;

	protected $feed;

	public function homePage()
	{
		$this->roundAbout();

		$this->konami();

		$this->newsFeed();

		$this->updateNotification();

		if ($this->okt['debug']) {
			$this->okt['instantMessages']->warning(__('c_a_public_debug_mode_enabled'));
		}

		return $this->render('Home', [
			'sNewVersion'        => $this->sNewVersion,
			'bFeedSuccess'       => $this->bFeedSuccess,
			'feed'               => $this->feed,
			'aRoundAboutItems'   => (array) $this->aRoundAboutItems
		]);
	}

	protected function roundAbout()
	{
		$aRoundAboutOptions = new ArrayObject();
		$aRoundAboutOptions['tilt'] = 4;
		$aRoundAboutOptions['easing'] = 'easeOutElastic';
		$aRoundAboutOptions['duration'] = 1400;

		$this->page->css->addCss('
			#roundabout img {
				display: block;
				margin: 0 auto;
			}
			.roundabout-holder {
				list-style: none;
				width: 75%;
				height: 15em;
				margin: 1em auto;
			}
			.roundabout-moveable-item {
				height: 4em;
				width: 8em;
				font-size: 2em;
				text-align: center;
				cursor: pointer;
			}
			.roundabout-moveable-item a {
				text-decoration: none;
			}
			.roundabout-moveable-item a:focus {
				outline: none;
			}
			.roundabout-in-focus {
				cursor: auto;
			}
		');

		# -- CORE TRIGGER : adminIndexRoundaboutOptions
		$this->okt['triggers']->callTrigger('adminIndexRoundaboutOptions', $aRoundAboutOptions);

		$this->page->roundabout((array) $aRoundAboutOptions, '#roundabout');

		# RoundAbout defaults Items
		$this->aRoundAboutItems = new ArrayObject();

		$sRoundAboutItemFormat = '<a href="%2$s">%3$s<span>%1$s</span></a>';

		foreach ($this->page->mainMenu->getItems() as $item)
		{
			$this->aRoundAboutItems[] = sprintf($sRoundAboutItemFormat, $item['title'], $item['url'], ($item['icon'] ? '<img src="' . $item['icon'] . '" alt="" />' : ''));
		}

		$this->aRoundAboutItems[] = sprintf($sRoundAboutItemFormat, __('c_c_user_profile'), $this->okt['adminRouter']->generate('User_profile'), '<img src="' . $this->okt['public_url'] . '/img/admin/contact-new.png" alt="" />');

		$this->aRoundAboutItems[] = sprintf($sRoundAboutItemFormat, __('c_c_user_Log_off_action'), $this->okt['adminRouter']->generate('logout'), '<img src="' . $this->okt['public_url'] . '/img/admin/system-log-out.png" alt="" />');

		# -- CORE TRIGGER : adminIndexaRoundAboutItems
		$this->okt['triggers']->callTrigger('adminIndexaRoundAboutItems', $this->aRoundAboutItems);
	}

	protected function konami()
	{
		$this->page->js->addScript('
			if (window.addEventListener) {
				var kkeys = [], konami = "38,38,40,40,37,39,37,39,66,65";
				window.addEventListener("keydown", function(e){
					kkeys.push(e.keyCode);
					if (kkeys.toString().indexOf( konami ) >= 0) {
						window.location = "http://okatea.org/";
					}
				}, true);
			}
		');
	}

	protected function newsFeed()
	{
		if (!$this->okt['config']->news_feed['enabled']
			|| empty($this->okt['config']->news_feed['url'][$this->okt['visitor']->language]))
		{
			return null;
		}

		# We'll process this feed with all of the default options.
		$this->feed = new \SimplePie();

		# set cache directory
		$sCacheDir = $this->okt['cache_path'] . '/feeds/';

		(new Filesystem())->mkdir($sCacheDir);

		$this->feed->set_cache_location($sCacheDir);

		# Set which feed to process
		$this->feed->set_feed_url($this->okt['config']->news_feed['url'][$this->okt['visitor']->language]);

		# Run SimplePie
		$this->bFeedSuccess = $this->feed->init();

		# This makes sure that the content is sent to the browser
		# as text/html and the UTF-8 character set (since we didn't change it).
		$this->feed->handle_content_type();

		$this->page->css->addCss('
			#news_feed_list {
				height: 13em;
				width: 28%;
				overflow-y: scroll;
				overflow-x: hidden;
				padding-right: 0.8em;
				float: right;
			}
			#news_feed_list .ui-widget-header a {
				text-decoration: none;
			}
			#news_feed_list .ui-widget-header {
				margin-bottom: 0;
				padding: 0.3em 0.5em;
			}
			#news_feed_list .ui-widget-content {
				padding: 0.5em;
			}

			#roundabout-wrapper {
				float: left;
				width: 70%;
			}
		');
	}

	protected function updateNotification()
	{
		if ($this->okt['config']->updates['enabled'] && $this->okt['visitor']->checkPerm('is_superadmin') && is_readable($this->okt['digests_path']))
		{
			$updater = new Updater(
				$this->okt['config']->updates['url'],
				'okatea',
				$this->okt['config']->updates['type'],
				$this->okt['cache_path'] . '/versions'
			);

			$this->sNewVersion = $updater->check($this->okt->getVersion());

			if ($updater->getNotify() && $this->sNewVersion) {
				$this->okt['l10n']->loadFile($this->okt['locales_path'] . '/%s/admin/update');
			}
		}
	}
}
