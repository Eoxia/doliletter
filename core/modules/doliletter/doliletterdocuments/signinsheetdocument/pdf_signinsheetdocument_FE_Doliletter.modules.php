<?php
/* Copyright (C) 2026 DoliLetter
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
 */

/**
 * \file       core/modules/doliletter/doliletterdocuments/signinsheetdocument/pdf_signinsheetdocument_standard.modules.php
 * \ingroup    doliletter
 * \brief      File with class to generate attendance sheet PDF
 */

require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';

require_once DOL_DOCUMENT_ROOT . '/custom/saturne/core/modules/saturne/modules_saturne.php';
require_once DOL_DOCUMENT_ROOT . '/custom/saturne/class/saturnesignature.class.php';

/**
 * Class to generate attendance sheet PDF
 */
class pdf_signinsheetdocument_FE_Doliletter extends SaturneDocumentModel
{
    /**
     * @var int Page count
     */
    public $page_count;
	public $db;
	public $name;
	public $description;
	public $type;
	public $phpmin = array(7, 4);
	public $version = 'dolibarr';
	public $page_largeur;
	public $page_hauteur;
	public $format;
	public $marge_gauche;
	public $marge_droite;
	public $marge_haute;
	public $marge_basse;

    // Saturne ODT bypass properties to avoid PHP 8.2 warnings
    public $custom_info = false;
    public $custom_scandir = '';
    public $custom_name = '';

	public function __construct($db)
	{
		global $conf, $langs;

		$this->db = $db;
		$this->name = "FE_Doliletter";
		$this->description = $langs->trans('DocModelFE_DoliletterDesc');
		$this->type = 'pdf';
		$this->update_main_doc_field = 1;

		$this->format = array(210, 297);
		$this->marge_gauche = isset($conf->global->MAIN_PDF_MARGIN_LEFT) ? $conf->global->MAIN_PDF_MARGIN_LEFT : 10;
		$this->marge_droite = isset($conf->global->MAIN_PDF_MARGIN_RIGHT) ? $conf->global->MAIN_PDF_MARGIN_RIGHT : 10;
		$this->marge_haute = isset($conf->global->MAIN_PDF_MARGIN_TOP) ? $conf->global->MAIN_PDF_MARGIN_TOP : 10;
		$this->marge_basse = isset($conf->global->MAIN_PDF_MARGIN_BOTTOM) ? $conf->global->MAIN_PDF_MARGIN_BOTTOM : 10;

		$this->page_largeur = $this->format[0];
		$this->page_hauteur = $this->format[1];
	}

    /**
     * Return description of document model
     *
     * @param  Translate $langs Lang object to use for output
     * @return string           Description
     */
    public function info(Translate $langs): string
    {
        return $this->description;
    }

	public function write_file(SaturneDocuments $objectDocument, Translate $outputLangs, string $srcTemplatePath, int $hideDetails, int $hideDesc, int $hideRef, array $moreParam): int
	{
		global $user, $langs, $conf, $mysoc, $db, $hookmanager;

        $object = $moreParam['object'];
        if (empty($object->ref)) $object->ref = 'SPECIMEN';
        if (empty($object->id)) $object->id = 0;

        $outputlangs = $outputLangs;
        $srctemplatepath = $srcTemplatePath;
        $hidedetails = $hideDetails;
        $hidedesc = $hideDesc;
        $hideref = $hideRef;

		if (!is_object($outputlangs)) {
			$outputlangs = $langs;
		}
		$outputlangs->load("main");
		$outputlangs->load("companies");
		$outputlangs->load("bills");
		$outputlangs->load("other");

		$isSpecimen = empty($object->ref) || $object->ref === 'SPECIMEN';
		if ($isSpecimen) {
			$dir = $conf->doliletter->dir_output . "/signinsheet/public_specimen";
			$filename = "SPECIMEN.pdf";
		} else {
			$dir = $conf->doliletter->dir_output . "/signinsheet/" . dol_sanitizeFileName($object->ref);
			$filename = dol_sanitizeFileName($object->ref) . ".pdf";
		}
		
		if (!file_exists($dir)) dol_mkdir($dir);

		$file = $dir . "/" . $filename;

		$pdf = pdf_getInstance($this->format);
		$default_font_size = pdf_getPDFFontSize($outputlangs);
		$pdf->SetFont(pdf_getPDFFont($outputlangs));
		$pdf->SetCreator("Dolibarr");
		$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
		$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
		$pdf->SetSubject($outputlangs->convToOutputCharset($object->ref));
		$pdf->SetKeywords($object->ref);
		$pdf->SetAutoPageBreak(1, 0);

		$pdf->AddPage();
        
        $posy = $this->_pagehead($pdf, $object, $outputlangs);

		$pdf->SetFont('', '', $default_font_size);
		
		// Signatures Data
		$signatory = new SaturneSignature($this->db);
		$signatories = $signatory->fetchAll('', '', 0, 0, ['customsql' => 't.object_type = "doliletter_attendance_sheet" AND t.fk_object = ' . $object->id]);
		if (!is_array($signatories)) $signatories = [];

		$pdf->SetXY($this->marge_gauche, $posy);
		$pdf->SetFillColor(240, 240, 240);
		
        // Motif
        if (!empty($object->label)) {
            $pdf->MultiCell(0, 8, "Motif de l'émargement : " . $object->label, 0, 'L');
            $posy = $pdf->GetY() + 2;
        }

		// Table Header
		$pdf->SetXY($this->marge_gauche, $posy);
		$pdf->SetFillColor(63, 114, 134);
		$pdf->SetTextColor(255, 255, 255);
		$pdf->SetFont('', 'B', $default_font_size);
		$pdf->Cell(15, 10, 'Réf.', 1, 0, 'C', 1);
		$pdf->Cell(35, 10, 'Nom', 1, 0, 'C', 1);
		$pdf->Cell(35, 10, 'Prénom', 1, 0, 'C', 1);
		$pdf->Cell(30, 10, 'Présence', 1, 0, 'C', 1);
		$pdf->Cell(40, 10, 'Signature', 1, 0, 'C', 1);
		$pdf->Cell(35, 10, 'Date de signature', 1, 1, 'C', 1);

		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('', '', $default_font_size);

		$posy = $pdf->GetY();
		$rowHeight = 20;

		foreach ($signatories as $sig) {
			if ($posy + $rowHeight > $this->page_hauteur - $this->marge_basse - 20) {
				$pdf->AddPage();
				$posy = $this->_pagehead($pdf, $object, $outputlangs);
				
				// Redraw Table Header
				$pdf->SetXY($this->marge_gauche, $posy);
				$pdf->SetFillColor(63, 114, 134);
				$pdf->SetTextColor(255, 255, 255);
				$pdf->SetFont('', 'B', $default_font_size);
				$pdf->Cell(15, 10, 'Réf.', 1, 0, 'C', 1);
				$pdf->Cell(35, 10, 'Nom', 1, 0, 'C', 1);
				$pdf->Cell(35, 10, 'Prénom', 1, 0, 'C', 1);
				$pdf->Cell(30, 10, 'Présence', 1, 0, 'C', 1);
				$pdf->Cell(40, 10, 'Signature', 1, 0, 'C', 1);
				$pdf->Cell(35, 10, 'Date de signature', 1, 1, 'C', 1);
				
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size);
				$posy = $pdf->GetY();
			}

			$pdf->SetXY($this->marge_gauche, $posy);
			$pdf->Cell(15, $rowHeight, $sig->id, 1, 0, 'C');
			$pdf->Cell(35, $rowHeight, $sig->lastname, 1, 0, 'C');
			$pdf->Cell(35, $rowHeight, $sig->firstname, 1, 0, 'C');
			
			$presence = ($sig->attendance == 0) ? 'Présent' : 'Absent/Retard';
			$pdf->Cell(30, $rowHeight, $presence, 1, 0, 'C');
			
			// Signature cell
			$pdf->Cell(40, $rowHeight, '', 1, 0, 'C');
			if (!empty($sig->signature)) {
                // Determine if signature is base64
                if (preg_match('/^data:image\/(\w+);base64,/', $sig->signature, $type)) {
                    $data = substr($sig->signature, strpos($sig->signature, ',') + 1);
                    $data = base64_decode($data);
                    $img_file = sys_get_temp_dir() . '/sig_' . $sig->id . '.png';
                    file_put_contents($img_file, $data);
                    $pdf->Image($img_file, $this->marge_gauche + 115 + 5, $posy + 2, 30, 16);
                    unlink($img_file);
                }
			}

			// Date
			$dateStr = !empty($sig->signature_date) ? dol_print_date($sig->signature_date, 'dayhour') : '';
			$pdf->Cell(35, $rowHeight, $dateStr, 1, 1, 'C');

			$posy += $rowHeight;
		}

		$this->_pagefoot($pdf, $object, $outputlangs);

		$pdf->Output($file, 'F');
		
		if (!empty($conf->global->MAIN_UMASK)) {
			@chmod($file, octdec($conf->global->MAIN_UMASK));
		}
		
		$this->result = ['fullpath' => $file];
		
		return 1;
	}

	protected function _pagehead(&$pdf, $object, $outputlangs)
	{
		global $conf, $mysoc;

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		// Logo
		if (!empty($mysoc->logo)) {
			$logo = $conf->mycompany->dir_output . '/logos/' . $mysoc->logo;
			if (is_readable($logo)) {
				$pdf->Image($logo, $this->marge_gauche, $this->marge_haute, 40);
			}
		}

		// Title
		$pdf->SetXY(80, $this->marge_haute + 5);
		$pdf->SetFont('', 'B', 18);
		$pdf->SetTextColor(31, 56, 100);
		$pdf->Cell(0, 10, "FEUILLE D'ÉMARGEMENT", 0, 1, 'C');
		
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('', '', $default_font_size);

		if ($pdf->PageNo() > 1) {
			return $this->marge_haute + 20;
		}

		// Company Box
		$posy = $this->marge_haute + 30;
		$pdf->SetXY($this->marge_gauche, $posy);
		$pdf->SetFillColor(245, 245, 245);
		$pdf->Rect($this->marge_gauche, $posy, $this->page_largeur - $this->marge_gauche - $this->marge_droite, 25, 'F');

		$pdf->SetXY($this->marge_gauche + 5, $posy + 2);
		$pdf->SetFont('', 'B', $default_font_size);
		$pdf->Cell(30, 5, "Société :", 0, 0, 'L');
		$pdf->SetFont('', '', $default_font_size);
		$pdf->Cell(60, 5, $mysoc->name, 0, 0, 'L');
		
		$pdf->SetFont('', 'B', $default_font_size);
		$pdf->Cell(20, 5, "Siret :", 0, 0, 'L');
		$pdf->SetFont('', '', $default_font_size);
		$pdf->Cell(40, 5, $mysoc->idprof1, 0, 1, 'L');

		$pdf->SetXY($this->marge_gauche + 5, $posy + 8);
		$pdf->SetFont('', 'B', $default_font_size);
		$pdf->Cell(30, 5, "Adresse :", 0, 0, 'L');
		$pdf->SetFont('', '', $default_font_size);
		$pdf->Cell(60, 5, $mysoc->address, 0, 1, 'L');

		$pdf->SetXY($this->marge_gauche + 5, $posy + 14);
		$pdf->SetFont('', 'B', $default_font_size);
		$pdf->Cell(30, 5, "E-mail :", 0, 0, 'L');
		$pdf->SetFont('', '', $default_font_size);
		$pdf->Cell(60, 5, $mysoc->email, 0, 0, 'L');

		$pdf->SetFont('', 'B', $default_font_size);
		$pdf->Cell(20, 5, "Tél :", 0, 0, 'L');
		$pdf->SetFont('', '', $default_font_size);
		$pdf->Cell(40, 5, $mysoc->phone, 0, 1, 'L');

		$pdf->SetXY($this->marge_gauche + 5, $posy + 20);
		$pdf->SetFont('', 'B', $default_font_size);
		$pdf->Cell(30, 5, "Site Web :", 0, 0, 'L');
		$pdf->SetFont('', '', $default_font_size);
		$pdf->Cell(60, 5, $mysoc->url, 0, 1, 'L');

		// Public Note Box
		$posy += 30;
		if (!empty($object->note_public)) {
			$pdf->SetFillColor(240, 248, 255);
			$pdf->SetXY($this->marge_gauche, $posy);
			$pdf->SetFont('', 'B', $default_font_size);
			$pdf->Cell(0, 6, "NOTE PUBLIQUE", 0, 1, 'L', 1);
			$pdf->SetFont('', '', $default_font_size);
			$pdf->SetXY($this->marge_gauche, $posy + 6);
			$pdf->MultiCell(0, 5, dol_htmlcleanlastbr($object->note_public), 0, 'L', 1);
            $posy = $pdf->GetY();
		}
        
        return $posy + 10;
	}

	protected function _pagefoot(&$pdf, $object, $outputlangs)
	{
		$default_font_size = pdf_getPDFFontSize($outputlangs);
		$pdf->SetXY($this->marge_gauche, -20);
		$pdf->SetFont('', '', $default_font_size - 1);
		$pdf->Cell(0, 5, "Document généré électroniquement", 0, 1, 'L');
		$pdf->SetXY($this->marge_gauche, -15);
		$pdf->Cell(0, 5, "Référence : " . $object->ref, 0, 0, 'C');
		$pdf->SetXY($this->marge_gauche, -15);
		$pdf->Cell(0, 5, "Page " . $pdf->PageNo(), 0, 0, 'R');
	}
}
