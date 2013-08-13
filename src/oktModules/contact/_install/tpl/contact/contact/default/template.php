
<?php # début Okatea : ce template étend le layout
$this->extend('layout');
# fin Okatea : ce template étend le layout ?>


<?php # début Okatea : ajout du CHEMIN du fichier LESS
$okt->page->css->addLessFile(dirname(__FILE__).'/styles.less');
# fin Okatea : ajout du CHEMIN du fichier LESS ?>


<?php # début Okatea : ajout de jQuery
$okt->page->js->addFile(OKT_COMMON_URL.'/js/jquery/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php # début Okatea : liste des champs obligatoires pour la validation JS
$aJsValidateRules = new ArrayObject;
while ($okt->contact->rsFields->fetch())
{
	if ($okt->contact->rsFields->active == 2) {
		$aJsValidateRules[] = $okt->contact->rsFields->html_id.': { required: true }';
	}
}
# fin Okatea : liste des champs obligatoires pour la validation JS ?>


<?php # -- CORE TRIGGER : publicModuleContactJsValidateRules
$okt->triggers->callTrigger('publicModuleContactJsValidateRules', $okt, $aJsValidateRules, $okt->contact->config->captcha); ?>


<?php # début Okatea : validation JS
if (!empty($aJsValidateRules))
{
	$okt->page->validateForm();
	$okt->page->js->addReady("
		var contactValidator = $('#contact-form').validate({
			rules: {
				".implode(',',(array)$aJsValidateRules)."
			}
		});
	");
}
# fin Okatea : validation JS ?>


<?php # -- CORE TRIGGER : publicModuleContactBeforeDisplayPage
$okt->triggers->callTrigger('publicModuleContactBeforeDisplayPage', $okt); ?>


<?php # début Okatea : nécessaire pour la google map
if ($okt->contact->config->google_map['enable'] && $okt->contact->config->google_map['display'] != 'other_page') : ?>

	<?php # début Okatea : Google Maps API
	$okt->page->js->addFile('http://maps.google.com/maps/api/js?sensor=false');
	# fin Okatea : Google Maps API ?>

	<?php # début Okatea : ajout du plugin Gmap3
	$okt->page->js->addFile(OKT_COMMON_URL .'/js/jquery/gmap3/gmap3.min.js');
	# fin Okatea : ajout du plugin Gmap3 ?>

	<?php # début Okatea : Gmap3 loader
	$sJsGmap3Loader =
	'$("#google_map").gmap3({
		map: {
			address: "'.html::escapeJS($okt->contact->getAdressForGmap()).'",
			options: {
				center: true,
				zoom: '.html::escapeJS($okt->contact->config->google_map['options']['zoom']).',
				mapTypeId: google.maps.MapTypeId.'.html::escapeJS($okt->contact->config->google_map['options']['mode']).'
			}
		},
		infowindow:{
			address: "'.html::escapeJS($okt->contact->getAdressForGmap()).'",
			options: {
				content: "<div id=\"infobulle\"><strong>'.html::escapeJS((!empty($okt->config->company['com_name']) ? $okt->config->company['com_name'] : $okt->config->company['name'])).'</strong><br/> '.
					html::escapeJS($okt->config->address['street']).'<br/> '.
					($okt->config->address['street_2'] != '' ? html::escapeJS($okt->config->address['street_2']).'<br/>' : '').
					html::escapeJS($okt->config->address['code']).' '.html::escapeJS($okt->config->address['city']).'<br/> '.
					html::escapeJS($okt->config->address['country']).'</div>"
			}
		}
	});';
	# fin Okatea : Gmap3 loader ?>

	<?php # début Okatea : affichage du plan dans la page
	if ($okt->contact->config->google_map['display'] == 'inside') :
		$okt->page->js->addReady($sJsGmap3Loader);
	endif; # fin Okatea : affichage du plan dans la page ?>

	<?php # début Okatea : affichage du plan dans UI dialog
	if ($okt->contact->config->google_map['display'] == 'link') :

		# ajout jQuery UI
		$okt->page->js->addFile(OKT_COMMON_URL.'/js/jquery/ui/jquery-ui.min.js');
		$okt->page->css->addFile(OKT_COMMON_URL.'/ui-themes/'.$okt->config->public_theme.'/jquery-ui.css');

		$okt->page->js->addReady('
			$("#google_map").dialog({
				autoOpen: false,
				hide: "fade",
				show: "fade",
				title: "Google map",
				width: 700,
				height: 500,
				dialogClass: "google_map_added_class"
			});

			$("#google_map").hide();

			$("#google_map_link").click(function(e){
				$("#google_map").dialog("open");
				e.preventDefault();
				'.$sJsGmap3Loader.'
			});
		');

	endif; # fin Okatea : affichage du plan dans UI dialog ?>

<?php endif; # fin Okatea : nécessaire pour la google map ?>


<?php # début Okatea : on ajoutent des éléments à l'en-tête HTML
$this->start('head') ?>

	<?php # début Okatea : on index pas la page contact ?>
	<meta name="robots" content="none" />
	<?php # fin Okatea : on index pas la page contact ?>

<?php $this->stop();
# fin Okatea : on ajoutent des éléments à l'en-tête HTML ?>


<?php # début Okatea : affichage des éventuelles erreurs
if ($okt->error->notEmpty()) : ?>
	<div class="error_box">
		<?php echo $okt->error->get(); ?>
	</div>
<?php endif; # fin Okatea : affichage des éventuelles erreurs ?>


<?php # début Okatea : si le mail est envoyé on affiche une confirmation
if (!empty($_GET['sended'])) : ?>
<p><?php _e('m_contact_success'); ?></p>
<?php endif; # fin Okatea : si le mail est envoyé on affiche une confirmation ?>


<?php # début Okatea : si le mail n'est PAS envoyé on affiche le formulaire
if (empty($_GET['sended'])) : ?>
<form action="<?php echo html::escapeHTML($okt->contact->config->url) ?>" method="post" id="contact-form">

	<?php # début Okatea : boucle sur les champs
	while ($okt->contact->rsFields->fetch()) : ?>

		<?php echo $okt->contact->rsFields->getHtmlField() ?>

	<?php endwhile; # fin Okatea : boucle sur les champs ?>

	<?php # -- CORE TRIGGER : publicModuleContactTplFormBottom
	$okt->triggers->callTrigger('publicModuleContactTplFormBottom', $okt, $okt->contact->config->captcha); ?>

	<p class="submit-wrapper"><input type="submit" value="<?php _e('m_contact_send'); ?>" name="send" id="submit-contact-form" /></p>

</form>

<div id="coordonnees">
	<?php # début Okatea : affichage du nom commercial ou de la raison sociale ?>
	<p><strong>
		<?php # début Okatea : si on as un nom commercial (ou une raison sociale), on l'affiche
		if (!empty($okt->config->company['com_name'])) : ?>
			<?php echo html::escapeHTML($okt->config->company['com_name']) ?>
		<?php elseif (!empty($okt->config->company['name'])) : ?>
			<?php echo html::escapeHTML($okt->config->company['name']) ?>
		<?php endif;  # fin Okatea : si on as un nom commercial (ou une raison sociale), on l'affiche ?>
	</strong></p>
	<?php # fin Okatea : affichage du nom commercial ou de la raison sociale ?>

	<?php # début Okatea : affichage de l'adresse ?>
	<p><?php echo html::escapeHTML($okt->config->address['street'].' '.
	($okt->config->address['street_2'] != '' ? ' - '.$okt->config->address['street_2'] : '').
	' - '.$okt->config->address['code'].' '.$okt->config->address['city'].' '.$okt->config->address['country']); ?></p>
	<?php # fin Okatea : affichage de l'adresse ?>

	<?php # début Okatea : affichage du numéro de téléphone
	if (!empty($okt->config->address['tel'])) : ?>
	<p><span class="label"><abbr title="<?php _e('c_c_Phone') ?>"><?php _e('m_contact_tel') ?></abbr> :</span> <?php echo html::escapeHTML($okt->config->address['tel']) ?></p>
	<?php endif; # fin Okatea : affichage du numéro de téléphone ?>

	<?php # début Okatea : affichage du numéro de mobile
	if (!empty($okt->config->address['mobile'])) : ?>
	<p><span class="label"><?php _e('m_contact_mobile') ?> :</span> <?php echo html::escapeHTML($okt->config->address['mobile']) ?></p>
	<?php endif; # fin Okatea : affichage du numéro de mobile ?>

	<?php # début Okatea : affichage du numéro de fax
	if (!empty($okt->config->address['fax'])) : ?>
	<p><span class="label"><?php _e('m_contact_fax') ?> :</span> <?php echo html::escapeHTML($okt->config->address['fax']) ?></p>
	<?php endif; # fin Okatea : affichage du numéro de fax ?>

	<?php # début Okatea : affichage de l'image de l'email ?>
	<p><strong><?php _e('m_contact_email'); ?> : </strong><img src="<?php echo $okt->contact->genImgMail(); ?>" alt="E-mail <?php echo html::escapeHTML($okt->config->company['name']) ?>"></p>
	<?php # fin Okatea : affichage de l'image de l'email ?>
</div>

<?php # début Okatea : affichage du template de la map si le plan est à afficher dans la page contact et si le plan d'accès est activé
if ($okt->contact->config->google_map['enable'] && $okt->contact->config->google_map['display'] != 'other_page') : ?>

	<?php # début Okatea : lien pour afficher le plan dans UI dialog
	if ($okt->contact->config->google_map['display'] == 'link') : ?>
	<p id="google_map_link_wrapper"><a id="google_map_link" href="#google_map"><?php _e('m_contact_google_map_link') ?></a></p>
	<?php endif; # fin Okatea : lien pour afficher le plan dans UI dialog ?>


	<?php # début Okatea : élément HTML qui recevra le plan ?>
	<div id="google_map"></div>
	<?php # fin Okatea : élément HTML qui recevra le plan ?>

<?php endif; # fin Okatea : affichage du template de la map si le plan est à afficher dans la page contact et si le plan d'accès est activé ?>

<?php endif; # fin Okatea : si le mail n'est PAS envoyé on affiche le formulaire ?>
