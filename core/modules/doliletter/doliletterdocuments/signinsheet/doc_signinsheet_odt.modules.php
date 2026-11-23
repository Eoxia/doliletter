<?php
/* Copyright (C) 2021-2023 EVARISK <technique@evarisk.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 * or see https://www.gnu.org/
 */

/**
 *	\file       core/modules/digiriskdolibarr/digiriskdocuments/informationssharing/doc_informationssharing_odt.modules.php
 *	\ingroup    digiriskdolibarr
 *	\brief      File of class to build ODT documents for digiriskdolibarr
 */

require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/doc.lib.php';

// Load saturne libraries
dol_include_once('/saturne/core/modules/saturne/modules_saturne.php');
// Reading of the stored signatures
dol_include_once('/doliletter/class/dolilettersignature.class.php');

/**
 *	Class to build documents using ODF templates generator
 */
class doc_signinsheet_odt extends SaturneDocumentModel
{
	/**
	 * @var string Module.
	 */
	public string $module = 'doliletter';

	/**
	 * @var string Document type.
	 */
	public string $document_type = 'signinsheet';

	/**
	 * Constructor.
	 *
	 * @param DoliDB $db Database handler.
	 */
	public function __construct(DoliDB $db)
	{
		parent::__construct($db, $this->module, $this->document_type);
	}

	/**
	 * Return description of a module.
	 *
	 * @param  Translate $langs Lang object to use for output.
	 * @return string           Description.
	 */
	public function info(Translate $langs): string
	{
		return parent::info($langs);
	}

	/**
	 * Function to build a document on disk.
	 *
	 * @param  SaturneDocuments $objectDocument  Object source to build document.
	 * @param  Translate        $outputLangs     Lang object to use for output.
	 * @param  string           $srcTemplatePath Full path of source filename for generator using a template file.
	 * @param  int              $hideDetails     Do not show line details.
	 * @param  int              $hideDesc        Do not show desc.
	 * @param  int              $hideRef         Do not show ref.
	 * @param  array            $moreParam       More param (Object/user/etc).
	 * @return int                               1 if OK, <=0 if KO.
	 * @throws Exception
	 */
	public function write_file(SaturneDocuments $objectDocument, Translate $outputLangs, string $srcTemplatePath, int $hideDetails = 0, int $hideDesc = 0, int $hideRef = 0, array $moreParam = []): int
	{
		global $object;

		dol_include_once('/doliletter/lib/doliletter_spread.lib.php');

		$objectsMetadata    = doliletter_spread_get_objects_metadata();

		if (empty($objectsMetadata[$object->object_type]['object'])) {
			return -1;
		}
		$objectsMetadata[$object->object_type]['object']->fetch($object->fk_object);

		$tmpArray = [];

        $previousObjectDocumentElement = $objectDocument->element;
        $objectDocument->element       = $objectDocument->element . '@digiriskdolibarr';
        complete_substitutions_array($tmpArray, $outputLangs, $objectDocument);
        $objectDocument->element = $previousObjectDocumentElement;

		$tmpArray['Label'] 		  = $objectsMetadata[$object->object_type]['object']->ref;

		$tmpArray['note_public'] = $object->note_public;

		$moreParam = self::getMoreParam($objectDocument, $moreParam);

		$moreParam['tmparray']         = $tmpArray;
		$moreParam['hideTemplateName'] = 1;

		// Domains of the {lang_...} labels of the template that Saturne does not load itself: company
		// block (companies) and attendance values (Saturne signatures)
		$outputLangs->loadLangs(['companies', 'signature@saturne']);

		return parent::write_file($objectDocument, $outputLangs, $srcTemplatePath, $hideDetails, $hideDesc, $hideRef, $moreParam);
	}

	public function fillTagsLines(Odf $odfHandler, Translate $outputLangs, array $moreParam): int
	{

		global $object, $langs, $conf;

		$signatory = new SaturneSignature($this->db, $object->module, $object->element);
		$signatories = $signatory->fetchSignatory('', $object->id ?? 0, $object->element);
		if ($signatories <= 0) {
			$signatories = [];
		} elseif (is_array($signatories)) {
			$signatories = current($signatories);
		}

		$foundTagForLines = 1;
        try {
            $listLines = $odfHandler->setSegment('sessiontrainer');
        } catch (OdfExceptionSegmentNotFound $e) {
            // We may arrive here if tags for lines not present into template
            $foundTagForLines = 0;
            $listLines        = '';
            dol_syslog($e->getMessage());
        }

        if ($foundTagForLines) {
			$tmpArray = [
				'sessiontrainer_number'         => '',
				'sessiontrainer_lastname'       => '',
				'sessiontrainer_firstname'      => '',
				'sessiontrainer_attendance'     => '',
				'sessiontrainer_signature'      => '',
				'sessiontrainer_signature_date' => '',
			];

            if (empty($signatories)) {

                static::setTmpArrayVars($tmpArray, $listLines, $outputLangs);
                $odfHandler->mergeSegment($listLines);
                return 0;
            }

			// Temp dir of this module: Saturne empties it once the document is written (it was the one of Digirisk,
			// which does not exist without Digirisk and is never emptied by this generation)
			$multidirOutput = $conf->doliletter->multidir_output;
			$tempdir        = ($multidirOutput[$object->entity ?? $conf->entity] ?? $multidirOutput[$conf->entity] ?? '') . '/temp/';
			dol_mkdir($tempdir);

            foreach ($signatories as $k => $signatory) {

				// Skip the rows still waiting for a user to be picked, as the Diffusion tab does. A person registered
				// from the public link has no user either, but signs like the others
				if (empty($signatory->element_id) && $signatory->element_type != DOLILETTER_SPREAD_EXTERNAL_ELEMENT_TYPE) {
					continue;
				}

				// Data URL or bare base64: a value that holds no image reads as no signature, not as an invalid file
				$signatureFile = DoliletterSignature::writeSignatureImage($signatory->signature, $tempdir . 'signature' . $k . '.png');
				$tmpArray['sessiontrainer_signature'] = $signatureFile !== '' ? $signatureFile : $langs->trans('NoSignature');
				$tmpArray['sessiontrainer_number']     = $k;
				$tmpArray['sessiontrainer_lastname']   = $signatory->lastname;
				$tmpArray['sessiontrainer_firstname']  = $signatory->firstname;
				// Same values as the PDF model: a late person is no longer reported absent
				$attendanceKeys = [SaturneSignature::ATTENDANCE_PRESENT => 'Present', SaturneSignature::ATTENDANCE_DELAY => 'Delay', SaturneSignature::ATTENDANCE_ABSENT => 'Absent'];
				$tmpArray['sessiontrainer_attendance'] = $outputLangs->transnoentities($attendanceKeys[(int) $signatory->attendance] ?? 'Absent');
				$tmpArray['sessiontrainer_signature_date'] = !empty($signatory->signature_date) ? dol_print_date($signatory->signature_date, 'dayhour', 'tzuserrel') : $langs->trans('NoSignature');

                static::setTmpArrayVars($tmpArray, $listLines, $outputLangs);
            }
            $odfHandler->mergeSegment($listLines);
        }

		return 0;
	}
}
