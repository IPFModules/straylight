<?php
/**
 * Straylight version infomation
 *
 * This file holds the configuration information of this module
 *
 * @copyright	Copyright Madfish (Simon Wilkinson).
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		straylight
 * @version		$Id$
 */

defined("ICMS_ROOT_PATH") or die("ICMS root path not defined");

/**  General Information  */
$modversion = array(
	"name"                      => _MI_STRAYLIGHT_MD_NAME,
	"version"					=> 1.0,
	"description"               => _MI_STRAYLIGHT_MD_DESC,
	"author"					=> "Madfish (Simon Wilkinson)",
	"credits"					=> "",
	"help"						=> "",
	"license"					=> "GNU General Public License (GPL)",
	"official"					=> 0,
	"dirname"					=> basename(dirname(__FILE__)),
	"modname"					=> "straylight",

/**  Images information  */
	"iconsmall"					=> "images/icon_small.png",
	"iconbig"					=> "images/icon_big.png",
	"image"						=> "images/icon_big.png", /* for backward compatibility */

/**  Development information */
	"status_version"            => "1.0",
	"status"					=> "Alpha",
	"date"						=> "10/9/2014",
	"author_word"               => "For use by trusted administrators only.",
	"warning"					=> _CO_ICMS_WARNING_BETA,

/** Contributors */
	"developer_website_url"     => "http://www.isengard.biz",
	"developer_website_name"    => "Isengard.biz",
	"developer_email"           => "simon@isengard.biz",

/** Administrative information */
	"hasAdmin"					=> 1,
	"adminindex"                => "admin/index.php",
	"adminmenu"					=> "admin/menu.php",

/** Install and update informations */
	"onInstall"					=> "include/onupdate.inc.php",
	"onUpdate"					=> "include/onupdate.inc.php",

/** Search information */
	"hasSearch"					=> 0,

/** Menu information */
	"hasMain"					=> 0,

/** Comments information */
	"hasComments"               => 0);

/** other possible types: testers, translators, documenters and other */
$modversion['people']['developers'][] = "Madfish (Simon Wilkinson)";

/** Manual */
$modversion['manual']['wiki'][] = "<a href='http://wiki.impresscms.org/index.php?title=Straylight' target='_blank'>English</a>";

/** Database information */
$modversion['object_items'][1] = 'client';

$modversion["tables"] = icms_getTablesArray($modversion['dirname'], $modversion['object_items']);

/** Templates information */
$modversion['templates'] = array(
	array("file" => "straylight_admin_client.html", "description" => "Client admin index"));

/** Preferences information **/
$modversion['config'][] = array(
	'name' => 'timestamp_tolerance',
	'title' => '_MI_STRAYLIGHT_TIMESTAMP_TOLERANCE',
	'description' => '_MI_STRAYLIGHT_TIMESTAMP_TOLERANCEDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' => '600'
);