<?php
/**
 * English language constants related to module information
 *
 * @copyright	Copyright Madfish (Simon Wilkinson).
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		straylight
 * @version		$Id$
 */

defined("ICMS_ROOT_PATH") or die("ICMS root path not defined");

define("_MI_STRAYLIGHT_MD_NAME", "Straylight");
define("_MI_STRAYLIGHT_MD_DESC", "ImpressCMS Simple Straylight");
define("_MI_STRAYLIGHT_CLIENTS", "Clients");

// Preferences
define("_MI_STRAYLIGHT_TIMESTAMP_TOLERANCE", "Timestamp tolerance (seconds)");
define("_MI_STRAYLIGHT_TIMESTAMP_TOLDERANCEDSC", "The maximum permitted difference between the 
	server time, and the timestamp of the client request. Old requests will be discarded to help
	prevent replay attacks.");