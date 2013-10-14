
<?php # début Okatea : ce template étend le layout
$this->extend('layout');
# fin Okatea : ce template étend le layout ?>


<?php # début Okatea : ajout du CHEMIN du fichier LESS
$okt->page->css->addLessFile(__DIR__.'/styles.less');
# fin Okatea : ajout du CHEMIN du fichier LESS ?>


<?php # début Okatea : ajout de jQuery
$okt->page->js->addFile(OKT_PUBLIC_URL.'/js/jquery/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php # début Okatea : ajout de jQuery UI
$okt->page->js->addFile(OKT_PUBLIC_URL.'/js/jquery/ui/jquery-ui.min.js');
$okt->page->css->addFile(OKT_PUBLIC_URL.'/ui-themes/'.$okt->config->public_theme.'/jquery-ui.css');
# fin Okatea : ajout de jQuery UI ?>


<?php # début Okatea : ajout du datepicker
$okt->page->datePicker();
# fin Okatea : ajout du datepicker ?>


<?php

$okt->page->js->addScript('
	var accessories = '.json_encode($aProductsAccessories).';
');

$okt->page->js->addReady('
	$(".spinner").spinner({ min: 0 });

	$(".product_choice").change(function(){

		var product_counter = $(this).attr("id").match(/[\d]+$/);
		var product_id = parseInt($(this).val());
		
		var accessories_selects = $(".accessories_" + product_counter);

		if (product_id > 0 && accessories[product_id] != undefined) {

			$(accessories_selects).empty();

			$.each(accessories[product_id], function(value, key) {

				$.each(accessories_selects, function(){
					$(this).append($("<option></option>")
					.attr("value",key)
					.text(value));
				});
			});
		}
	});
');
?>

<!-- <h1><?php echo html::escapeHTML($okt->estimate->getName()) ?></h1> -->


<?php # début Okatea : affichage des éventuelles erreurs
if ($okt->error->notEmpty()) : ?>
	<div class="error_box">
		<?php echo $okt->error->get(); ?>
	</div>
<?php endif; # fin Okatea : affichage des éventuelles erreurs ?>


<form id="estimate-form" action="<?php echo html::escapeHTML($okt->estimate->config->url) ?>" method="post">

	<fieldset>
		<legend>Vous concernant</legend>

		<p class="infos">Merci d'indiquer les informations vous concernant afin que nous puissions vous répondre dans les plus bref délais.</p>

		<div class="two-cols">
			<p class="field col"><label for="p_lastname" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_Last_name') ?></label>
			<?php echo form::text('p_lastname', 40, 255, html::escapeHTML($aFormData['lastname'])) ?></p>

			<p class="field col"><label for="p_firstname" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_First_name') ?></label>
			<?php echo form::text('p_firstname', 40, 255, html::escapeHTML($aFormData['firstname'])) ?></p>
		</div>

		<div class="two-cols">
			<p class="field col"><label for="p_email" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_Email') ?></label>
			<?php echo form::text('p_email', 40, 255, html::escapeHTML($aFormData['email'])) ?></p>

			<p class="field col"><label for="p_phone" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_Phone') ?></label>
			<?php echo form::text('p_phone', 40, 255, html::escapeHTML($aFormData['phone'])) ?></p>
		</div>

	</fieldset>

	<fieldset>
		<legend>Dates prévisionelles</legend>

		<p class="infos">Merci d'indiquer les dates pendant lesquelles vous souhaitez louer le matériel.</p>

		<div class="two-cols">
			<p class="field col"><label for="p_start_date" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_estimate_form_start_date') ?></label>
			<?php echo form::text('p_start_date', 40, 255, html::escapeHTML($aFormData['start_date']), 'datepicker') ?></p>

			<p class="field col"><label for="p_end_date" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_estimate_form_end_date') ?></label>
			<?php echo form::text('p_end_date', 40, 255, html::escapeHTML($aFormData['end_date']), 'datepicker') ?></p>
		</div>
	</fieldset>

	<fieldset>
		<legend>Choix des produits et des accessoires</legend>

		<p class="infos">Veuillez choisir les matériels pour lequel porte ce devis. Vous pouvez ajouter des accessoires pour chacun des matériels.</p>

		<?php for ($i=1; $i<=3; $i++) : ?>

		<fieldset class="product-line">

			<p class="field product"><label for="p_product_<?php echo $i ?>"><?php printf(__('m_estimate_form_product_%s'), $i) ?></label>
			<?php echo form::select(array('p_product['.$i.']', 'p_product_'.$i), $aProductsSelect, '', 'product_choice') ?></p>

			<p class="field quantity"><label for="p_product_quantity_<?php echo $i ?>"><?php _e('m_estimate_form_quantity') ?></label>
			<?php echo form::text(array('p_product_quantity['.$i.']', 'p_product_quantity_'.$i), 10, 255, '', 'spinner') ?></p>

			<div class="accessories">
				<?php for ($j=1; $j<=2; $j++) : ?>
				<p class="field accessory"><label for="p_accessory_<?php echo $i ?>_<?php echo $j ?>"><?php printf(__('m_estimate_form_accessory_%s'), $j) ?></label>
				<?php echo form::select(array('p_accessory['.$i.']['.$j.']', 'p_accessory_'.$i.'_'.$j), array(), '', 'accessories_'.$i) ?></p>
	
				<p class="field quantity"><label for="p_accessory_quantity_<?php echo $i ?>_<?php echo $j ?>"><?php _e('m_estimate_form_quantity') ?></label>
				<?php echo form::text(array('p_accessory_quantity['.$i.']['.$j.']', 'p_accessory_quantity_'.$i.'_'.$j), 10, 255, '', 'spinner') ?></p>
				<?php endfor; ?>
				<p class="add_accessory_wrapper accessory"><a href="#" class="add_accessory_link"><?php _e('m_estimate_form_add_accessory') ?></a></p>
			</div>
		</fieldset>

		<?php endfor; ?>

		<?php debug($aProductsAccessories) ?>

	</fieldset>

	<p class="submit-wrapper"><input type="submit" value="<?php _e('m_estimate_send') ?>" name="sended" id="submit-estimate-form" /></p>
</form>
