<?php
require_once('BaseSearch.php');
require_once('Settings.php');
require_once('Categories.php');

/**
 *
 * @author Ids
 * @version 1.0
 * @copyright 2011 Fur
 * An API for the sphider plus package
 */
class Search extends BaseSearch {

	private $_blacklist;
	private $_s;

	/**
	 * Holds all common words
	 * @var array
	 */
	private $_common;


	/**
	 * Constructor, create the search class with your custom settings object
	 * @see bright/search/Settings
	 * @param $settings
	 */
	function __construct(){
		parent::__construct();
		$this -> _s = Settings::getInstance();
	}

	public function performsearch() {
    	$start_all   = Common::getmicrotime();
    	/**
    	 * @todo replace with php filter functions
    	 * @var unknown_type
    	 */
		$params = array('query' => 255,			'search' => 10,			'domain' => 255,
						'type' => 10,			'catid' => 10,			'category' => 255,
						'mark' => 64,			'results' => 10,		'start' => 10,
						'start_links' => 10,	'adv' => 10,			'media_type' => 10,
						'media_only' => 10,		'link' => 255,			'title' => 255,
						'db' => 1,				'prefix' => 20,			'sort' => 20,
						'submit' => 20);
		$this -> parameters = new Parameters();
		foreach($params as $param => $len) {
			if(isset($_GET[$param])) {
				$this -> parameters	-> {$param} = $this -> _cleaninput(substr(trim(urldecode ($_GET[$param])),0, $len));
			}
		}
		$nostalgic_phrase = '';
	    if (strpos($this -> parameters	-> query, "\"")) {
	        $nostalgic_phrase = '1';
	        $this -> parameters	-> query = str_replace('"', '', $this -> parameters	-> query);
	    }

	    $this -> _processQuery();


	    //  overwrite the configuration setting with respect to users decision
	    if(isset($this -> parameters -> mark) && $this -> parameters -> mark !='') {
	        $this -> _s -> mark = $this -> parameters -> mark;
	    }

		if ($this -> _s -> mb == 1) {
        	mb_internal_encoding("UTF-8");      //  define standard charset for mb functions
    	}

	    //      if requested by Search-form, overwrite default db number
	    // @todo check if this is nesseccary
	    if ($this -> parameters	-> db > 0 && $this -> parameters -> db <= 5) {
	        Configuration::getInstance() -> dbu_act = $this -> parameters	-> db;
	    }
	    $db = Configuration::getInstance() -> dbu_act;

		Connection::getInstance() -> reconnect(Configuration::getInstance() -> {"mysql_host$db"}, Configuration::getInstance() -> {"mysql_user$db"}, Configuration::getInstance() -> {"mysql_password$db"}, Configuration::getInstance() -> {"database$db"});
	    $this -> _s  -> mysql_table_prefix = Configuration::getInstance() -> getTablePrefix();

	    //      if requested by Search-form, overwrite default table prefix
	    if ($this -> parameters	-> prefix != 0 && $this -> parameters -> prefix != '') {
	        $this -> _s -> mysql_table_prefix = $this -> parameters -> prefix;
	    }
	    $result = '';
	    if ($this -> _s -> use_ids == 1){     // if Intrusion Detection System should be used
			require_once("IDSHandler.php");
			$ih = new IDSHandler();
			$result = $ih -> setupIds();

	    	if (strlen($result) > 13) {
				//  get impact of intrusion
				$len = strpos($result, "<")-13;
				$res = trim(substr($result, '13', $len));
				if ($res >= $this -> _s -> ids_warn) {
					throw new IDSException(IDSException::IDSBLOCKED);
        		}
    		}

    		$ih -> checkBlockedIDS();
	    }

	    if ($this -> _s -> show_media == 1) {
        	include_once("SearchMedia.php");
	    }

		$this -> _getCommonwords();


    	include "L10N/{$this -> _s -> language}-language.php";
		Model::getInstance() -> L10N = $sph_messages;


		$this -> parameters -> mark = 'markbold';

		$categories = new Categories();
		if ($this -> parameters -> catid && is_numeric($this -> parameters -> catid)){
			$cattree = array(" ",$sph_messages['Categories']);
			$cat_info = $categories -> get_category_info($this -> parameters -> catid);
			foreach ($cat_info['cat_tree'] as $_val){
				$thiscat = $_val['category'];
				array_push($cattree," > ",$thiscat);
			}
			$cattree = implode($cattree);
		}

		//now follow the advanced search form for text and media search
		if ($this -> parameters -> submit) {
			if ($this -> parameters -> submit == $sph_messages['t_search']) {
				$this -> parameters -> text_only = "1";
			}
			if ($this -> parameters -> submit == $sph_messages['m_search']) {
				$this -> parameters -> media_only = "1";
			}
		}

		// Strict check was here

		if ($this -> parameters -> type != "or" && $this -> parameters -> type != "and" && $this -> parameters -> type != "phrase" && $this -> parameters -> type != "tol") {
			$this -> parameters -> type = "and";
		}

		if ($this -> parameters -> results != "") {
			$this -> _s -> results_per_page = $this -> parameters -> results;
		}

		if (!is_numeric($this -> parameters -> catid)) {
			$this -> parameters -> catid = "";
		}

		if (!is_numeric($this -> parameters -> category)) {
			$this -> parameters -> category = "-1";
		}

		$checked_cat = '';
		$checked_all = '';

		if ($this -> parameters -> category == '-1') {
			$checked_all = 'checked="checked"';   //  remember that last query was for all sites
		} else {
			$checked_cat = 'checked="checked"';   //  remember that last query was in category
		}

		if ($this -> parameters -> catid && is_numeric($this -> parameters -> catid)) {
			/**
			 * @todo Fix and find out what it's for
			 * @var unknown_type
			 */
				$tpl_['category'] = Connection::getInstance() -> getRowsIndexedArray('SELECT category FROM '.$this -> _s -> mysql_table_prefix.'categories WHERE category_id='.(int)$this -> parameters -> catid);
		}

		/**
		 * @todo Used in SearchMedia.php, which is not implemented yet
		 */
		$has_categories = Connection::getInstance() -> getField('SELECT count(*) FROM '.$this -> _s -> mysql_table_prefix.'categories WHERE parent_num=0');
		$has_categories = ($has_categories == null) ? 0 : $has_categories;

//		$type_rem   = $this -> parameters -> type;
//		$result_rem = Settings::getInstance() -> results_per_page;
//		$mark_rem   = $this -> parameters -> mark;
//		$sort_rem   = $this -> parameters -> sort;
//		$catid_rem  = $this -> parameters -> catid;
//		$cat_rem	= $this -> parameters -> category;



		switch ($this -> parameters -> search) {
			case 1:
				if (!isset($this -> parameters -> results)) {
					$this -> parameters -> results = "";
				}

				$multi_word = strpos($this -> parameters -> query, " ");	  //  check, whether the query contains a 'blank' character?

				//  if search without quotes is activated in Admin settings
				if ($this -> _s -> no_quotes == '1' && !$multi_word) {
					$this -> parameters -> query = preg_replace("/&#8216;|&lsquo;|&#8217;|&rsquo;|&#8242;|&prime;|â€˜|â€˜|Â´|`/", "'", $this -> parameters -> query);
					$quote = strstr($this -> parameters -> query, "'");
					if ($quote && !$strict_search) {
						$q_pos = strpos($this -> parameters -> query, "'");
						$word1 =  substr($this -> parameters -> query, 0, $q_pos);
						$word2 =  substr($this -> parameters -> query, $q_pos+1);
						$this -> parameters -> query = '';
						if (strlen($word1) >= $this -> _s -> min_word_length) {
							$this -> parameters -> query = $word1;
						}

						if (strlen($word2) >= $this -> _s -> min_word_length) {
							//$this -> parameters -> query .= " ".$word2."";	  //  depending on some Admin 'spider' settings, this does not always deliver results
							$this -> parameters -> query .= "*".$word2."";
						}
					}
				}

				//$this -> parameters -> query = trim($this -> parameters -> query);
				if ($this -> parameters -> query == ''){	//  don't care about 'blank' queries
					break;
				}

				if (!$this -> parameters -> media_only) {
					$loop = '1';

					//  search for text results
					$text_results = $this -> get_text_results(	$this -> parameters -> query,
																$this -> parameters -> start,
																$this -> parameters -> category,
																$this -> parameters -> type,
																$this -> parameters -> results,
																$this -> parameters -> domain,
																$loop,
																$this -> parameters -> orig_query,
																$this -> _s -> mysql_table_prefix);
				//	extract($text_results);   // get the text results

					if ($text_results['total_results'] == '' && $this -> _s -> translit_el != "1") {	 //	  if nothing found, try to find something written without hyphen,comma, dot etc.
						$this -> parameters -> query = strtr($this -> parameters -> query, "-,.?CIII", "	ICII");
						$loop = '2';
						$text_results = $this -> get_text_results(	$this -> parameters -> query,
																	$this -> parameters -> start,
																	$this -> parameters -> category,
																	$this -> parameters -> type,
																	$this -> parameters -> results,
																	$this -> parameters -> domain,
																	$loop,
																	$this -> parameters -> orig_query,
																	$this -> _s -> mysql_table_prefix);

				//		extract($text_results);   // get the text results
					}
				}

				//  search only for media results
				if ($this -> _s -> show_media == '1' && $this -> parameters -> media_only == '1') {
					$this -> media_only($this -> parameters -> query, $this -> parameters -> start, $this -> parameters -> media_only, $this -> parameters -> type, $this -> parameters -> category, $this -> parameters -> catid, $mark, $db, $this -> _s -> mysql_table_prefix, $this -> parameters -> domain);
					break;
				}

				if (array_key_exists('ignore_words',$text_results) && $this -> parameters -> type !='phrase'){
					$ignored = '';
					while ($thisword = each($text_results['ignore_words'])) {
						$ignored .= ", ".$thisword[1];
					}
					$ignored = substr($ignored, 1);
				}

				if ($this -> _s -> debug == '2') {
					$slv1 = '';
					$slv2 = '';
					$slv3 = '';
					$slv4 = '';
					$slv5 = '';
					if (Configuration::getInstance() -> db1_slv == 1 && !$this -> _s -> user_db || $this -> _s -> user_db == 1)  $slv1 = '1,';
					if (Configuration::getInstance() -> db2_slv == 1 && !$this -> _s -> user_db || $this -> _s -> user_db == 2)  $slv2 = '2,';
					if (Configuration::getInstance() -> db3_slv == 1 && !$this -> _s -> user_db || $this -> _s -> user_db == 3)  $slv3 = '3,';
					if (Configuration::getInstance() -> db4_slv == 1 && !$this -> _s -> user_db || $this -> _s -> user_db == 4)  $slv4 = '4,';
					if (Configuration::getInstance() -> db5_slv == 1 && !$this -> _s -> user_db || $this -> _s -> user_db == 5)  $slv5 = '5';
				}

				if ($text_results['total_results']== 0){   //	  if query did not match any keyword or any media
					$catname	= '';
					$catsearch  = '';

					if ($this -> parameters -> category != '-1') {	// if active search in categories, enter here
						$catname = $tpl_['category'][0]['category'];
						$catsearch  = $sph_messages['catsearch'];
					}
					$no_res = str_replace ('%query', $this -> parameters -> orig_query, $sph_messages["noMatch"]);
				}

				//	  Now prepare the text results  and eventually also media results
				if ($text_results['total_results'] != 0 && $text_results['from'] <= $text_results['to']){   // this is the standard results header
					$result = $sph_messages['Results'];
					$result = str_replace ('%from', $text_results['from'], $result);
					$result = str_replace ('%to', $text_results['to'], $result);
					$result = str_replace ('%all', $text_results['total_results'], $result);

					if ($this -> _s -> advanced_search == 1 && $this -> _s -> show_categories == 1 && $this -> parameters -> category != '-1') {	// additional headline for category search results
						$catname = $tpl_['category'][0]['category'];
						if ($catname != '') {

							$highlight  = "span class=\"higlightcategory\"";	  // comment this row for standard highlighting
							$catname	= "<".$highlight.">".$catname."</span>";
							$result	 = "$result<br />";
							$catsearch  = $sph_messages['catsearch'];
							$result	 = "".$result." ".$catsearch." ".$catname."";
						} else {
							$result = $sph_messages['catselect'];
						}
					}

					$matchword = $sph_messages["matches"];
					if ($text_results['total_results']== 1) {
						$matchword= $sph_messages["match"];
					} else {
						$matchword= $sph_messages["matches"];
					}
					$result = str_replace ('%matchword', $matchword, $result);
					$result = str_replace ('%secs', $text_results['time'], $result);

					if ($this -> _s -> show_sort == '1' && $this -> parameters -> wildcount != '1') {
						$res_order = $sph_messages['ResultOrder'];	// show order of result listing
						if ($sort_results == '1') {
							$this_list = $sph_messages['order1'];
						}
						if ($sort_results == '2') {
							$this_list = $sph_messages['order2'];
						}
						if ($sort_results == '3') {
							$this_list = $sph_messages['order3'];
						}
						if ($sort_results == '4') {
							$this_list = $sph_messages['order4'];
						}
						if ($sort_results == '5') {
							$this_list = $sph_messages['order5'];
						}
						if ($sort_results == '6') {
							$this_list = $sph_messages['order6'];
						}
						if ($sort_results == '7') {
							$this_list = $sph_messages['order7'];
						}
					}
				}

		//	  display result header
				//include "".$template_dir."/html/050_result-header.html";

				//  if no text results, alternatively search for media results
				//  but only if media results should be shown
				//  and 'text only' is not selected in advanced search form
				if ($text_results['total_results'] == '' && $this -> _s -> show_media == '1' && $submit != $sph_messages['t_search']) {
					$this -> media_only($this -> parameters -> orig_query, $start, $this -> parameters -> media_only, $type, $this -> parameters -> category, $this -> parameters -> catid, $mark, $db, $this -> _s -> mysql_table_prefix, $this -> parameters -> domain);
					break;
				}

				if (isset($text_results['qry_results'])) {  //  start of result listing
					$known_host = '1';
					$class = "evrow";	   //  in order to start with something
					$media_results = array();
					foreach ($text_results['qry_results'] as &$resultitem){
						$resultitem['hits'] = $resultitem['weight'];
						$change = '1';

						if ($this -> _s -> show_query_scores == 0 || $this -> _s -> sort_results > '2' || $this -> parameters -> wildcount == '1') {
							$resultitem['indexdate']  = $resultitem['weight'];  //  remember the indexdate
							//$weight	 = '';
						}

						$title1	 = strip_tags($resultitem['title']);
						$urlx	   = $resultitem['url2'];


						if ($this -> _s -> more_catres == '1'){
							$catidx = ($this -> parameters -> catid != null) ? $this -> parameters -> catid : $this -> parameters -> category;   //  separate catid for cat selection


							$catlist	= $categories -> findcats($urlx, $this -> parameters -> category, $catidx, $this -> _s -> mysql_table_prefix);
							$catlinks   = array ();
							$catlink	= '';

							foreach ($catlist as $value) {
								$res = mysql_query("select category_id from ".$this -> _s -> mysql_table_prefix."categories where category like '$value'");   //  get cat_id for this category

								$catidx = mysql_result($res, 0);

								if ($catidx) {
									//  build complete query for cat search
									$catlink = (object)array('catid' => $catidx, 'category' => $value);
									$catlinks[] = $catlink;
								}
							}
							$resultitem['catlinks'] = $catlinks;
						}
					}
//					foreach ($text_results['qry_results'] as $_key => $_row){
//						//$last_domain = $domain_name;
//						extract($_row);
//						$hits = $weight;
////
////						//	  prepare current page-url for click counter
////						$url_crypt  = str_replace("&", "-_-", $url);	//  crypt the & character
////						$url_click  = "$include_dir/click_counter.php?url=$url_crypt&amp;query={$this -> parameters -> query}&amp;db=$db&amp;prefix=$prefix";   //  redirect users click in order to update Most Popular Links
////
//						//  prepare the category selection for each result
//		//	   display result-listing
//						//include "".$template_dir."/html/060_text-results.html";
//						$known_host = $new_host;	//  remember host of this link (for domain sorting)
//
//					}//  end of result listing
					if (isset($other_pages)) {
						if ($adv==1) {
							$adv_qry = "&amp;adv=1";
						}
						if ($type != "") {
							$type_qry = "&amp;type=$type";
						}
					}

				}
				break;
		}

		if(!isset($text_results))
			return (object)array('totalresults' => 0);

		$text_results = (object) $text_results;
		$tresults = new StdClass();
		foreach($text_results as $key => $value) {
			$nk = explode('_', $key);
			$nk = implode('', $nk);
			$tresults -> {$nk} = $value;

		}
		if($tresults -> numofresults > 0) {
			foreach($tresults -> qryresults as &$result) {
				$result = (object)$result;
			}
		}

		return $tresults;
	}

	private function _cleaninput($input) {

        if (get_magic_quotes_gpc()) {
            $input = stripslashes($input);  //      delete quotes
        }

        //      prevent SQL-injection
        if (substr_count($input,"'") != '1') {
            $input = Connection::getInstance() -> escape_string($input);
        } else {
            $input = str_replace('\\','\\\\', $input);  //      if one slash is part of the query, we have to allow it  . . .
            $input = str_replace('"','\"', $input);     //      never the less we need to prevent SQL attacks
        }

        //	prevent XSS-attack and Shell-execute
        if (preg_match("/%FF%FE%3C%73%63%72%69%70%74%3E/i",$input)) {   //  tr/vb.hpq trojan
            $input = '';
        }
        if (preg_match("/cmd|CREATE|DELETE|DROP|eval|EXEC|File|INSERT|printf/i",$input)) {
            $input = '';
        }
        if (preg_match("/LOCK|PROCESSLIST|SELECT|shell|SHOW|SHUTDOWN/i",$input)) {
            $input = '';
        }
        if (preg_match("/SQL|SYSTEM|TRUNCATE|UNION|UPDATE|DUMP/i",$input)) {
            $input = '';
        }

        //  suppress JavaScript execution and tag inclusions
        $input = $this -> _unsafe($input);

        return $input;
    }

    /**
     * Gets the common words which are ignored by the search
     * @return array an array of common words
     */
    private function _getCommonwords() {
    	$common_dir = str_replace('//', '/', $this -> _s -> commondir . '/');

		if (is_dir($this -> _s -> commondir)) {
			// Get ALL common words
			if ($this -> _s -> use_common == 'all') {
				$files = scandir($common_dir);
				foreach($files as $common_file) {
					if (strpos($common_file, "ommon_")) {
						//  get content of actual common file
						$act = @file($common_dir.$common_file);
						//  build a complete array of common words
						Model::getInstance() -> all = array_merge(Model::getInstance() -> all, $act);
					}
				}
			}

			if ($this -> _s -> use_common != 'all' && $this -> _s -> use_common != 'none') {
				//  get content of language specific common file
				Model::getInstance() -> all = @file("{$common_dir}common_{$this -> _s -> use_common}.txt");
			}

			if ($this -> _s -> kill_query == '1'){
				$black_in = @file($common_dir.'blacklist.txt');	 //  get all words to prevent indexing of page
				if (is_array($black_in)) {
					foreach ($black_in as $val) {
						if ($this -> _s -> case_sensitive == '0') {
							$val = Common::lower_case($val);
						}
						$val = @iconv($this -> _s -> home_charset,"UTF-8",$val);
						$black[] = $val;
					}

					while (list($id, $word) = each($black))
						$blacklist[] = trim($word);
						$blacklist = array_unique($blacklist);
						sort($blacklist);

					 // delete input if query contains any word of blacklist
					if (count($blacklist) >= '1') {
						$kill = implode("|", $blacklist);
						if (preg_match("/$kill/i",$this -> parameters -> query)) {
							$this -> parameters -> query = '';
						}
					}
				}
			}

			if (is_array(Model::getInstance() -> all)) {
				while (list($id, $word) = each(Model::getInstance() -> all))
					Settings::getInstance() -> common[trim($word)] = 1;
			}
		}
    }

    private function _isDomainSearch() {
    //	  perhaps we want to search for all pages of a site or perform a domain restricted search
    	$pos = strstr(strtolower($this -> parameters -> query),"site:");
		if ($pos) {
			$domain_search = substr($this -> parameters -> query, '5');
			//	  If you want to search for all pages of a site by: site:abc.de
//					if (!strrpos($domain_search, ' ')){
//						include ("$include_dir/search_links.php");
//					}

			//	  must be domain search, by: site:abc.de query
			$this -> parameters -> query = substr($domain_search, strpos($domain_search, ' ')+1);  //  extract query
			$dom_url = substr($domain_search, 0, strpos($domain_search, ' '));  //  extract domain

			//  buid domain URL
			$url = parse_url($dom_url);
			$hostname = $url[host];

			//  rebuild domain for localhost applications
			if ($hostname == 'localhost') {
				$host1 = str_replace($local,'',$dom_url);
			}

			$pos = strpos($host1, "/");		 //	  on local server delete all behind the /
			if ($pos) {
				$host1 = substr($host1,0,$pos); //	  build full adress again, now only local domain
			}

			if ($hostname == 'localhost') {
				$domain = ("".$local."".$host1."/");
				$domain = str_replace("http://",'',$domain);
			}else {
				$domain = $hostname;
			}

			// @todo save domain
		}

    }

    private function _processQuery() {
   		 //  if requested by query, overwrite search type to AND
	    if (strpos($this -> parameters	-> query, " && ")){
	        $this -> parameters	-> type   = "and";
	    }

	    //  if requested by query, overwrite search type to OR
	    if (strpos($this -> parameters	-> query, " || ")){
	        $this -> parameters	-> type   = "or";
	    }

    	// Check for strictmode
		$strictpos = strpos($this -> parameters -> query, '!');
		// Any wildcards used?
		$this -> parameters -> wildcount = substr_count($this -> parameters -> query, '*');
		$strict_search = false;
		if ($this -> parameters -> wildcount || $strictpos === 0) {
			if ($this -> parameters -> type != 'and') {
				$this -> parameters -> mustbe_and = '1';
			}

			//  if wildcard, or strict search mode, switch always to AND search
			$this -> parameters -> type = 'and';
			// @todo save in a setting
			//  prevent wildcard for quotes search
			$strict_search = true;

		}

		if($this -> parameters -> type =='tol' || $strict_search) {
			if(strpos($this -> parameters -> query, " ", 3)) {
				// only the first word of the query will be used for these search modes
				$this -> parameters -> query = substr($this -> parameters -> query, 0, strpos($this -> parameters -> query, " ", 3));
				// @todo save in a setting
				$one_word = '1';
			}
		}

    	//  if search with wildcards is activated in Admin settings for queries containing numbers
		if ($this -> _s -> wild_num == '1' && preg_match("/[0-9]/i", $this -> parameters -> query ) && !strstr($this -> parameters -> query, " ") && !strstr($this -> parameters -> query, "*")) {
			$this -> parameters -> query = "*{$this -> parameters -> query}*";
		}

		//	  kill remained backslash
		$this -> parameters -> query = str_replace("\\", "", $this -> parameters -> query);

		// Save the original query with all the AND / OR parameters
		$this -> parameters -> orig_query = $this -> parameters -> query;

		//  if requested by query, kill AND characters
		if (strpos($this -> parameters -> query, " && ")){
			$this -> parameters -> query  = str_replace(" && ", " ",$this -> parameters -> query);
		}

		//  if requested by query, kill OR characters
		if (strpos($this -> parameters -> query, " || ")){
			$this -> parameters -> query  = str_replace(" || ", " ",$this -> parameters -> query);
		}

		$this -> parameters -> query = str_replace('http://', '', $this -> parameters -> query);	//	  URL's are stored without this in database
		$this -> parameters -> query = preg_replace("/&nbsp;/", " ", $this -> parameters -> query); //	  replace '&nbsp;' with " "
		$this -> parameters -> query = preg_replace("/&apos;/", "'", $this -> parameters -> query); //	  replace '&apos;' with " ' "
    }

	/**
	 * Clean input from any unsave javascript content
	 * @param string $input The input to check
	 * @return string The cleaned input
	 */
    private function _unsafe($input) {
	  	$UNSAFE_IN = array("/javascript\s*:/i", "/vbscri?pt\s*:/i", "/<\s*embed.*swf/i", "/<[^>]*[^a-z]onabort\s*=/i", "/<[^>]*[^a-z]onblur\s*=/i", "/<[^>]*[^a-z]onchange\s*=/i", "/<[^>]*[^a-z]onfocus\s*=/i", "/<[^>]*[^a-z]onmouseout\s*=/i", "/<[^>]*[^a-z]onmouseover\s*=/i", "/<[^>]*[^a-z]onload\s*=/i", "/<[^>]*[^a-z]onreset\s*=/i", "/<[^>]*[^a-z]onselect\s*=/i", "/<[^>]*[^a-z]onsubmit\s*=/i", "/<[^>]*[^a-z]onunload\s*=/i", "/<[^>]*[^a-z]onerror\s*=/i", "/<[^>]*[^a-z]onclick\s*=/i", "/onabort\s*=/i", "/onblur\s*=/i", "/onchange\s*=/i", "/onfocus\s*=/i", "/onmouseout\s*=/i", "/onmouseover\s*=/i", "/onload\s*=/i", "/onreset\s*=/i", "/onselect\s*=/i", "/onsubmit\s*=/i", "/onunload\s*=/i", "/onerror\s*=/i", "/onclick\s*=/i");

        foreach ($UNSAFE_IN as $match) {
            if( preg_match($match, $input)) {
                $input = '';
                return $input;
            }
        }
        return $input;
    }

}