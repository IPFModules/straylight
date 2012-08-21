<?php
/**
* Client page
*
* @copyright	Copyright Madfish (Simon Wilkinson).
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		straylight
* @version		$Id$
*/

include_once "header.php";

$xoopsOption["template_main"] = "straylight_client.html";
include_once ICMS_ROOT_PATH . "/header.php";

$straylight_client_handler = icms_getModuleHandler("client", basename(dirname(__FILE__)), "straylight");

/** Use a naming convention that indicates the source of the content of the variable */
$clean_client_id = isset($_GET["client_id"]) ? (int)$_GET["client_id"] : 0 ;
$clientObj = $straylight_client_handler->get($clean_client_id);

if ($clientObj && !$clientObj->isNew()) {
	$icmsTpl->assign("straylight_client", $clientObj->toArray());
} else {
	$icmsTpl->assign("straylight_title", _MD_STRAYLIGHT_ALL_CLIENTS);

	$objectTable = new icms_ipf_view_Table($straylight_client_handler, FALSE, array());
	$objectTable->isForUserSide();
	$objectTable->addColumn(new icms_ipf_view_Column("uid"));
	$icmsTpl->assign("straylight_client_table", $objectTable->fetch());
}

$icmsTpl->assign("straylight_module_home", '<a href="' . ICMS_URL . "/modules/" . icms::$module->getVar("dirname") . '/">' . icms::$module->getVar("name") . "</a>");

include_once "footer.php";