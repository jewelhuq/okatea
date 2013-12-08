<?php
/**
 * @ingroup okt_module_diary
 * @brief Controller public.
 *
 */

use Tao\Misc\Utilities as util;
use Tao\Core\Controller;

class diaryController extends Controller
{
	/**
	 * Affichage du calendrier
	 *
	 */
	public function diaryList($aMatches)
	{
		# module actuel
		$this->okt->page->module = 'diary';
		$this->okt->page->action = 'list';

		# année et mois à afficher ?
		$iYear = !empty($_GET['year']) ? intval($_GET['year']) : null;
		$iMonth = !empty($_GET['month']) ? intval($_GET['month']) : null;

		if (!empty($aMatches[0]))
		{
			$aDate = explode('/',$aMatches[0]);

			$iYear = !empty($aDate[0]) ? intval($aDate[0]) : null;
			$iMonth = !empty($aDate[1]) ? intval($aDate[1]) : null;

			unset($aDate);
		}

		# initialisation calendrier
		$oCal = new diaryMonthlyCalendar(
			array(
				'htmlBlock' => '<table id="diary" class="common calendar" summary="'.__('agenda').'">%s</table>',

				'htmlNavigation' 		=>
				'<caption>%2$s - <strong>%1$s</strong> - %3$s</caption>
				<colgroup>
				<col width="14%%"></col>
				<col width="14%%"></col>
				<col width="14%%"></col>
				<col width="14%%"></col>
				<col width="14%%"></col>
				<col width="14%%"></col>
				<col width="14%%"></col>
				</colgroup>',

				'htmlPrevLink' 		=> '<a id="diary-prev-link" href="%1$s">&laquo; %2$s</a>',
				'htmlNextLink' 		=> '<a id="diary-next-link" href="%1$s">%2$s &raquo;</a>',

				'htmlHeadCelContent' => '%2$s',

				'htmlBodyCelContent' => '<h4 class="number">%1$s</h4>',

				'htmlClassActive'	=> 'active',
				'htmlClassDisabled'	=> 'disabled',

				'htmlEventsList' => '<ul class="events-list">%s</ul>',
				'htmlEventItem'  => '<li class="%3$s"%4$s><a href="%2$s">%1$s</a></li>',

				'urlBase' 		=> $this->okt->diary->config->url,
				'urlPattern' 	=> '/%s/%s'
			),
			$iYear,
			$iMonth
		);

		# récupération des évènements pour le mois affiché par le calendrier
		$aDatesEvents = $this->okt->diary->getDatesEventsByInterval(
			$oCal->getStartDate(),
			$oCal->getEndDate(),
			1
		);
		$oCal->setDatesEvents($aDatesEvents);

		# meta description
		if ($this->okt->diary->config->meta_description[$this->okt->user->language] != '') {
			$this->okt->page->meta_description = $this->okt->diary->config->meta_description[$this->okt->user->language];
		}
		else {
			$this->okt->page->meta_description = util::getSiteMetaDesc();
		}

		# meta keywords
		if ($this->okt->diary->config->meta_keywords[$this->okt->user->language] != '') {
			$this->okt->page->meta_keywords = $this->okt->diary->config->meta_keywords[$this->okt->user->language];
		}
		else {
			$this->okt->page->meta_keywords = util::getSiteMetaKeywords();
		}

		# fil d'ariane
		if (!$this->isDefaultRoute(__CLASS__, __FUNCTION__)) {
			$this->okt->page->breadcrumb->add($this->okt->diary->getName(), $this->okt->diary->config->url);
		}

		# title tag du module
		$this->okt->page->addTitleTag($this->okt->diary->getTitle());

		# titre de la page
		$this->okt->page->setTitle($this->okt->diary->getName());

		# titre SEO de la page
		$this->okt->page->setTitleSeo($this->okt->diary->getNameSeo());


		# affichage du template
		echo $this->okt->tpl->render('diary_list_tpl', array(
			'oCal' => $oCal
		));
	}

	/**
	 * Affichage d'un évènement
	 *
	 */
	public function diaryEvent($aMatches)
	{
		# récupération de l'élément en fonction du slug
		if (!empty($aMatches[0])) {
			$slug = $aMatches[0];
		}
		else {
			$this->serve404();
		}

		# récupération de l'évènement
		$rsEvent = $this->okt->diary->getEvents(array(
			'slug' => $slug,
			'visibility' => 1
		));

		if ($rsEvent->isEmpty()) {
			$this->serve404();
		}

		# module actuel
		$this->okt->page->module = 'diary';
		$this->okt->page->action = 'event';


		# meta description
		if ($rsEvent->meta_description != '') {
			$this->okt->page->meta_description = $rsEvent->meta_description;
		}
		else if ($this->okt->diary->config->meta_description[$this->okt->user->language] != '') {
			$this->okt->page->meta_description = $this->okt->diary->config->meta_description[$this->okt->user->language];
		}
		else {
			$this->okt->page->meta_description = util::getSiteMetaDesc();
		}

		# meta keywords
		if ($rsEvent->meta_keywords != '') {
			$this->okt->page->meta_keywords = $rsEvent->meta_keywords;
		}
		else if ($this->okt->diary->config->meta_keywords[$this->okt->user->language] != '') {
			$this->okt->page->meta_keywords = $this->okt->diary->config->meta_keywords[$this->okt->user->language];
		}
		else {
			$this->okt->page->meta_keywords = util::getSiteMetaKeywords();
		}

		# description
		if (!$this->okt->diary->config->enable_rte) {
			$rsEvent->description = util::nlToP($rsEvent->description);
		}

		# récupération des images
		$rsEvent->images = $rsEvent->getImagesInfo();

		# récupération des fichiers
		$rsEvent->files = $rsEvent->getFilesInfo();

		# title tag du module
		$this->okt->page->addTitleTag($this->okt->diary->getTitle());

		# fil d'ariane
		if (!$this->isDefaultRoute(__CLASS__, __FUNCTION__, $slug))
		{
			$this->okt->page->breadcrumb->add($this->okt->diary->getName(), $this->okt->diary->config->url);

			$this->okt->page->breadcrumb->add($rsEvent->title, $rsEvent->getEventUrl());
		}

		# title tag
		$this->okt->page->addTitleTag(($rsEvent->title_tag != '' ? $rsEvent->title_tag : $rsEvent->title));

		# titre de la page
		$this->okt->page->setTitle($rsEvent->title);

		# titre SEO de la page
		$this->okt->page->setTitleSeo(!empty($rsEvent->title_seo) ? $rsEvent->title_seo : $rsEvent->title);

		# affichage du template
		echo $this->okt->tpl->render('diary_event_tpl', array(
			'rsEvent' => $rsEvent
		));
	}

} # class
