<?php
/**
 * Footer page included at the end of each page on user side of the mdoule
 *
 * @copyright	Copyright Madfish (Simon Wilkinson).
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		straylight
 * @version		$Id$
 */

defined("ICMS_ROOT_PATH") or die("ICMS root path not defined");

$icmsTpl->assign("straylight_adminpage", "<a href='" . ICMS_URL . "/modules/" . icms::$module->getVar("dirname") . "/admin/index.php'>" ._MD_STRAYLIGHT_ADMIN_PAGE . "</a>");
$icmsTpl->assign("straylight_is_admin", icms_userIsAdmin(STRAYLIGHT_DIRNAME));
$icmsTpl->assign('straylight_url', STRAYLIGHT_URL);
$icmsTpl->assign('straylight_images_url', STRAYLIGHT_IMAGES_URL);

$xoTheme->addStylesheet(STRAYLIGHT_URL . 'module' . ((defined("_ADM_USE_RTL") && _ADM_USE_RTL) ? '_rtl' : '') . '.css');

include_once ICMS_ROOT_PATH . '/footer.php';