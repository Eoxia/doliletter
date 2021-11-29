<?php
/* Copyright (C) 2021 EOXIA <dev@eoxia.com>
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
 * \file    core/triggers/interface_99_modDigiriskdolibarr_DigiriskdolibarrTriggers.class.php
 * \ingroup digiriskdolibarr
 * \brief   Digirisk Dolibarr trigger.
 */

require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';

/**
 *  Class of triggers for Digiriskdolibarr module
 */
class InterfaceDoliLetterTriggers extends DolibarrTriggers
{
	/**
	 * @var DoliDB Database handler
	 */
	protected $db;

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;

		$this->name = preg_replace('/^Interface/i', '', get_class($this));
		$this->family = "demo";
		$this->description = "Doliletter triggers.";
		$this->version = '1.0.0';
		$this->picto = 'Doliletter@Doliletter';
	}

	/**
	 * Trigger name
	 *
	 * @return string Name of trigger file
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Trigger description
	 *
	 * @return string Description of trigger file
	 */
	public function getDesc()
	{
		return $this->description;
	}

	/**
	 * Function called when a Dolibarrr business event is done.
	 * All functions "runTrigger" are triggered if file
	 * is inside directory core/triggers
	 *
	 * @param string 		$action 	Event action code
	 * @param CommonObject 	$object 	Object
	 * @param User 			$user 		Object user
	 * @param Translate 	$langs 		Object langs
	 * @param Conf 			$conf 		Object conf
	 * @return int              		<0 if KO, 0 if no triggered ran, >0 if OK
	 */
	public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
	{
		//echo '<pre>'; print_r( $conf ); echo '</pre>'; exit;
		if (empty($conf->doliletter->enabled)) return 0; // If module is not enabled, we do nothing

		// Data and type of action are stored into $object and $action
		switch ($action) {

			case 'DOLILETTER_ENVELOPE_SENTBYMAIL' :
				require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
				$contact_temp = new Contact($this->db);
				require_once __DIR__ . "/../../class/envelope_email.class.php";
				$now = dol_now();
				$mail = new EnvelopeEmail($this->db);
				$mail->fk_envelope = $object->id;
				$mail->fk_socpeople = 1;
				if (!empty($mail->fk_socpeople))
				{
					$contact_temp->fetch($mail->fk_socpeople);
					$mail->contact_fullname = $contact_temp->firstname . ' ' . $contact_temp->lastname;
				}

				else
				{
					$mail->contact_fullname = 'no contact set';
				}
				$mail->recipient_email = 'receveur@domain.tld';
				$mail->date_creation = $mail->db->idate($now);
				$mail->status = 0;
				print $mail->create($user);
				break;

			case 'COMPANY_CREATE' :
				require_once __DIR__ . "/../../class/envelope_email.class.php";
				$now = dol_now();
				$mail = new EnvelopeEmail($this->db);
				$mail->fk_envelope = 31;
				$mail->fk_socpeople = 2;
				$mail->contact_fullname = 'fullname envoyeurxxxxxx';
				$mail->recipient_email = 'receveur@domain.TDL';
				$mail->date_creation = $mail->db->idate($now);
				$mail->status = 0;
				print $mail->create($user);
				//echo '<pre>'; print_r( ':creation success?' ); echo '</pre>'; exit;
				break;

			default:
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
				break;
		}


		return 0;
	}
}
