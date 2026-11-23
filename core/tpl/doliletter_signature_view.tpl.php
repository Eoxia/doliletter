<?php
// Decided on the stored value, not on a translated text: an image is shown as such, a value that holds no image
// (the text DocumentGenerated that older versions wrote once a document was generated, in the language of the one
// who generated it) shows the status of the signatory, never a broken image
$signatureDataUrl = DoliletterSignature::getSignatureDataUrl($element->signature ?? null);

// $element is null while the role has no signatory yet (draft envelope): the button stays, signing creates the signatory
if (empty($element->signature)) : ?>
	<div class="wpeo-button button-blue wpeo-modal-event modal-signature-open modal-open" value="<?php echo ($element->id ?? '') ?>">
		<span><i class="fas fa-signature"></i> <?php echo $langs->trans('Sign'); ?></span>
	</div>

<?php elseif ($signatureDataUrl === '') :
	print $element->getLibStatut(5);
else : ?>
	<img class="wpeo-modal-event modal-open doliletter-signature-image" value="<?php echo ($element->id ?? '') ?>" src="<?php echo $signatureDataUrl ?>" width="100px" height="100px">
<?php endif; ?>

<div class="modal-signature" value="<?php echo ($element->id ?? '') ?>">
	<div class="wpeo-modal modal-signature" id="modal-signature<?php echo ($element->id ?? '') ?>">
		<div class="modal-container wpeo-modal-event">
			<!-- Modal-Header-->
			<div class="modal-header">
				<h2 class="modal-title"><?php echo $langs->trans('Signature'); ?></h2>
				<div class="modal-close"><i class="fas fa-times"></i></div>
			</div>
			<!-- Modal-ADD Signature Content-->
			<div class="modal-content" id="#modalContent">
				<?php // Image the signature pad starts from: empty when the stored value holds none ?>
				<input type="hidden" id="signature_data<?php echo ($element->id ?? '') ?>" value="<?php echo $signatureDataUrl ?>">
				<canvas class="doliletter-signature-canvas"></canvas>
			</div>
			<!-- Modal-Footer-->
			<div class="modal-footer">
				<div class="signature-erase wpeo-button button-grey">
					<span><i class="fas fa-eraser"></i> <?php echo $langs->trans('Erase'); ?></span>
				</div>
				<div class="wpeo-button button-grey modal-close">
					<span><?php echo $langs->trans('Cancel'); ?></span>
				</div>
				<div class="signature-validate wpeo-button button-primary" value="<?php echo ($element->id ?? '') ?>" data-error-message="<?php echo dol_escape_htmltag($langs->transnoentities('ErrorSignatureNotSaved')) ?>">
					<input type="hidden" id="redirect<?php echo ($element->id ?? '') ?>" value="<?php echo $url ?>">
					<input type="hidden" id="zone<?php echo ($element->id ?? '') ?>" value="<?php echo $zone ?>">
					<span><?php echo $langs->trans('Validate'); ?></span>
				</div>
			</div>
		</div>
	</div>
</div>




