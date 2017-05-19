<?php
/**
 * Sets up the basic include paths. Include this file to any custom php file which is not included through the Bootstrap. (For instance, upload scripts or ajax calls)
 * @author Ids Klijnsma - Fur
 */
ini_set('display_errors', '1'); // display errors in the HTML
ini_set('track_errors', '1'); // creates php error variable
ini_set('log_errors', '1'); // writes to the log file
error_reporting(E_ALL|E_STRICT);

header('Content-Type: text/html; charset=utf-8');

date_default_timezone_set('Europe/Amsterdam');
include_once(dirname(__FILE__) . '/../../site/config/Constants.php');

if(!SHOWDEPRECATION)
	error_reporting((E_ALL|E_STRICT)&~E_USER_DEPRECATED&~E_DEPRECATED);

if(!DISPLAYERRORS) {
	ini_set('display_errors', '0');
}

if(LIVESERVER === true) {
	ini_set('display_errors', '0'); // display errors in the HTML
	ini_set('track_errors', '0'); // creates php error variable
	ini_set('log_errors', '1'); // writes to the log file
	error_reporting(E_ALL|E_STRICT);
}
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR .
					BASEPATH . 'bright/library' . PATH_SEPARATOR .
					BASEPATH . 'bright/site/');

define('SMARTYAVAILABLE', is_dir(BASEPATH . 'bright/externallibs/smarty'));
define('SWIFTAVAILABLE', is_dir(BASEPATH . 'bright/externallibs/Swift'));
define('TCPDFAVAILABLE', is_dir(BASEPATH . 'bright/externallibs/tcpdf'));
define('MINIFYAVAILABLE', is_dir(BASEPATH . 'bright/externallibs/min'));
define('SPHIDERAVAILABLE', is_dir(BASEPATH . 'bright/externallibs/sphider')|| is_dir(BASEPATH . 'bright/externallibs/search'));

if(is_dir(BASEPATH . 'bright/externallibs')) {
	ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR .
						BASEPATH . 'bright/externallibs');
}

if(file_exists(BASEPATH . 'vendor/autoload.php')) {
    require_once BASEPATH . 'vendor/autoload.php';
}

class BrightAutoloader {
	public function __construct() {
		spl_autoload_register([$this, '_namespaceLoader']);
		spl_autoload_register([$this, '_loader']);
	}

	private function _namespaceLoader($className) {

		if(strpos($className, '\\') === false)
			return false;

		$classPath = explode('\\', $className);

		if($classPath[0] == 'Bright' && file_exists(BASEPATH . 'bright/library/' . join(DIRECTORY_SEPARATOR, $classPath) . '.php')) {
			include BASEPATH . 'bright/library/' . join(DIRECTORY_SEPARATOR, $classPath) . '.php';
			return true;
		}

		if($classPath[0] !== 'Bright' && file_exists(BASEPATH . 'bright/externallibs/' . join(DIRECTORY_SEPARATOR, $classPath) . '.php'))
			include BASEPATH . 'bright/externallibs/' . join(DIRECTORY_SEPARATOR, $classPath) . '.php';

	}
	
	private function _loader($className) {

		// We don't handle namespaces
		if(strpos($className, '\\') !== false)
			return false;

		switch($className) {
			case 'Backup':
				include BASEPATH . 'bright/library/Bright/services/page/Backup.php';
				break;

			case 'UserGroup':
				include BASEPATH . 'bright/library/Bright/services/user/UserGroup.php';
				break;

			case 'Layers':
				include BASEPATH . 'bright/library/Bright/services/maps/Layers.php';
				break;

			case 'Config':
			case 'Settings':
				include BASEPATH . "bright/library/Bright/services/config/$className.php";
				break;

			case 'Lists':
			case 'CustomActions':
				include BASEPATH . "bright/library/Bright/services/custom/$className.php";
				break;
				
			case 'BaseConstants':
			case 'Connection':
			case 'Update':
				include BASEPATH . "bright/library/Bright/core/$className.php";
				break;

			case 'TwitterOAuth':
				include BASEPATH . "bright/library/Bright/utils/twitteroauth.php";
				break;
			case 'OAuthSignatureMethod':
			case 'OAuthSignatureMethod_HMAC_SHA1':
			case 'OAuthSignatureMethod_PLAINTEXT':
				include BASEPATH . "bright/library/Bright/utils/OAuth.php";
				break;
			case 'ArrayCollection':
			case 'LatLng':
			case 'OAdministratorObject':
			case 'ObjectInitializer':
			case 'OCalendarDateObject':
			case 'OCalendarEvent':
			case 'OBaseObject':
			case 'OContent':
			case 'OFieldtype':
			case 'OFile':
			case 'OFolder':
			case 'OLayer':
			case 'OMarker':
			case 'OPage':
			case 'OPoly':
			case 'OTemplate':
			case 'OTreeNode':
			case 'OUserGroup':
			case 'OUserObject':
				include BASEPATH . "bright/library/Bright/services/objects/$className.php";
				break;

			case 'Exceptions':
			case 'UploadException':
			case 'Permissions':
				include BASEPATH . 'bright/library/Bright/core/' . $className . '.php';
				break;

			case 'Contstants':
			case 'Resources':
				include BASEPATH . 'bright/site/config/' . $className . '.php';
				break;
			case 'Bright':
			case 'Bootstrap':
			case 'Serve':
			case 'Parser':
			case 'Router':
			case 'GeneralView':
			case 'UserRegister':
			case 'Bright':
				include BASEPATH . 'bright/library/Frontend/' . $className . '.php';
				break;
			case 'Smarty':
				include BASEPATH . "bright/externallibs/smarty/libs/Smarty.class.php";
				break;
			case 'SmartyView':
				include BASEPATH . "bright/externallibs/smarty/SmartyView.php";
				break;
			default:
				if(endsWith($className, 'View')) {
					$a = explode('\\', $className);
					$className = array_pop($a);
					if(!file_exists(BASEPATH . "bright/site/views/$className.php"))
						return false;
					
					include BASEPATH . "bright/site/views/$className.php";
					
				} else 
				if(endsWith($className, 'Hook')) {
					if(file_exists(BASEPATH . "bright/site/hooks/$className.php")) {
						include BASEPATH . "bright/site/hooks/$className.php";
					} else {
						return false;
					}
				} else 
				if(endsWith($className, 'Controller')) {
					if(file_exists(BASEPATH . "bright/controllers/$className.php")) {
						include BASEPATH . "bright/controllers/$className.php";
					} else if(file_exists(BASEPATH . "bright/site/controllers/$className.php")) {
						include BASEPATH . "bright/site/controllers/$className.php";
					} else {
						return false;
					}
				}else if(startsWith($className, 'TCPDF') && TCPDFAVAILABLE === true) {
					include BASEPATH . "bright/externallibs/tcpdf/tcpdf.php";
				} else if(startsWith($className, 'Swift') && SWIFTAVAILABLE === true) {
					include_once BASEPATH . 'bright/externallibs/Swift/lib/swift_required.php';
				} else {
				
					if(file_exists(BASEPATH . 'bright/library/Bright/services/' . strtolower($className) . '/' . $className . '.php')) {
						include BASEPATH . 'bright/library/Bright/services/' . strtolower($className) . '/' . $className . '.php';
					
					} else if(file_exists(BASEPATH . 'bright/library/Bright/' . $className . '.php')) {
						include BASEPATH . 'bright/library/Bright/' . $className . '.php';
					
					} else if(file_exists(BASEPATH . 'bright/library/Bright/utils/' . $className . '.php')) {
						include BASEPATH . 'bright/library/Bright/utils/' . $className . '.php';
					
					} else if(file_exists(BASEPATH . 'bright/site/actions/' . $className . '.php')) {
						include BASEPATH . 'bright/site/actions/' . $className . '.php';
					
					} else if(file_exists(BASEPATH . 'bright/site/controllers/' . $className . '.php')) {
						include BASEPATH . 'bright/site/controllers/' . $className . '.php';
					
					} else {
						return false;
					}
				}
		}
	}
}

$bal = new BrightAutoloader();

\Bright\utils\Security::init();

function pre($val) {
	echo'<pre>';print_r($val);echo '</pre>';
}


/**
 * Check if haystack starts with needle
 * @param String $haystack
 * @param String $needle
 * @return bool
 */
function startsWith($haystack, $needle) {
	return !strncmp($haystack, $needle, strlen($needle));
}

/**
 * Check if haystack ends with needle
 * @param String $haystack
 * @param String $needle
 * @return bool
 */
function endsWith($haystack, $needle) {
	$length = strlen($needle);
	if ($length == 0) {
		return true;
	}

	return (substr($haystack, -$length) === $needle);
}