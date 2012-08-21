<?php
/**
 * English language constants commonly used in the module
 *
 * @copyright	Copyright Madfish (Simon Wilkinson).
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		straylight
 * @version		$Id$
 */

defined("ICMS_ROOT_PATH") or die("ICMS root path not defined");

// Client
define("_CO_STRAYLIGHT_CLIENT_UID", "User ID");
define("_CO_STRAYLIGHT_CLIENT_UID_DSC", "Select the admin associated with this Android client.");
define("_CO_STRAYLIGHT_CLIENT_AUTHORISED", "Authorised?");
define("_CO_STRAYLIGHT_CLIENT_AUTHORISED_DSC", "Enable or disable remote administrative privileges for this Android client. A disabled client has no rights and is completely non-functional.");
define("_CO_STRAYLIGHT_CLIENT_PUBLICKEY", "Public key");
define("_CO_STRAYLIGHT_CLIENT_PUBLICKEY_DSC", "Enter the client&#039;s public key here. This is used to validate request signatures submitted by the Android client. Requests that are not accompanied by a valid signature will be discarded.");

// Admin table
define("_CO_STRAYLIGHT_USER_NAME", "User name");
define("_CO_STRAYLIGHT_AUTHORISATION_ENABLED", "Authorisation enabled");
define("_CO_STRAYLIGHT_AUTHORISATION_DISABLED", "Authorisation disabled");
define("_CO_STRAYLIGHT_AUTHORISATION_ENABLE", "Enable authorisation");
define("_CO_STRAYLIGHT_AUTHORISATION_DISABLE", "Disable authorisation");