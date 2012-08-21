<?php
/**
 * Common file of the module included on all pages of the module
 *
 * @copyright	Copyright Madfish (Simon Wilkinson).
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		straylight
 * @version		$Id$
 */

defined("ICMS_ROOT_PATH") or die("ICMS root path not defined");

if (!defined("STRAYLIGHT_DIRNAME")) define("STRAYLIGHT_DIRNAME", $modversion["dirname"] = basename(dirname(dirname(__FILE__))));
if (!defined("STRAYLIGHT_URL")) define("STRAYLIGHT_URL", ICMS_URL."/modules/".STRAYLIGHT_DIRNAME."/");
if (!defined("STRAYLIGHT_ROOT_PATH")) define("STRAYLIGHT_ROOT_PATH", ICMS_ROOT_PATH."/modules/".STRAYLIGHT_DIRNAME."/");
if (!defined("STRAYLIGHT_IMAGES_URL")) define("STRAYLIGHT_IMAGES_URL", STRAYLIGHT_URL."images/");
if (!defined("STRAYLIGHT_ADMIN_URL")) define("STRAYLIGHT_ADMIN_URL", STRAYLIGHT_URL."admin/");

// Include the common language file of the module
icms_loadLanguageFile("straylight", "common");