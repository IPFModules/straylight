<?php
/**
 * Class representing Straylight client objects
 *
 * @copyright	Copyright Madfish (Simon Wilkinson).
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		straylight
 * @version		$Id$
 */

defined("ICMS_ROOT_PATH") or die("ICMS root path not defined");

class mod_straylight_Client extends icms_ipf_Object 
{
	/**
	 * Constructor
	 *
	 * @param mod_straylight_Client $handler Object handler
	 */
	public function __construct(&$handler)
	{
		parent::__construct($handler);

		$this->quickInitVar("client_id", XOBJ_DTYPE_INT, TRUE);
		$this->quickInitVar("authorised", XOBJ_DTYPE_INT, TRUE, FALSE, FALSE, 0);
		$this->quickInitVar("shared_hmac_key", XOBJ_DTYPE_TXTAREA, TRUE);
		$this->setControl("authorised", "yesno");
	}

	/**
	 * Overriding the icms_ipf_Object::getVar method to assign a custom method on some
	 * specific fields to handle the value before returning it
	 *
	 * @param str $key key of the field
	 * @param str $format format that is requested
	 * @return mixed value of the field that is requested
	 */
	public function getVar($key, $format = "s")
	{
		if ($format == "s" && in_array($key, array("authorised")))
		{
			return call_user_func(array ($this,	$key));
		}
		return parent::getVar($key, $format);
	}
	
	public function authorised()
	{
		$status = $button = '';

		$status = $this->getVar('authorised', 'e');
		$button = '<a href="' . ICMS_URL . '/modules/' . basename(dirname(dirname(__FILE__)))
				. '/admin/client.php?client_id=' . $this->getVar('client_id')
				. '&amp;op=changeAuthorisationStatus">';
		if ($status == false) {
			$button .= '<img src="../images/button_cancel.png" alt="' . _CO_STRAYLIGHT_AUTHORISATION_DISABLED
				. '" title="' . _CO_STRAYLIGHT_AUTHORISATION_ENABLE . '" /></a>';

		} else {

			$button .= '<img src="../images/button_ok.png" alt="' . _CO_STRAYLIGHT_AUTHORISATION_ENABLED
				. '" title="' . _CO_STRAYLIGHT_AUTHORISATION_DISABLE . '" /></a>';
		}
		return $button;
	}
	
	public function user_name()
	{
		return icms_member_user_Handler::getUserLink($this->getVar('uid', 'e'));
	}
}