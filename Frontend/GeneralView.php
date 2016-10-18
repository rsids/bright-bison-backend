<?php
/**
 * This class should not be altered. If you want to do some settings for all your pages, extend this class and let your views extend your custom class
 * Version history:
 * 2.8 - 2012-08-21
 * - Added createdby, modifiedby, creationdate
 * 2.7 - 2012-03-19
 * - Template variables became public
 * 2.6 - 2011-12-06
 * - Added treeId
 * @author Fur
 * @copyright Copyright &copy; 2010, Fur
 * @version 2.7
 * @package Frontend
 */
class GeneralView extends Parser{
	/**
	 * @var string Title of the page
	 */
	protected $title;

	/**
	 * @var string The canonical tag, generated automatically
	 * @since 2.3 - 8 sep 2010
	 */
	protected $canonical = '';
	/**
	 * @var OTreeNode Contents of the page (OTreeNode)
	 */
	protected $pageData;

	/**
	 * @var string Base url to the user files
	 */
	protected $filesurl;

	/**
	 * @var string Base url of the website
	 */
	protected $baseurl;

	/**
	 * @var string Base path to the user files
	 */
	protected $filespath;

	/**
	 * @var	string The name of the template
	 * @deprecated 2.1 - 16 feb 2010
	 */
	protected $itemtype;

	/**
	 * @var number Number of days to wait before revisiting by indexing robot
	 */
	protected $revisit;

	/**
	 * @var int UNIX Timestamp of the expirationdate for the cache
	 */
	public $expDate;

	/**
	 * @var int UNIX Timestamp
	 */
	public $time;
	/*
	 * Properties from page
	 */

	/**
	 * @var int The id of the page
	 */
	protected $pageId = 0;

	/**
	 * @var int The treeId of the page
	 */
	protected $treeId = 0;
	/**
	 * @var string The unique label of the page
	 */
	protected $label = '';
	/**
	 * @var int The publicationdate as UNIX timestamp
	 */
	protected $publicationdate = 0;
	/**
	 * @var int The expirationdate as UNIX timestamp
	 */
	protected $expirationdate = 0;
	/**
	 * @var int The modificationdate as UNIX timestamp
	 */
	protected $modificationdate = 0;
	/**
	 * @var boolean Indicates whether publication rules (by date) should be taken in account
	 */
	protected $alwayspublished = false;
	/**
	 * @var int The id of the template
	 */
	protected $itemType = 0;
	/**
	 * @var string The label of the template (for historical reasons, this is still called item instead of template)
	 */
	protected $itemLabel = '';
	/**
	 * @var string A string indicating how long this page may be cached by the server (eg. '4 hours')
	 */
	protected $lifetime = '';
	/**
	 * @var boolean Indicates whether the page should be shown in the main navigation
	 */
	protected $showinnavigation;
	/**
	 * @var string The path of the page, without BASEURL or language prefix;
	 * @since 2.4 - 9 nov 2010
	 */
	protected $path;
	
	/**
	 * @since 2.8
	 * @var int The id of the creator
	 */
	protected $createdby;
	/**
	 * @since 2.8
	 * @var int The id of the administrator who last modified this page
	 */
	protected $modifiedby;
	/**
	 * @since 2.8
	 * @var int The creationdate as UNIX timestamp
	 */
	protected $creationdate;

	/**
	 * @var string The basic template (place your css declarations / script files in this file)
	 */
	public $baseTemplate = 'default';
	/**
	 * @var string The view specific template, usually named the same as the view. Override this property to parse other views
	 */
	public $viewTemplate;
	/**
	 * @var string If you specify multiple templates in 1 file, you can specify the name of the specific template here
	 */
	public $templatename = '';

	function __construct($pageData) {
		parent::__construct($pageData);
		if(isset($_SESSION['prefix']))
			$this -> prefix = $_SESSION['prefix'];

		$this -> time = time();
		$this -> lang = $_SESSION['language'];
		$this -> baseurl = BASEURL;
		$this -> filesurl = BASEURL . UPLOADFOLDER;
		$this -> filespath = BASEPATH . UPLOADFOLDER;
		if(!$pageData)
			return;

		$this -> path = $pageData -> path;
		$this -> pageData = $pageData;
		$this -> treeId = $pageData -> treeId;
		$tree = new Tree();
		$nodes = $tree -> getCanonical($pageData -> page -> pageId);
		if(count($nodes) > 1 && (int)$nodes[0]['treeId'] != (int)$pageData -> treeId) {
			$this -> canonical = '<link rel="canonical" href="' . BASEURL . $this -> prefix . $nodes[0]['path'] . '" />';
		}


		// Sets all the page properties for easy manipulation
		foreach($pageData -> page as $name => $value) {
			if(property_exists($this, $name)) {
				$this -> $name = $value;
			}
		}

		$missing = array();
		// Sets all the content properties for easy manipulation
		foreach($pageData -> page -> content as $name => $value) {
			if(property_exists($this, $name)) {
				$lp = 'loc_' . $name;
				$this -> $name = $pageData -> page -> content -> $lp;
			}else {
				$missing[] = $name;
			}
		}
		if(count($missing) > 0 && defined('SHOWTEMPLATEERRORS') && SHOWTEMPLATEERRORS === true) {
			echo '<pre>';
			throw new Exception("The following variables are not defined in the template:\r\n\$" . implode("\r\n\$",$missing) ."\r\n");
		}

		$this -> viewTemplate = $this -> itemLabel;

		$exArr = explode(' ', $this -> pageData -> page -> lifetime);
		$time = time();
		$this -> expDate = time();
		$add = 0;
		if(count($exArr) > 1) {
			switch($exArr[1]) {
				case "year":
				case "years":
					$add = $exArr[0] * 31536000;
					break;
				case "month":
				case "months":
					 $add = $exArr[0] * 2592000;
					break;
				case "week":
				case "weeks":
					$add  = $exArr[0] * 604800;
					break;
				case "day":
				case "days":
					$add  = $exArr[0] * 86400;
					break;
				case "hour":
				case "hours":
					$add  = $exArr[0] * 3600;
					break;
				case "minute":
				case "minutes":
					$add = $exArr[0] * 60;
					break;
			}
		}
		$this -> revisit = floor($add / 86400);
		$this -> expDate += $add;
		// Set Cache header
		header('Expires: ' . date('r', $this -> expDate));
		header('Cache-control: max-age=' . $add);
		header('Last-Modified: ' . $this -> getModificationdate());
		if($exArr == 0)
			$this -> expDate = -1;
		
		
		if(method_exists($this, 'trimText'))
			trigger_error('Defining your own trimText is deprecated since the method has moved to BrightUtils, use BrightUtils::trimText');
		
	}

	protected function getModificationdate() {
		return date('r', $this -> modificationdate);
	}

	private $_tablestr;
	/**
	 * Gets all the defined variables in the view and how to access them in the template
	 * @since 2.5
	 */
	protected function getVariables() {

		$this -> _tablestr = '<table border="1" cellspacing="0" cellpadding="5" style="border:1px solid #ccc;font-family:Consolas, Courier New, Courier; font-size:10pt">'. "\r\n". '<tr><th>Name</th><th>Type</th><th>Excerpt</th>' . "\r\n";
		echo '<style>';
		echo '.integer, .double, .float { background-color: #AEF997;}';
		echo '.boolean { background-color: #D7DAC8;}';
		echo '.array { background-color: #CAF8ED;}';
		echo '.object { background-color: #F6CDAC;}';
		echo '</style>';
		echo '<div><h1>Available properties of ' . get_class($this) . '</h1>';
		echo $this -> _tablestr;
		$reflection = new ReflectionClass($this);
		$arr = $reflection->getdefaultProperties();
		ksort($arr);
		$this -> _processProps($arr, $this, '');
		echo '</table></div>';
	}

	private function _processProps($props, $obj, $prefix) {
		// First, loop over array and move objects & arrays to bottom
		$scalars = array();
		$nonscalars = array();
		foreach($props as $key => $value) {
			if(!is_scalar($obj -> $key) && $obj -> $key != null) {
				$nonscalars[$key] = $value;
			} else {
				$scalars[$key] = $value;
			}
		}

		foreach(array($scalars, $nonscalars) as $props) {
			foreach($props as $key => $value) {
				switch($key) {
					// Skip some keys
					case 'pageData':
						break;
					default:
						$type = gettype($obj -> {$key});
						$indent = count(explode('.', $prefix));
						$typeprefix = ($type == 'array') ? 'A_' : 'V_';
						echo "<tr class='$type'><td>&lt;!-- ###$typeprefix$prefix$key### --&gt;</td>\r\n";
						echo "<td>$type</td>\r\n";

						if(!is_scalar($obj -> {$key}) && $obj -> $key != null) {
							$indentpx = $indent * 10;
							echo "<td>$type</td>\r\n";
							echo "</tr>";
							echo '</table></div>';
							echo "<div style='margin-left: " . $indentpx . "px'><h3>Properties of &lt;!-- ###V_$prefix$key### --&gt;</h3>";
							echo $this -> _tablestr;
						}

						switch($type) {
							case 'integer':
							case 'double':
							case 'float':
								echo "<td>" . $obj -> {$key} . "</td>\r\n</tr>";
								break;
							case 'boolean':
								$bv = ($value) ? 'true' : 'false';
								echo "<td>$bv</td></tr>\r\n";
								break;
							case 'string':
								$str = (strlen($obj -> {$key}) > 100) ? substr(strip_tags($obj -> {$key}), 0, 100) : $obj -> {$key};
								echo "<td>$str&nbsp;</td>\r\n</tr>";
								break;
							case 'object':
								$arr = get_object_vars ($value);
								ksort($arr);
								$this -> _processProps($arr, $value, $prefix . $key . '.');
								break;
							case 'array':
								$this -> _processArray($obj -> $key, $prefix . $key . '.');
								break;
						}

				}
			}
		}
	}

	private function _processArray($array, $prefix) {
		foreach($array as $arrval => $value) {
			if(is_object($value)) {
				$arr = get_object_vars ($value);
				ksort($arr);
				$this -> _processProps($arr, $value, $prefix);

			} else if(is_array($value)) {
				$this -> _processArray($value, $prefix . $arrval . '.');
			}
		}
	}

	/**
	 * Sends the contents to the browser and outputs the page
	 * @param string template The template to use in the output
	 * @return string The page contents
	 */
	public function output($template='general') {
		$unt = 0;
		try {
			$unt = USENEWTEMPLATE;
		} catch(Exception $ex) {
			// Nothing
		}
		if($unt == 1) {
			return $this -> parseTemplate($this, $this -> baseTemplate, $this -> viewTemplate, $this -> templatename);
		}
		ob_start();
		include 'templates/' . $template . '.php';
		return ob_get_clean();
	}
}
