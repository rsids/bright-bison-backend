<?php
/**
 * Parses the templates
 * Version history:
 * 1.6 20130118
 * - Added static method templateExists
 * 1.5 20120621
 * - Added arrlength
 * @author Fur
 * @version 1.5
 * @package Frontend
 */
class Parser {

	private $_view;
	private $_viewtemplate;
	private $_tree;
	private $_constants;

	/**
	 * @var string Prefix of the website, "/{LANG}"
	 * @since 1.1 - 14 sep 2010 (Moved from generalview)
	 * @deprecated 28 oct 2010 use the /index.php?tid= syntax
	 */
	protected $prefix;

	/**
	 * @var string The language, same as prefix, but without the /
	 * @since 1.3 - 1 nov 2010 In time, this variable will replace prefix (IE, you have to place the slash yourself)
	 * @deprecated 28 oct 2010 use the /index.php?tid= syntax
	 */
	protected $lang;

	function __construct($pageData) {
		$this -> _tree = new Tree();
   		$this -> _constants = get_defined_constants();
	}

	/**
	 * Checks if a template exists
	 * @since 1.6
	 * @param string $file
	 * @param string $templateName
	 * @return bool
	 */
	public static function templateExists($file, $templateName) {
		$file = filter_var($file, FILTER_SANITIZE_STRING);
		$templateName = filter_var($templateName, FILTER_SANITIZE_STRING);
		if(!file_exists(BASEPATH . 'bright/site/templates/' . $file . '.html'))
			return false;
		
		$base = file_get_contents(BASEPATH . 'bright/site/templates/' . $file . '.html');
		if($templateName != '') {
				
			if(strpos($templateName, 'TEMPLATE_') === false)
				$templateName = 'TEMPLATE_' . $templateName;
				
			return
			(preg_match('/<!--[ ]*###' . $templateName . '_START###[ ]*-->/', $base, $arr, PREG_OFFSET_CAPTURE) > 0
			&&
			preg_match('/<!--[ ]*###' . $templateName . '_END###[ ]*-->/', $base, $arr, PREG_OFFSET_CAPTURE) > 0);
			
		}
	}

	/**
	 * Parses a html-template
	 * @param stdClass $data The object holding the variables / functions
	 * @param string $baseTemplate The name of the base template
	 * @param string $subTemplate The name of the optional sub template, which will be parsed inside basetemplate
	 * @param string $templateName The name of the template, if you have multiple templates defined in one file
	 * @throws Exception
	 * @return string The parsed template
	 */
	public function parseTemplate($data, $baseTemplate , $subTemplate = "", $templateName = "") {
		if(!file_exists(BASEPATH . 'bright/site/templates/' . $baseTemplate . '.html'))
			throw new Exception('Template ' . $baseTemplate . ' not found',0);

		$baseTemplate = addslashes($baseTemplate);
		$subTemplate = addslashes($subTemplate);
		$templateName = addslashes($templateName);
		$this -> _view = $data;
		$base = file_get_contents(BASEPATH . 'bright/site/templates/' . $baseTemplate . '.html');

		if($subTemplate != '') {
			if(!file_exists(BASEPATH . 'bright/site/templates/' . $subTemplate . '.html'))
				throw new Exception('Subtemplate ' . $subTemplate . ' not found',0);


			$this -> _viewtemplate = file_get_contents(BASEPATH . 'bright/site/templates/' . $subTemplate . '.html');

			//First, include view template
			preg_match('/<!--[ ]*###TEMPLATE_START###[ ]*-->/', $base, $arr, PREG_OFFSET_CAPTURE);
			if(!$arr) {
				throw new Exception('<!-- ###TEMPLATE_START### --> missing in ' . $baseTemplate ,0);
			}
			$baseindex_start = $arr[0][1];
			preg_match('/<!--[ ]*###TEMPLATE_END###[ ]*-->/', $base, $arr, PREG_OFFSET_CAPTURE);
			if(!$arr) {
				throw new Exception('<!-- ###TEMPLATE_END### --> missing in ' . $baseTemplate ,0);
			}
			$baseindex_end = $arr[0][1] + strlen($arr[0][0]) - $baseindex_start;
			
			preg_match('/<!--[ ]*###TEMPLATE_START###[ ]*-->/', $this -> _viewtemplate, $arr, PREG_OFFSET_CAPTURE);
			if(!array_key_exists(0, $arr))
				throw new Exception('<!-- ###TEMPLATE_START### --> missing in ' . $subTemplate ,0);
			$viewindex_start = $arr[0][1] + strlen($arr[0][0]);
			preg_match('/<!--[ ]*###TEMPLATE_END###[ ]*-->/', $this -> _viewtemplate, $arr, PREG_OFFSET_CAPTURE);
			if(!array_key_exists(0, $arr))
				throw new Exception('<!-- ###TEMPLATE_END### --> missing in ' . $subTemplate ,0);
			$viewindex_end = $arr[0][1]  - $viewindex_start;
			$base = substr_replace($base, substr($this -> _viewtemplate, $viewindex_start, $viewindex_end), $baseindex_start, $baseindex_end);

			// Find additional headers
			preg_match('/<!--[ ]*###HEADER###[ ]*-->/', $base, $arr, PREG_OFFSET_CAPTURE);
			if(count($arr) > 0) {
				$baseheaderindex_start = $arr[0][1];
				$baseheaderindex_end = strlen($arr[0][0]);

				preg_match('/<!--[ ]*###HEADER###[ ]*-->/', $this -> _viewtemplate, $arr, PREG_OFFSET_CAPTURE);
				$bots = "\r\n";
				//Non-live header:
				if(!LIVESERVER) {
					$bots = '<meta name="googlebot" content="noindex,noarchive,nofollow" />' . "\r\n" .
							'<meta name="robots" content="noindex,noarchive,nofollow" />';
				}
				if(count($arr) > 0) {
					$viewheaderindex_start = $arr[0][1] + strlen($arr[0][0]);
					preg_match('/<!--[ ]*###HEADER###[ ]*-->/', $this -> _viewtemplate, $arr, PREG_OFFSET_CAPTURE, $viewheaderindex_start);
					$viewheaderindex_end = $arr[0][1]  - $viewheaderindex_start;

					$bots .= substr($this -> _viewtemplate, $viewheaderindex_start, $viewheaderindex_end);
				}
				$base = substr_replace($base, $bots, $baseheaderindex_start, $baseheaderindex_end);
			}
			// Find additional footer
			preg_match('/<!--[ ]*###FOOTER###[ ]*-->/', $base, $arr, PREG_OFFSET_CAPTURE);
			if(count($arr) > 0) {
				$basefooterindex_start = $arr[0][1];
				$basefooterindex_end = strlen($arr[0][0]);

				preg_match('/<!--[ ]*###FOOTER###[ ]*-->/', $this -> _viewtemplate, $arr, PREG_OFFSET_CAPTURE);
				$bots = "\r\n";
				//Non-live footer:
	
				if(count($arr) > 0) {
					$viewfooterindex_start = $arr[0][1] + strlen($arr[0][0]);
					preg_match('/<!--[ ]*###FOOTER###[ ]*-->/', $this -> _viewtemplate, $arr, PREG_OFFSET_CAPTURE, $viewfooterindex_start);
					$viewfooterindex_end = $arr[0][1]  - $viewfooterindex_start;

					$bots .= substr($this -> _viewtemplate, $viewfooterindex_start, $viewfooterindex_end);
				}
				$base = substr_replace($base, $bots, $basefooterindex_start, $basefooterindex_end);
			}
		}

		if($templateName != '') {
			
			if(strpos($templateName, 'TEMPLATE_') === false)
				$templateName = 'TEMPLATE_' . $templateName;
			
			preg_match('/<!--[ ]*###' . $templateName . '_START###[ ]*-->/', $base, $arr, PREG_OFFSET_CAPTURE);
			if(count($arr) == 0)
				throw new Exception("Template $templateName not found in $subTemplate", 0);
			$viewindex_start = $arr[0][1] + strlen($arr[0][0]);
			preg_match('/<!--[ ]*###' . $templateName . '_END###[ ]*-->/', $base, $arr, PREG_OFFSET_CAPTURE);
			$viewindex_end = $arr[0][1]  - $viewindex_start;
			$base = substr($base, $viewindex_start, $viewindex_end);
		}
		//echo '<!--';
		$parsed = $this -> _parse($base);
		$parsed = preg_replace_callback('/\/index\.php\?tid=([0-9]*)/ism', array($this, '_buildpaths'), $parsed);
		$parsed = preg_replace_callback('/\/index\.php\?rtid=([0-9]*)/ism', array($this, '_buildrelativepaths'), $parsed);

		// Fix for IE7, which places the BASEURL in front of all /index.php links
		$bu = substr(BASEURL, 0, strlen(BASEURL) - 1);
		$parsed = str_replace($bu.BASEURL, BASEURL, $parsed);

		return $parsed;
	}

	/**
	 * Recursive parse function searches for <!-- ###_____### --> and replaces it with a value
	 * @since 2.2 - 26 apr 2010
	 * @param string $text The text with the markers
	 * @param mixed $data If available, data will be parsed from this object, instead of the view
	 * @param string $path A string representation of the data path (view -> array1 -> array2 -> variable results in array1.array2.variable
	 * @param int $arrayIndex the index of the array
	 * @param int $arrayLength the length of the array
	 * @throws Exception
	 * @return string The parsed template
	 */
	private function _parse($text, $data = null, $path = '', $arrayIndex = 0, $arrayLength = -1) {
		$current = 0;
		$offset = 0;
		$safety = 1000;
		$lang = str_replace('/', '',$this -> lang);
		while(preg_match('/<!--[ ]*###([A-z0-9_\.\,\-\:\/ ]*)###[ ]*-->\r*\n*/', $text, $arr, PREG_OFFSET_CAPTURE) != 0 && $safety != 0) {
			$datachain = array();
			$safety--;
			$extraparams = array();
			if(strpos($arr[1][0], ',')) {
				$extraparams = explode(',', $arr[1][0]);
				$arr10 = array_splice($extraparams,0,1);
				$arr[1][0] = $arr10[0];
			}
			$vararr = explode('_', $arr[1][0]);

			// Type: D, F, A, V, T, R
			$type = (count($vararr) > 1) ? $vararr[0] :null;
			$fullvar =
			$var = (count($vararr) > 1) ? $vararr[1] :$vararr[0];
			$extra = (count($vararr) > 2) ? $vararr[2] : null;

			$dataobject = $this -> _view;
			if($type != 'T' && strpos($var, '.') !== false) {
				// Nested variable
				$datachain = explode('.', $var);
				// Last variable is the actual var
				$var = array_pop($datachain);
				$strchain = '';
				foreach($datachain as $chainedvar) {

					$strchain .= $chainedvar . '.';
					if(isset($dataobject -> {$chainedvar})) {
						// Array value, get current item from the $data variable
						if(is_array($dataobject -> {$chainedvar})) {
							if(is_array($data) && array_key_exists($strchain, $data)) {
								$dataobject = $data[$strchain];
							}
						// Object, check if it exists in the $dataobject
						} elseif(is_object($dataobject -> {$chainedvar})) {
							$dataobject = $dataobject -> {$chainedvar};
						}
					} else {
						/**
						 * Setting the dataobject to null basically does the following:
						 * Suppose a page has a title 'mytitle'
						 * You have an object called myobject which is supposed to have a title
						 * When you do <!--###V_myobject.title###-->, it would normally print the page title 'mytitle',
						 * now, it will print nothing.
						 *
						 */
						$dataobject = null;
					}
				}


			} else if($path != '' && ($type == 'D' || $type == 'F')) {
				$dataobject = $data[$path];
			}

			if($extra && ($extra == 'START' || $extra == 'IF')) {
				//Startindex
				$end = ($extra == 'IF') ? 'IF' : '';
				$si = $arr[0][1] + strlen($arr[0][0]);

				// Find close tag
				$regex = '/<!--[ ]*###' . $type . '_' . $fullvar . '_END' . $end . '###[ ]*-->/';

				$m = preg_match($regex, $text, $ea, PREG_OFFSET_CAPTURE, $si);
				if($m == 0)
					throw new Exception('Missing Close tag "' . $arr[1][0] . '"');

				// Or else tag
				$regex = '/<!--[ ]*###' . $type . '_' . $fullvar . '_ELSE###[ ]*-->/';
				$m = preg_match($regex, $text, $else, PREG_OFFSET_CAPTURE, $si);


				$elseindex = -1;
				$endindex = $ea[0][1];
				if($m != 0 && $else[0][1] < $endindex) {
					// Else statement
					$elseindex = $else[0][1];
				}

				if($this -> checkVar($var, $dataobject)) {
					if($elseindex > -1) {
						// Remove else statement
						$text = substr_replace($text, '',$elseindex,$endindex + strlen($ea[0][0])-$elseindex);

					} else {
						// Remove closetag
						$text = substr_replace($text, '',$endindex, strlen($ea[0][0]));
					}
					// Remove if tag
					$text = substr_replace($text, '',$arr[0][1], strlen($arr[0][0]));

				} else {
					if($elseindex > -1) {
						// Remove closetag
						$text = substr_replace($text, '',$endindex, strlen($ea[0][0]));
						// Remove if statement
						$text = substr_replace($text, '',$arr[0][1], $elseindex + strlen($else[0][0]) - $arr[0][1]);

					} else {
						// Remove entire statement (no else condition)
						$text = substr_replace($text, '', $arr[0][1], $ea[0][1] + strlen($ea[0][0]) - $arr[0][1]);
					}

				}

			} else {

				switch($type) {
					case 'R':
						// Resource
						$text = substr_replace($text, Resources::getResource($var, $lang), $arr[0][1], strlen($arr[0][0]));
						break;
					case 'V':
						//String / Number variable;

						$valid = false;
						if(isset($dataobject -> {$var})) {
							$valid = true;
							$strdata = $dataobject -> {$var};
						} else if(is_array($dataobject) && array_key_exists($var, $dataobject)) {
							$valid = true;
							$strdata = $dataobject[$var];
						}

						if($valid) {
							switch(gettype($strdata)) {
								case 'string':
								case 'integer':
								case 'double':
									$text = substr_replace($text, $strdata, $arr[0][1], strlen($arr[0][0]));
									break;
								default:
									throw new Exception('<b>' . $var . '</b> is not a valid type, expected string or numeric, got ' . gettype($strdata) .
									"\r\nContaining: " . print_r($dataobject -> {$var}, true));
							}
						} else {
							$text = substr_replace($text, '', $arr[0][1], strlen($arr[0][0]));
						}

						break;

					case 'F':
						//Function
						switch($var) {
							default:
								if(method_exists($this -> _view, $var)) {
		
									if($path != '' || count($extraparams) > 0) {
										if(count($extraparams) == 0) {
											$extraparams = array(&$dataobject);
										} else if($path != '') {
											$last = array_pop($extraparams);
											if($last != 'NODATA') {
												array_splice($extraparams, 0,0, array(&$dataobject));
												$extraparams[] = $last;
											}
										}
										
										
										$text = substr_replace($text, 
																call_user_func_array(array($this -> _view, $var), 
																						$extraparams), 
																$arr[0][1], 
																strlen($arr[0][0]));
										//
									} else {
										$text = substr_replace($text, $this -> _view -> {$var}(), $arr[0][1], strlen($arr[0][0]));
									}
								} else if(function_exists($var)) {
									if($path != '' || count($extraparams) > 0) {
										if(count($extraparams) == 0) {
											$extraparams = array(&$dataobject);
										} else if($path != '') {
											$last = array_pop($extraparams);
											if($last != 'NODATA') {
												array_splice($extraparams, 0,0, array(&$dataobject));
												$extraparams[] = $last;
											}
										}
										$text = substr_replace($text, call_user_func_array($var, $extraparams), $arr[0][1], strlen($arr[0][0]));
										//
									} else {
										$text = substr_replace($text, call_user_func($var), $arr[0][1], strlen($arr[0][0]));
									}
								}else if(method_exists('BrightUtils', $var)) {
									if($path != '' || count($extraparams) > 0) {
										if(count($extraparams) == 0) {
											$extraparams = array(&$dataobject);
										} else if($path != '') {
											$last = array_pop($extraparams);
											if($last != 'NODATA') {
												array_splice($extraparams, 0,0, array(&$dataobject));
												$extraparams[] = $last;
											}
										}
										$text = substr_replace($text, call_user_func_array(array(BrightUtils, $var), $extraparams), $arr[0][1], strlen($arr[0][0]));
										//
									} else {
										$text = substr_replace($text, call_user_func(array(BrightUtils, $var)), $arr[0][1], strlen($arr[0][0]));
									}
								} else {
									$text = substr_replace($text, '', $arr[0][1], strlen($arr[0][0]));
								}
						}
						break;

					case 'A':
						//Array;
						//Get part
						$si = $arr[0][1] + strlen($arr[0][0]);
						$regex = '/<!--[ ]*###' . $arr[1][0] . '###[ ]*-->/';

						// Find close tag
						preg_match($regex, $text, $ea, PREG_OFFSET_CAPTURE, $si);

						if(count($ea) == 0)
							throw new Exception('Missing Close tag "' . $arr[1][0] . '"');

						$ei = $ea[0][1];
						$part = substr($text, $si, $ei - $si);

						$repl = '';
						// Added is_array
						if(isset($dataobject -> {$var}) && is_array($dataobject -> {$var})) {
							$i = 0;
							foreach($dataobject -> {$var} as $item) {
								$p = (count($datachain) > 0) ? implode('.', $datachain) . '.' . $var . '.' : $var . '.';
								$data[$p] = $item;
								$repl .= $this -> _parse($part, $data, $p, $i, count($datachain));
								$i++;
							}
							$text = substr_replace($text, $repl, $arr[0][1], $ea[0][1] + strlen($ea[0][0]) - $arr[0][1]);
						} else {
							$text = substr_replace($text, '', $arr[0][1], $ea[0][1] + strlen($ea[0][0]) - $arr[0][1]);

						}
						break;

					case 'I':
						$text = substr_replace($text, $arrayIndex, $arr[0][1], strlen($arr[0][0]));
						break;

					case 'D':
						if(isset($dataobject -> {$var})) {
							$text = substr_replace($text, $dataobject -> {$var}, $arr[0][1], strlen($arr[0][0]));
						} else {
							if($dataobject instanceof OTreeNode) {
								if(isset($dataobject -> page -> {$var})) {
									$text = substr_replace($text, $dataobject -> page -> {$var}, $arr[0][1], strlen($arr[0][0]));

								} else if(isset($dataobject -> page -> content -> {$var})){
									$lv = 'loc_' . $var;
									$text = substr_replace($text, $dataobject -> page -> content -> {$lv}, $arr[0][1], strlen($arr[0][0]));

								}
							} else if($dataobject instanceof OPage) {
								if(isset($dataobject -> content -> {$var})){
									$lv = 'loc_' . $var;
									$text = substr_replace($text, $dataobject -> content -> {$lv}, $arr[0][1], strlen($arr[0][0]));

								}
							} else {
								$text = substr_replace($text, '', $arr[0][1], strlen($arr[0][0]));
							}

						}

						break;
					case 'T':
						// NEW! Load a template Part!
						$subtemplate = $this -> _viewtemplate;

						if(strpos($var, '.') !== false) {
							$tarr = explode('.', $var);
							if(!file_exists(BASEPATH . 'bright/site/templates/' . addslashes($tarr[0]) . '.html'))
								throw new Exception('Template file "' . addslashes($tarr[0]) . '" not found');
							$var = $tarr[1];
							$subtemplate = file_get_contents(BASEPATH . 'bright/site/templates/' . addslashes($tarr[0]) . '.html');
						}

						preg_match('/<!--[ ]*###TEMPLATE_' . $var . '_START###[ ]*-->/', $subtemplate, $sub_arr, PREG_OFFSET_CAPTURE);
						if(count($sub_arr) == 0)
							throw new Exception('Template "' . $var . '" not found');

						$subtemplate_start = $sub_arr[0][1] + strlen($sub_arr[0][0]);
						preg_match('/<!--[ ]*###TEMPLATE_' . $var . '_END###[ ]*-->/', $subtemplate, $sub_arr, PREG_OFFSET_CAPTURE);
						if(count($sub_arr) == 0)
							throw new Exception('Close tag for template "' . $var . '" not found');
						$subtemplate_end = $sub_arr[0][1]  - $subtemplate_start;

						$subtemplatecontent = substr($subtemplate, $subtemplate_start, $subtemplate_end);
						$text = substr_replace($text, $subtemplatecontent, $arr[0][1], strlen($arr[0][0]));
						break;
					case null:
						if(defined($var)) {
							$text = substr_replace($text, $this -> _constants[$var],$arr[0][1], strlen($arr[0][0]));
						} else {
							switch($var) {
								case 'VALUE':
									// $data[$path]
									$text = substr_replace($text, $data[$path],$arr[0][1], strlen($arr[0][0]));
									break;

								case 'DUMMY':
									$si = $arr[0][1] + strlen($arr[0][0]);
									$regex = '/<!--[ ]*###DUMMY###[ ]*-->/';

									// Find close tag
									preg_match($regex, $text, $ea, PREG_OFFSET_CAPTURE, $si);
									if(count($ea) == 0)
										throw new Exception('Missing Close tag "dummy"');

									$ei = $ea[0][1];

									$text = substr_replace($text, '', $arr[0][1], $ea[0][1] + strlen($ea[0][0]) - $arr[0][1]);
									break;
							}
						}
						break;
				}
			}
		$current++;
		}
		return $text;
	}

    /**
     * Checks if a variable exists in view
     * @param string $var The name of the variable to check
     * @param array $data
     * @return bool
     */
	public function checkVar($var, $data = null) {
		if(method_exists($this -> _view, $var)) {

			return $this -> _view -> {$var}($data);
		}
		if(isset($data -> {$var})) {

			if(!$data -> {$var} || $data -> {$var} === 'false' || $data -> {$var} === false)
				return false;

			return true;
		}

		if(is_array($data) && array_key_exists($var, $data)) {

			if(!$data[$var])
				return false;

			return true;
		}
		return false;

	}
	
	private function _buildpaths($matches) {
		$tid = $matches[1];
		if(USEPREFIX && (!$this -> prefix || $this -> prefix == '')) {
			$langs = explode(',', AVAILABLELANG);
			$this -> prefix = $langs[0] . '/';
		}

		return BASEURL . $this -> prefix . $this -> _tree -> getPath((int)$tid);

	}
	
	private function _buildrelativepaths($matches) {
		$tid = $matches[1];
		if(USEPREFIX && (!$this -> prefix || $this -> prefix == '')) {
			$langs = explode(',', AVAILABLELANG);
			$this -> prefix = $langs[0] . '/';
		}

		return '/' . $this -> prefix . $this -> _tree -> getPath((int)$tid);

	}
}
