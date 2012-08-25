<?php
/**
 * Admin page to manage clients
 *
 * List, add, edit and delete client objects
 *
 * @copyright	Copyright Madfish (Simon Wilkinson).
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		straylight
 * @version		$Id$
 */

/**
 * Edit a Client
 *
 * @param int $client_id Clientid to be edited
*/
function editclient($client_id = 0) {
	global $straylight_client_handler, $icmsModule, $icmsAdminTpl;

	$clientObj = $straylight_client_handler->get($client_id);

	if (!$clientObj->isNew()){
		$icmsModule->displayAdminMenu(0, _AM_STRAYLIGHT_CLIENTS . " > " . _CO_ICMS_EDITING);
		$sform = $clientObj->getForm(_AM_STRAYLIGHT_CLIENT_EDIT, "addclient");
		$sform->assign($icmsAdminTpl);
	} else {
		$icmsModule->displayAdminMenu(0, _AM_STRAYLIGHT_CLIENTS . " > " . _CO_ICMS_CREATINGNEW);
		$sform = $clientObj->getForm(_AM_STRAYLIGHT_CLIENT_CREATE, "addclient");
		$sform->assign($icmsAdminTpl);

	}
	$icmsAdminTpl->display("db:straylight_admin_client.html");
}

include_once "admin_header.php";

$straylight_client_handler = icms_getModuleHandler("client", basename(dirname(dirname(__FILE__))), "straylight");
/** Use a naming convention that indicates the source of the content of the variable */
$clean_op = "";
/** Create a whitelist of valid values, be sure to use appropriate types for each value
 * Be sure to include a value for no parameter, if you have a default condition
 */
$valid_op = array ("mod", "changedField", "addclient", "del", "view", "changeAuthorisationStatus", "");

if (isset($_GET["op"])) $clean_op = htmlentities($_GET["op"]);
if (isset($_POST["op"])) $clean_op = htmlentities($_POST["op"]);

/** Again, use a naming convention that indicates the source of the content of the variable */
$clean_client_id = isset($_GET["client_id"]) ? (int)$_GET["client_id"] : 0 ;

/**
 * in_array() is a native PHP function that will determine if the value of the
 * first argument is found in the array listed in the second argument. Strings
 * are case sensitive and the 3rd argument determines whether type matching is
 * required
*/
if (in_array($clean_op, $valid_op, TRUE)) {
	switch ($clean_op) {
		case "mod":
		case "changedField":
			icms_cp_header();
			editclient($clean_client_id);
			break;

		case "addclient":
			$controller = new icms_ipf_Controller($straylight_client_handler);
			$controller->storeFromDefaultForm(_AM_STRAYLIGHT_CLIENT_CREATED, _AM_STRAYLIGHT_CLIENT_MODIFIED);
			break;

		case "del":
			$controller = new icms_ipf_Controller($straylight_client_handler);
			$controller->handleObjectDeletion();
			break;

		case "view" :
			$clientObj = $straylight_client_handler->get($clean_client_id);
			icms_cp_header();
			$clientObj->displaySingleObject();
			break;
		
		case "changeAuthorisationStatus":
			$authorisationStatus = $ret = '';
			$authorisationStatus = $straylight_client_handler->changeAuthorisationStatus($clean_client_id, 'authorised');
			$ret = '/modules/' . basename(dirname(dirname(__FILE__))) . '/admin/client.php';
			if (!$authorisationStatus) {
				redirect_header(ICMS_URL . $ret, 2, _AM_STRAYLIGHT_AUTHORISATION_DISABLED);
			} else {
				redirect_header(ICMS_URL . $ret, 2, _AM_STRAYLIGHT_AUTHORISATION_ENABLED);
			}
			break;

		default:
			icms_cp_header();
			$icmsModule->displayAdminMenu(0, _AM_STRAYLIGHT_CLIENTS);
			$objectTable = new icms_ipf_view_Table($straylight_client_handler);
			$objectTable->addColumn(new icms_ipf_view_Column("client_id"));				
			$objectTable->addColumn(new icms_ipf_view_Column("authorised"));
			$objectTable->addColumn(new icms_ipf_view_Column("request_counter"));
			$objectTable->addIntroButton("addclient", "client.php?op=mod", _AM_STRAYLIGHT_CLIENT_CREATE);
			$icmsAdminTpl->assign("straylight_client_table", $objectTable->fetch());
			$icmsAdminTpl->display("db:straylight_admin_client.html");
			break;
	}
	icms_cp_footer();
}
/**
 * If you want to have a specific action taken because the user input was invalid,
 * place it at this point. Otherwise, a blank page will be displayed
 */