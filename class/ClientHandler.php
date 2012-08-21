<?php
/**
 * Classes responsible for managing Straylight client objects
 *
 * @copyright	Copyright Madfish (Simon Wilkinson).
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		straylight
 * @version		$Id$
 */

defined("ICMS_ROOT_PATH") or die("ICMS root path not defined");

class mod_straylight_ClientHandler extends icms_ipf_Handler {
	/**
	 * Constructor
	 *
	 * @param icms_db_legacy_Database $db database connection object
	 */
	public function __construct(&$db) {
		parent::__construct($db, "client", "client_id", "uid", "publickey", "straylight");

	}
	
	/**
	 * Toggles Straylight authentication online or offline
	 *
	 * @param int $token_id
	 * @param str $field
	 * @return int $visibility
	 */
	public function changeAuthorisationStatus($client_id, $field) {

		$authorised = $clientObj = '';

		$clientObj = $this->get($client_id);
		if ($clientObj->getVar($field, 'e') == TRUE) {
			$clientObj->setVar($field, 0);
			$authorised = 0;
		} else {
			$clientObj->setVar($field, 1);
			$authorised = 1;
		}
		$this->insert($clientObj, true);

		return $authorised;
	}
}