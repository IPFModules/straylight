<?php
/**
 * Listens for administration commands from remote devices (eg. Android, Arduino).
 * 
 * Commands are passed to the Straylight client handler, which handles sanitisation and
 * authentication checks internally before executing an instruction. See docs folder for more details.
 * 
 * Commands are authenticated via calculation of SHA256 HMAC with preshared, per-device keys. 
 * This allows the integrity and authenticity of the request to be validated, but the request is not
 * encrypted (ie. it is not private). Replay attacks are guarded against by inclusion of a counter
 * (tracked by the server), timestamp and a random string of crap (RSOC) in the the request, which 
 * ensures that each request has a unique HMAC fingerprint.
 * 
 * However, some devices (eg. Arduino) may not be capable of supplying all of these credentials due
 * to resource limitations, so the challenge tests are arranged in a semi-independent manner; 
 * you can comment out particular tests if your remote device can't handle them. Of course, this 
 * reduces security. As a minimum: Use the client_id, HMAC and counter or timestamp. The random 
 * text is highly desirable. If your client device can't generate adequate randomness, consider 
 * pre-generating a large chunk of it it on a more capable device and storing it on the client (in
 * this case, you MUST discard random data as you use it, you must NEVER re-use it).
 * 
 * Key length: Use a 256 byte key for optimum security. Shorter keys weaken security. There is 
 * no evidence that longer keys improve it. 256 is what the algorithm uses internally.
 * 
 * Randomness: Get your key from a high quality random source, such as GRC's perfect passwords page.
 * Be aware that random number generation on small devices (notably Arduino) can be extremely bad 
 * (that is to say, UNACCEPTABLE for security purposes) https://www.grc.com/passwords.htm
 * 
 * Data length: The overall length of device-supplied data used to calculate the HMAC should be at 
 * least 256 characters for security reasons. So you may want to adjust the length of your RSOC
 * accordingly.
 *
 * @copyright	Copyright Madfish (Simon Wilkinson).
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL V2 or any later version
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		straylight
 * @version		$Id$
 */

include_once 'header.php';
include_once ICMS_ROOT_PATH . '/header.php';

// Initialise
$straylight_client_handler = icms_getModuleHandler('client', basename(dirname(__FILE__)), 'straylight');

// Pass request to Straylight for athentication and execution. Sanitisation is handled internally.
$straylight_client_handler->authenticate_remote_command($_POST);