<?php
/**
 * Base class for the search.
 * The search is based on the Sphider plus package and ported to an OOP structure
 * If you want to use it outside bright, simple do not extend Permissions
 * @author Ids
 *
 */
require_once(dirname(__FILE__) . '/../Permissions.php');
require_once(dirname(__FILE__) . '/Common.php');
require_once(dirname(__FILE__) . '/Configuration.php');
require_once(dirname(__FILE__) . '/Model.php');
require_once(dirname(__FILE__) . '/Parameters.php');

class BaseSearch extends Permissions {


	/**
	 * Holds the sanitized GET parameters
	 * @var object
	 */
	protected$parameters;

	/**
	 * Holds the settings singleton;
	 * @var unknown_type
	 */
	private $_s;

	private $_allwild;

	function __construct() {
		parent::__construct();
		$this -> _s = Settings::getInstance();
		error_reporting(E_ALL|E_STRICT);
	}


	public function swap_max (&$arr, $start, $domain) {
		$pos  = $start;
		$maxweight = $arr[$pos]['weight'];
		for  ($i = $start; $i< count($arr); $i++) {
			if ($arr[$i]['domain'] == $domain) {
				$pos = $i;
				$maxweight = $arr[$i]['weight'];
				break;
			}
			if ($arr[$i]['weight'] > $maxweight) {
				$pos = $i;
				$maxweight = $arr[$i]['weight'];
			}
		}
		$temp = $arr[$start];
		$arr[$start] = $arr[$pos];
		$arr[$pos] = $temp;
	}

	public function sort_with_domains (&$arr) {
		$domain = -1;
		for  ($i = 0; $i< count($arr)-1; $i++) {
			swap_max($arr, $i, $domain);
			$domain = $arr[$i]['domain'];
		}
	}

	public function sort_by_bestclick (&$arr) {
		$click_counter = -1;
		for  ($i = 0; $i< count($arr)-1; $i++) {
			swap_click($arr, $i, $click_counter);
			$click_counter = $arr[$i]['click_counter'];
		}
	}

	public function swap_click (&$arr, $start, $click_counter) {
		$pos  = $start;
		$maxclick = $arr[$pos]['click_counter'];
		for  ($i = $start; $i< count($arr); $i++) {
			if ($arr[$i]['click_counter'] == $domain) {
				$pos = $i;
				$maxclick = $arr[$i]['click_counter'];
				break;
			}
			if ($arr[$i]['click_counter'] > $maxclick) {
				$pos = $i;
				$maxclick = $arr[$i]['click_counter'];
			}
		}
		$temp = $arr[$start];
		$arr[$start] = $arr[$pos];
		$arr[$pos] = $temp;
	}

	public function cmp_weight($a, $b) {
		if ($a['weight'] == $b['weight'])
			return 0;
		return ($a['weight'] > $b['weight']) ? -1 : 1;
	}

	public function cmp_dom_dot($a, $b) {
		$dots_a = substr_count($a['domain'], ".");
		$dots_b = substr_count($b['domain'], ".");

		if ($dots_a == $dots_b)
			return 0;

		return ($dots_a < $dots_b) ? -1 : 1;
	}

	public function cmp_path_dot($a, $b) {
		$path_a = preg_replace('/([^/]+)$/i', "", $a['path']);	//	  get path without filename
		$path_b = preg_replace('/([^/]+)$/i', "", $b['path']);	//	  get path without filename

		$dots_a = substr_count($path_a, ".");
		$dots_b = substr_count($path_b, ".");

		if ($dots_a == $dots_b)
			return 0;

		return ($dots_a < $dots_b) ? -1 : 1;
	}

	public function cmp_path_slash($a, $b) {
		$path_a = preg_replace('/([^/]+)$/i', "", $a['path']);	//	  get path without filename
		$path_b = preg_replace('/([^/]+)$/i', "", $b['path']);	//	  get path without filename

		$slash_a = substr_count($a['path'], "/");
		$slash_b = substr_count($b['path'], "/");

		if ($slash_a == $slash_b)
			return 0;

		return ($slash_a < $slash_b) ? -1 : 1;
	}

	public function addmarks($a) {
		$a = preg_replace("/[ ]+/i", " ", $a);
		$a = str_replace(" +", "+", $a);
		$a = str_replace(" ", "+", $a);
		return $a;
	}

	public function makeboollist($a, $type) {
		//global $this -> _s -> stem_words, $this -> _s -> case_sensitive, $del_secchars, $cn_seg;
		$entities = Common::$entities;
		while ($char = each($entities)) {
			$a = preg_replace("/$char[0]/i", $char[1], $a);
		}

		if ($type != "phrase") {	//  delete secondary characters from query
			$search = "1";
			$a	  = Common::del_secchars($a, $search);
		}

		$a = trim($a);
		$a = preg_replace("/&quot;/i", "\"", $a);
		$returnWords = array();

		//get all phrases
		$regs = Array();
		while (preg_match("/([-]?)\"([^\"]+)\"/i", $a, $regs)) {
			if ($regs[1] == '') {
				$returnWords['+s'][] = $regs[2];
				$returnWords['hilight'][] = $regs[2];
			} else {
				$returnWords['-s'][] = $regs[2];
			}
			$a = str_replace($regs[0], "", $a);
		}

		if ($this -> _s -> case_sensitive == 1) {
			$a = preg_replace("/[ ]+/i", " ", $a);
		} else {
			$a = preg_replace("/[ ]+/", " ", $a);
		}

		//  $a = remove_accents($a);
		$a = trim($a);
		$words = explode(' ', $a);
		if ($a=="") {
			$limit = 0;
		} else {
		$limit = count($words);
		}

		$k = 0;
		//get all words (both include and exlude)
		$includeWords = array();
		while ($k < $limit) {
			if (substr($words[$k], 0, 1) == '+') {
				$includeWords[] = substr($words[$k], 1);
				if (!$this -> ignoreWord(substr($words[$k], 1))) {
					$returnWords['hilight'][] = substr($words[$k], 1);
					if ($this -> _s -> stem_words != 'none') {
						$returnWords['hilight'][] = Common::stem_word(substr($words[$k], 1), $type);
					}
				}
			} else if (substr($words[$k], 0, 1) == '-') {
				$returnWords['-'][] = substr($words[$k], 1);
			} else {
				$includeWords[] = $words[$k];
				if (!$this -> ignoreWord($words[$k])) {
					$returnWords['hilight'][] = $words[$k];
					if ($this -> _s -> stem_words != 'none') {
						$returnWords['hilight'][] = Common::stem_word($words[$k], $type);
					}
				}
			}
			$k++;
		}

		//add words from phrases to includes
		if (isset($returnWords['+s'])) {
			foreach ($returnWords['+s'] as $phrase) {
				if ($this -> _s -> case_sensitive == '0') {
					$phrase = Common::lower_case($phrase);
					$phrase = Common::lower_case(preg_replace("/[ ]+/i", " ", $phrase));
				} else {
					$phrase = preg_replace("/[ ]+/i", " ", $phrase);
				}

				$phrase = trim($phrase);
				$temparr = explode(' ', $phrase);
				foreach ($temparr as $w)
					$includeWords[] = $w;
			}
		}

		foreach ($includeWords as $word) {
			if (!($word =='')) {
				if ($this -> ignoreWord($word)) {

					$returnWords['ignore'][] = $word;
				} else {
					$returnWords['+'][] = $word;
				}
			}

		}
		return $returnWords;
	}

	/**
	 * Checks if the word should be ignored
	 * @param string $word
	 * @return boolean
	 */
	public function ignoreWord($word) {
		$min_word_length = Settings::getInstance() -> min_word_length;

		if (Settings::getInstance() -> index_numbers == 1) {
			$pattern = "[a-z0-9]+";
		} else {
			$pattern = "[a-z]+";
		}
		if (strlen($word) < Settings::getInstance() -> min_word_length || (array_key_exists($word, Settings::getInstance() -> common) && Settings::getInstance() -> common[$word] == 1)) {
			return 1;
		} else {
			return 0;
		}
	}

	public function links_only($searchstr, $type, $possible_to_find, $db_slv) {
		$sph_messages = Model::getInstance() -> L10N;
		//global $type, $this -> _s -> mark, $this -> _s -> case_sensitive;

		$url		= '';
		$fulltxt	= '';
		$res		= array();

		$this -> parameters -> wildcount = substr_count($searchstr['+']['0'], '*');
		if ($this -> parameters -> wildcount) {	   //  ****		for * wildcard , enter here
			$searchstr['+']['0'] = str_replace('*','%', $searchstr['+']['0']);
		}

		if ($type == "tol") {
			$searchstr['+']['0'] = make_tolerant($searchstr['+']['0']);
		}

		$i = 1;

		if ($type == "or") {
			foreach ($searchstr['+'] as $query) {
				if ($this -> _s -> stem_words != 'none') {
					$query = Common::stem_word($query, $type);
				}
				$query1 = Connection::getInstance() -> escape_string($query);

				//  build up the MySQL query for OR search
				if ($i != '1' ) {
					if ($this -> _s -> case_sensitive == '1') {
						$or_query .= " or title like '%$query1%' ";
					}else {
						$or_query .= " or CONVERT((title)USING utf8) like '%$query1%' ";
					}
				} else {
					if ($this -> _s -> case_sensitive == '1') {
						$or_query .= " title like '%$query1%' ";
					} else {
						$or_query .= " CONVERT((title)USING utf8) like '%$query1%' ";
					}
				}
				$i++;
			}

			$result = mysql_query("SELECT link_id, url, title from ".$this -> _s -> mysql_table_prefix."link_details where ".$or_query."");
			if ($this -> _s -> debug > '0') echo mysql_error();
			$num_rows = mysql_num_rows($result);
//echo "\r\n\r\n<br /> OR num_rows: $num_rows<br />\r\n";
			if ($num_rows == 0) {
				$possible_to_find = '0';
			}
		}

		if ($type == 'and' ) {
			foreach ($searchstr['+'] as $query) {
				$query1 = Connection::getInstance() -> escape_string($query);
				//  build up the MySQL query for AND search
				if ($i != '1' ) {
					if ($this -> _s -> case_sensitive == '1') {
						$and_query .= " and title like '%$query1%' ";
					} else {
						$and_query .= " and CONVERT((title)USING utf8) like '%$query1%' ";
					}
				} else {
					if ($this -> _s -> case_sensitive == '1') {
						$and_query .= " title like '%$query1%' ";
					} else {
						$and_query .= " CONVERT((title)USING utf8) like '%$query1%' ";
					}
				}
				$i++;
			}

			$result = mysql_query("SELECT link_id, url, title, domain from ".$this -> _s -> mysql_table_prefix."link_details where ".$and_query."");
			if ($this -> _s -> debug > '0') echo mysql_error();
			$num_rows = mysql_num_rows($result);
//echo "\r\n\r\n<br /> AND num_rows: $num_rows<br />\r\n";
			if ($num_rows == 0) {
				$possible_to_find = '0';
			}
		}

		if ($type == "phrase" || $type == "tol") {
			foreach ($searchstr['+'] as $query) {
				$phrase .= $query." ";
				if ($type == "tol") {
					$phrase = make_tolerant($phrase);
				}
			}

			$phrase1 = trim(Connection::getInstance() -> escape_string($phrase));
			$result = mysql_query("SELECT link_id, url, title from ".$this -> _s -> mysql_table_prefix."link_details where CONVERT((title)USING utf8) like '%$phrase1%'");
			if ($this -> _s -> debug > '0') echo mysql_error();
			$num_rows = mysql_num_rows($result);
//echo "\r\n\r\n<br /> Phrase-Tol num_rows: $num_rows<br />\r\n";
			if ($num_rows == 0) {
				$possible_to_find = '0';
			}
		}

		if ($possible_to_find == '1') {

			if ($this -> _s -> mark == 'markbold') {
				$highlight = "span class=\"mak_1\"";
			}
			if ($this -> _s -> mark == 'markblue') {
				$highlight = "span class=\"mak_2\"";
			}
			if ($this -> _s -> mark == 'markyellow') {
				$highlight = "span class=\"mak_3\"";
			}
			if ($this -> _s -> mark == 'markgreen') {
				$highlight = "span class=\"mak_4\"";
			}
			if ($this -> _s -> mark == 'markred') {
				$highlight = "span class=\"mak_5\"";
			}
			$i = 0;
			for ($i = 0; $i < $this -> _s -> max_results && $row = mysql_fetch_array($result, MYSQL_NUM); $i++) {


				$page_res = mysql_query("select * from ".$this -> _s -> mysql_table_prefix."links where link_id like '$row[0]'");
				if ($this -> _s -> debug > '0') echo mysql_error();
				$page_row = mysql_fetch_array($page_res);
				$page_title = ($page_row[3]);   //	get title of page that contains this new result (link)

				$title = " ".$row[2]." ";	   //  free the title of this new result (link)

				foreach($searchstr['hilight'] as $change) {
					$title  = $this -> highlight($title, $change, $highlight);   //  mark all searchwords found in this link text
				}
/*
 * @todo fix
				//	  prepare this link-url for our click counter
				$url_crypt  = str_replace("&", "-_-", $row[1]);	//  crypt the & character
				$url_click  = "$include_dir/click_counter.php?url=$url_crypt&amp;query=$query&amp;db=$db_slv&amp;prefix={$this -> _s -> mysql_table_prefix}";   //  redirect users click in order to update Most Popular Links

				$fulltxt  = "<br />Link: <a href=\"".$url_click."\" target =top>".$title."</a><br /><br />";
*/
				//  now build up the result array
				$res[$i]['title']		   = $page_row[3];
				$res[$i]['url']			 = $page_row[2];
				$res[$i]['fulltxt']		 = $fulltxt;
				$res[$i]['size']			= $page_row[7];
				$res[$i]['click_counter']   = $page_row[11];
				$res[$i]['weight']		  = "100";
				$res[$i]['domain']		  = $row[3];
				$urlparts = parse_url($res[$i]['url']);
				//$res[$i]['path'] = $urlparts['path'];	//	  get full path
				$res[$i]['path']			= preg_replace('/([^\/]+)$/i', "", $urlparts['path']);	//	  get path without filename
				$res[$i]['maxweight']	   = "100";
				$res[$i]['results']		 = $num_rows;
				$res[$i]['db']			  = $db_slv;	  //  all these results are from db (the currently active db)


			}

			if ($this -> _s -> clear == 1) {
				mysql_free_result($page_res);
				mysql_free_result($result);
				unset ($fulltxt, $title);
			}
//echo "\r\n\r\n<br>res Array:<br><pre>";print_r($res);echo "</pre>";
			return $res;

		} else {	//  if nothing found, try 'Did you mean'
			if ($possible_to_find == 0 && $this -> _s -> did_you_mean_enabled == 1) {
				reset ($searchstr['+']);
				foreach ($searchstr['+'] as $word) {
					$word2 = str_ireplace("Ãƒ", "Ã ", addslashes("$word"));
					$result = mysql_query("select keyword from ".$this -> _s -> mysql_table_prefix."keywords where soundex(keyword) = soundex('$word2%')");
					$max_distance = 100;
					$near_word ="";
					while ($row=mysql_fetch_row($result)) {
						$distance = levenshtein($row[0], $word);
						if ($distance < $max_distance && $distance <10) {
							$max_distance = $distance;
							$near_word = ($row[0]);
						}
					}
					if ($this -> _s -> clear == 1) mysql_free_result($result);

					if ($near_word != "" && $word != $near_word) {
						$near_words[$word] = $near_word;
					}
				}

				if ($this -> parameters -> wildcount == '0' && $near_words != "") {   //   No 'Did you mean' for wildcount search
					$res['did_you_mean'] = $near_words;
					return $res;
				}
			}
		}
	}

	public function make_tolerant($searchword) {

		$acct_a = array("a;", "a", "Ãƒ ", "ÃƒÂ¢", "Ã¥", "Ã¢", "ÃƒÆ’Ã‚Â¤", "ÃƒÂ¤", "ÃƒÆ’\"Å¾", "Ãƒâ€ž", "Ã„", "Ã¤", "ÃƒÂ¡", "Ã ",
					"&agrave;", "Ã¡", "&aacute;", "Ã€", "&Agrave;", "Ã", "&Aacute;");
		$base_a = array("a", "a", "a", "a", "a", "a", "a", "a", "A", "A", "A", "a", "a", "a",
					"a", "a", "a", "A", "A", "A", "A");
		$searchword = str_ireplace($acct_a, $base_a, $searchword);

		$acct_c = array("c", "Ã§", "ÃƒÂ§", "&ccedil;", "&Ccedil;", "C");
		$base_c = array("c", "c", "c", "c", "C", "C");
		$searchword = str_ireplace($acct_c, $base_c, $searchword);

		$acct_e = array("e", "ÃƒÂª", "ÃƒÂ¨", "Ãª", "ÃƒÂ©", "Ã¨", "&egrave;", "Ã©", "&eacute;", "Ãˆ", "&Egrave;", "Ã‰", "&Eacute;", "ÃƒË†", "Ãƒâ€°", "E");
		$base_e = array("e", "e", "e", "e", "e", "e", "e", "e", "e", "E", "E", "E", "E", "E", "E", "E");
		$searchword = str_ireplace($acct_e, $base_e, $searchword);

		$acct_i = array("ÃƒÂ®", "Ã®", "Ã¬", "&igrave;", "Ã­", "&iacute;", "ÃŒ", "&Igrave;", "Ã", "&Iacute;",
					"ÃƒÂ±", "Ã‚Â¡", "Ãƒ'", "Ã‚Â¿" );
		$base_i = array("i", "i", "i", "i", "i", "i", "I", "I", "I", "I",
					"Ã±", "Â¡", "Ã‘", "Â¿");
		$searchword = str_ireplace($acct_i, $base_i, $searchword);

		$acct_o = array("ÃƒÂ´", "Ã¸", "Ã˜", "Ã´", "Ã³", "Ã²", "Ãµ", "Ãƒâ€“", "ÃƒÆ’Ã‚Â¶", "ÃƒÂ¶", "Ã£Â¶",
					"ÃƒÂ³", "Ã²","&ograve;", "Ã³", "&oacute;", "Ã’", "&Ograve;", "Ã“", "&Oacute;");
		$base_o = array("o", "o", "O", "o", "o", "o", "o", "O", "o", "o", "o",
					"Ã–", "Ã¶", "O", "o", "o", "O", "O", "O", "O");
		$searchword = str_ireplace($acct_o, $base_o, $searchword);

		$acct_u = array("Ã‚Å“", "Å“", "ÃƒÂ»", "Ã¹", "Ãº", "Ã»", "ÃƒÆ’Ã‚Â¼", "ÃƒÂ¼", "ÃƒÆ’Ã…\â€œ", "ÃƒÅ“", "Ãœ", "Ã¼", "ÃƒÂº",
					"Ã¹", "&ugrave;", "Ãº", "&uacute;", "Ã™", "&Ugrave;", "Ãš", "&Uacute;");
		$base_u = array("u", "u", "u", "u", "u", "u", "u", "u", "U", "U", "U", "u", "u",
					"u", "u", "u", "u", "U", "U", "U", "U");
		$searchword = str_ireplace($acct_u, $base_u, $searchword);

		$get = array("a", "c", "e", "i", "o", "u");
		$out = array("%", "%", "%", "%", "%", "%");
		$searchword = str_ireplace($get, $out, $searchword);

		return $searchword;
	}



	public function search($searchstr, $category, $start, $per_page, $type, $domain, $prefix) {
//		global $this -> _s -> length_of_link_desc,  $this -> _s -> show_meta_description, $this -> _s -> sort_results, $this -> _allwild;
//		global $this -> _s -> stem_words, $this -> _s -> did_you_mean_enabled, $this -> _s -> relevance, $this -> parameters -> query, $this -> _s -> clear, $this -> _s -> greek, $this -> _s -> translit_el, $this -> _s -> noacc_el;
//		global $this -> parameters -> wildcount, $type, $this -> _s -> case_sensitive, $this -> _s -> debug, $this -> _s -> use_cache, $this -> _s -> max_ctresults, $this -> _s -> dom_count, $this -> _s -> out;
//		global $dbu_act, $db1_slv, $db2_slv, $db3_slv, $db4_slv, $db5_slv;
//		global $database1, $database2, $database3, $database4, $database5;
//		global $mysql_table_prefix1, $mysql_table_prefix2, $mysql_table_prefix3, $mysql_table_prefix4, $mysql_table_prefix5;

		//  collect results from all involved databases
		$res = array();

		//  search for query input
		$res = $this -> search_dbs($searchstr, $category, $start, $per_page, $type, $domain, $prefix, $res);
		$res1 = $res;

		if ($this -> _s -> translit_el == '1' || $this -> _s -> noacc_el == '1' ) {

			$rem_type   = $type ;   //  remember the original search mode
			$type	   = "tol";	//  temporary required for Greek vowels

			if ($this -> _s -> translit_el == '1') {
				if ($searchstr['+s'][0]) {
					$searchstr['+s'][0] = translit_el($searchstr['+s'][0]);			 //  translitate the search phrase
				}

				foreach ($searchstr['hilight'] as $key => $v) {
					$searchstr['hilight'][] = translit_el($searchstr['hilight'][$key]); //  add the translited words for highlighting
				}

				foreach ($searchstr['+'] as $key => $v) {
					$searchstr['+'][$key] = translit_el($searchstr['+'][$key]);		 //   translitate the search string
				}
			}

			if ($this -> _s -> noacc_el == '1') {
				if ($searchstr['+s'][0]) {
					$searchstr['+s'][0] = remove_acc_el($searchstr['+s'][0]);			 //  remove accents from Greek vowels in search phrase
				}

				foreach ($searchstr['hilight'] as $key => $v) {
					$searchstr['hilight'][] = remove_acc_el($searchstr['hilight'][$key]); // remove accents from Greek vowels  for highlighting
				}

				foreach ($searchstr['+'] as $key => $v) {
					$searchstr['+'][$key] = remove_acc_el($searchstr['+'][$key]);		 // remove accents from Greek vowels in search string
				}
			}

			//  now search for transliterated query input
			$res2 = $this -> search_dbs($searchstr, $category, $start, $per_page, $type, $domain, $prefix, $res);

			$all_wild2 = explode(" ", $this -> _allwild);
			//  find all 'nearby' words to be highlighted
			foreach ($all_wild2 as $value) {
				if (preg_match("/a|ÃŸ|d|e|?|?|?|?|Âµ|?|?|p|?|s|t|?|?/i", $value) || preg_match("/?|?|?|?|G|?|?|?|?|?|?|?|?|S|?|?|?/i", $value)) {
					$all_wild2n .= " ".$value."";		   //  collect only Greek words
				}
			}
			$this -> _allwild = "".$all_wild2n." ".$searchstr['hilight'][0]."";	  //  add original query and build $this -> _allwild
//echo "\r\n\r\n<br /> All matched keywords: $this -> _allwild<br />\r\n";
			//  if valid results (not only 'Did you mean'),  AND $res1 did not deliver 'Did you mean', add Greek results to $res1
			if ($res2[0]['url'] && !$res1['did_you_mean']) {
				foreach($res1 as $a1) {
					//  eliminate dublicate results  of $res1 and $res2
					$i = '0';
					foreach($res2 as $a2) {
						if ($a1['url'] == $a2['url']){
							$res2[$i] = '';
						}
						$i++;
					}
				}

				//  add only arrays of $res2
				$i = '0';
				foreach ($res2 as $res2a){
					if (is_array($res2a)) {
						$res[] =  $res2[$i];
					}
					$i++;
				}
			} else {	//  if $res1 only delivered 'Did you mean'
				if ($res2[0]['url']) {  //  if valid results in $res2
					$res = $res2;
				}

			}
			$type = $rem_type ;//  replace the original search mode
		}

		if (array_key_exists('did_you_mean', $res1) && $this -> _s -> noacc_el != '1'){	 //  for translit to Greeek use result array 1
			return $res1;
		}

		if (isset($res2) && array_key_exists('did_you_mean', $res2) && $this -> _s -> noacc_el == '1' && !$res1[0]['url']){	 //  for no Greek accents use result array 2
			return $res2;
		}

		if (count($res) == 0) {
			return null;
		}

		$all = count($res);
		if ($domain) {
			$this -> _s -> sort_results = '1';			// overwrite Admin settings, as for search in one domain, we need all results in relevance order
		}
		if ($this -> _s -> sort_results != '3') {
			usort($res, array($this, "cmp_weight"));	  //  standard output sorted by relevance (weight)
		}

		//if (($this -> _s -> sort_results == '4'  && $domain_qry == "" ) || $this -> _s -> sort_results == '3') {	//  output alla Google  OR  by domain name
		if ($this -> _s -> sort_results == '4'  && $domain_qry == "" ) {	//  output alla Google
			sort_with_domains($res);
		} else {
			if ($this -> _s -> sort_results == '2') {			 //	  enter here if 'Main URLs' on top of listing
				usort($res, array($this,"cmp_dom_dot"));		 //	  sort domains without dots on top
				usort($res, array($this,"cmp_path_slash"));	  //	  sort minimal slashes on top
			}

			if ($this -> _s -> sort_results == '5') {			 //	  enter here if 'Most Popular Click' on top of listing
				$this -> sort_by_bestclick($res);
			}
		}

		//  limit number of results per domain if Admin defined
		if ($this -> _s -> dom_count && $this -> _s -> sort_results == '3'){
			$i = '0';
			$known_domain = $res[0]['domain'];	  //  first known domain
			foreach($res as &$v) {
				$domain = $v['domain'];			 //  fetch actual domain from result array
				if ($known_domain == $domain && $i < $this -> _s -> dom_count) {
					 $dom_res[] = $v;			   //  build new result array
					 $i++;
				} else {	//  no more results from known domain or counter maximum reached
					if ($known_domain != $domain) { // fetched another domain in result array
						$known_domain = $domain;
						$dom_res[] = $v;			//  add first result of new domain
						$i = '1';
					}
				}
			}
			$res = $dom_res;
		}

		$results = count ($res);  //  total amount of results
		//  limit result count to limit of text-cache
		if ($this -> _s -> use_cache == '1') {
			if($results > $this -> _s -> max_ctresults) {
				$results = $this -> _s -> max_ctresults;
				$res = array_slice($res, 0, $this -> _s -> max_ctresults);
			}
		}

/*
*   in case that full (all) text results should be stored in XML output file,
*   uncomment next 3 rows and comment the row
*   convert_xml($xml_result, 'text');
*   in function 'get_text_results'

		if ($this -> _s -> out == 'xml') {
			text_xml($res, count($res), $searchstr);
		}
*/
		//  reduce results for one page in result listing
		$offset = ($start-1)*$per_page;
		$res = array_slice($res, $offset, $per_page);

		$res['maxweight'] = $res[0]['maxweight'];
		$res['results'] = $results;
		$res['hilight'] = $searchstr['hilight'];
//echo "<br>res Array complete:<br><pre>";print_r($res);echo "</pre>";
		return $res;
	}

	public function search_dbs($searchstr, $category, $start, $per_page, $type, $domain, $prefix, $res) {
		$cf = Configuration::getInstance();

		//global $this -> _s -> stem_words, $this -> _s -> did_you_mean_enabled, $this -> _s -> relevance, $this -> parameters -> query, $this -> _s -> clear, $this -> _s -> max_results;
		//global $this -> parameters -> wildcount, $type, $this -> _s -> case_sensitive, $this -> _s -> debug, $this -> _s -> debug_user, $this -> _s -> use_cache, $this -> _s -> max_ctresults;
//		global $dbu_act, $user_db, $cf -> db1_slv, $db2_slv, $db3_slv, $db4_slv, $db5_slv;
//		global $database1, $database2, $database3, $database4, $database5;
//		global $mysql_table_prefix1, $mysql_table_prefix2, $mysql_table_prefix3, $mysql_table_prefix4, $mysql_table_prefix5;

		for($j = 1; $j < 5; $j++) {
			if ($cf -> {'db' . $j . '_slv'} == 1 && !$this -> _s -> user_db || $this -> _s -> user_db == $j) {	//  as defined in Admin's Database Management settings or by user overwritten
				Connection::getInstance() -> reconnect($cf -> {"mysql_host$j"}, $cf -> {"mysql_user$j"}, $cf -> {"mysql_password$j"}, $cf -> {"database$j"});
				//$db_con	 = db1_connect() ;
				$valid = '1';

				if ($prefix > '0' ) {	   //	  if requested by the Search Form, we need to use the shifted table-suffix
					$valid = '';
					$result = mysql_query("SHOW TABLES");
					$num_rows = mysql_num_rows($result);
					for ($i = 0; $i < $num_rows && $valid == ''; $i++) {		//  the shifted table-suffix is part of this database?
						$valid = strstr(mysql_tablename($result, $i), $prefix); //  will create a non-zero value for $valid
					}
					if ($this -> _s -> clear == 1) mysql_free_result($result);
					if ($valid) {
						$this -> _s -> mysql_table_prefix = $prefix;	  //  replace the tablesuffix
					} else {
						if ($this -> _s -> debug_user == '1') {
							echo "Table prefix '$prefix' does not exist in database $j ";
							die();
						}
					}
				} else {
					$this -> _s -> mysql_table_prefix = $cf -> {"mysql_table_prefix$j"}; //  use default suffix for this db
				}

				if ($valid) { //   for standard table-suffix, or if shifted suffix is valid for this db
					$db_slv = $j;   // get results from this db
					$res = $this -> slave_search ($searchstr, $category, $domain, $this -> _s -> mysql_table_prefix, $start, $per_page, $db_slv, $type);
				}
			}
		}
		$dbu_act = $cf -> dbu_act;
		// re-activate database of actual 'Search User'
		switch($dbu_act) {
			case '1': case '2': case '3': case '4': case '5':
				// Just to be sure it's a valid value
				Connection::getInstance() -> reconnect($cf -> {"mysql_host$dbu_act"}, $cf -> {"mysql_user$dbu_act"}, $cf -> {"mysql_password$dbu_act"}, $cf -> {"database$dbu_act"});

				if ($prefix > '0' ) {
					$this -> _s -> mysql_table_prefix = $prefix;
				} else {
					$this -> _s -> mysql_table_prefix = $cf -> {"mysql_table_prefix$j"};
				}
				break;

		}

		$res = array_slice($res, 0, $this -> _s -> max_results, TRUE);
		return $res;
	}

	public function slave_search($searchstr, $category, $domain, $mysql_table_prefix, $start, $per_page, $db_slv, $type) {
//		global $this -> _s -> length_of_link_desc, $this -> _s -> show_meta_description, $this -> _s -> sort_results, $this -> _s -> stem_words, $this -> _s -> did_you_mean_enabled, $this -> _s -> relevance;
//		global $this -> parameters -> wildcount, $this -> _s -> case_sensitive, $this -> _s -> debug, $this -> _s -> max_results, $this -> _s -> clear, $this -> _s -> only_links;

		$possible_to_find = 1;
		$result = mysql_query("select domain_id from ".$mysql_table_prefix."domains where domain = '$domain'");

		if (mysql_num_rows($result)> 0) {
			$thisrow = mysql_fetch_array($result);
			$domain_qry = "and domain = ".$thisrow[0];
		} else {
			$domain_qry = "";
		}

		if ($this -> _s -> clear == 1) mysql_free_result($result);
		$notlist = array();
		//find all sites that should not be included in the result
		if (!array_key_exists('+', $searchstr) || count($searchstr['+']) == 0) {
			return $notlist;
		}
		$wordarray = array_key_exists('-', $searchstr) ? $searchstr['-'] : array();
		$not_words = 0;

		while ($not_words < count($wordarray)) {
			if ($this -> _s -> stem_words != 'none') {
				$searchword = addslashes(Common::stem_word($wordarray[$not_words], $type));
			} else {
				$searchword = addslashes($wordarray[$not_words]);
			}

			$wordmd5 = substr(md5($searchword), 0, 1);
			$query1 = "SELECT link_id from ".$mysql_table_prefix."link_keyword$wordmd5, ".$mysql_table_prefix."keywords where ".$mysql_table_prefix."link_keyword$wordmd5.keyword_id= ".$mysql_table_prefix."keywords.keyword_id and keyword='$searchword'";
			$result = mysql_query($query1);

			while ($row = mysql_fetch_row($result)) {
				$notlist[$not_words]['id'][$row[0]] = 1;
			}
			$not_words++;
			if ($this -> _s -> clear == 1) mysql_free_result($result);
		}

		//find all sites containing the search phrase
		$wordarray = array_key_exists('+s', $searchstr) ? $searchstr['+s'] : array();
		$phrase_words = 0;
		while ($phrase_words < count($wordarray)) {
			$searchword = addslashes($wordarray[$phrase_words]);
			$phrase_query = $searchword;

			//  search for phrase in fulltext
			if ($this -> _s -> case_sensitive =='1') {
				$query1 = "SELECT link_id from ".$this -> _s -> mysql_table_prefix."links where fulltxt like '%$searchword%'";
			}

			if ($this -> _s -> case_sensitive =='0') {
				$searchword = Common::lower_case($searchword);
				$query1 = "SELECT link_id from ".$this -> _s -> mysql_table_prefix."links where CONVERT(LOWER(fulltxt)USING utf8)  like '%$searchword%'";
			}

			$result = mysql_query($query1);
			if ($this -> _s -> debug > '0') echo mysql_error();
			$num_rows = mysql_num_rows($result);

			if ($num_rows == 0 && !$this -> _s -> only_links) {
				//	  phrase not found in fulltext. Now try to find in title tag. But not for 'only link search'
				if ($this -> _s -> case_sensitive =='1') {
					$query1 = "SELECT link_id from ".$this -> _s -> mysql_table_prefix."links where title like '%$searchword%'";
				}

				if ($this -> _s -> case_sensitive =='0') {
					$searchword = Common::lower_case($searchword);
					$query1 = "SELECT link_id from ".$this -> _s -> mysql_table_prefix."links where CONVERT(LOWER(title)USING utf8) like '%$searchword%'";
				}

				$result = mysql_query($query1);
				if ($this -> _s -> debug > '0') echo mysql_error();
					$num_rows = mysql_num_rows($result);

				if ($num_rows == 0) {
	 				$possible_to_find = 0;
					break;
				}
			}

			while ($row = mysql_fetch_row($result)) {
				$value =$row[0];
				$phraselist[$phrase_words]['id'][$row[0]] = 1;
				$phraselist[$phrase_words]['val'][$row[0]] = $value;
			}
			$phrase_words++;
		}

		if (($category> 0) && $possible_to_find==1) {
			$allcats = get_cats($category);
			$catlist = implode(",", $allcats);
			$query1 = "select link_id from ".$this -> _s -> mysql_table_prefix."links, ".$this -> _s -> mysql_table_prefix."sites, ".$this -> _s -> mysql_table_prefix."categories, ".$this -> _s -> mysql_table_prefix."site_category where ".$this -> _s -> mysql_table_prefix."links.site_id = ".$this -> _s -> mysql_table_prefix."sites.site_id and ".$this -> _s -> mysql_table_prefix."sites.site_id = ".$this -> _s -> mysql_table_prefix."site_category.site_id and ".$this -> _s -> mysql_table_prefix."site_category.category_id in ($catlist)";
			$result = mysql_query($query1);
			if ($this -> _s -> debug > '0') echo mysql_error();
			$num_rows = mysql_num_rows($result);
			if ($num_rows == 0) {
				$possible_to_find = 0;
			}
			while ($row = mysql_fetch_row($result)) {
				$category_list[$row[0]] = 1;
			}
		}
		if ($this -> _s -> clear == 1) mysql_free_result($result);

		//  if selected, search only links as full text and present them
		if ($this -> _s -> only_links) {
			$res = links_only($searchstr, $type, $possible_to_find, $db_slv);
			return $res;

		}

		//find all sites that include the search word
		$wordarray = $searchstr['+'];
		$words = 0;
		$searchword = addslashes($wordarray[$words]);   //  get only first word of search query
		$strictpos = strpos($searchword, '!'); //   if  ! is in position 0, we have to search strict

		if ($strictpos === 0) {   //	****		for 'Strict search' enter here
			$searchword = str_replace('!', '', $searchword);	//  remove the strict directive from query

			$query = "SELECT keyword_id, keyword from ".$this -> _s -> mysql_table_prefix."keywords where keyword = '$searchword'";
			if ($this -> _s -> debug > '0') echo mysql_error();
			$result = mysql_query($query);
			$num_rows = mysql_num_rows($result);

			if ($num_rows == 0) {   // if there was no searchword in table keywords
				$possible_to_find = 0;
				$break = 1;
			}
			if ($num_rows !=0) {
				// get all searchwords as keywords from table keywords
				$keyword_id = mysql_result($result, $i, "keyword_id");
				$keyword = mysql_result($result, $i, "keyword");
				$keyword = str_replace("'", "\\'", $keyword);	   //  replace backslash as during index created for MySQL database required
				$wordmd5 = substr(md5($keyword), 0, 1);			 // calculate attribute for link_keyword table
				if ($this -> _s -> clear == 1) mysql_free_result($result);

				if ($this -> _s -> sort_results == '7') {   //	  get query hit results
					$query1 = "SELECT distinct link_id, hits, domain, indexdate from ".$this -> _s -> mysql_table_prefix."link_keyword$wordmd5, ".$this -> _s -> mysql_table_prefix."keywords where ".$this -> _s -> mysql_table_prefix."link_keyword$wordmd5.keyword_id= ".$this -> _s -> mysql_table_prefix."keywords.keyword_id and keyword='$searchword' $domain_qry order by hits desc";
				} else {					// get weight results
					$query1 = "SELECT link_id, weight, domain, indexdate from ".$this -> _s -> mysql_table_prefix."link_keyword$wordmd5  where keyword_id = '$keyword_id' order by weight desc";
				}

				if ($this -> _s -> debug > '0') echo mysql_error();
				$reso = mysql_query($query1);
				$lines = mysql_num_rows($reso);

				if ($lines != 0) {
					$indx =$words;
				}
				while ($row = mysql_fetch_row($reso)) {
					$linklist[$indx]['id'][] = $row[0];
					$domains[$row[0]] = $row[2];


					if ($this -> _s -> sort_results == '6') {
						$linklist[$indx]['weight'][$row[0]] = $row[3];	  //  use indexdate
					} else {
						$linklist[$indx]['weight'][$row[0]] = $row[1];	  //  use weight
					}

					if ($this -> _s -> sort_results == '7') {   //	  ensure that result is also available in full text
						$txt_res = mysql_query("SELECT fulltxt FROM ".$this -> _s -> mysql_table_prefix."links where link_id = '$row[0]'");
						if ($this -> _s -> debug > '0') echo mysql_error();
						$full_txt = mysql_result($txt_res, 0);		  //	   get fulltxt  of this link ID
						if ($this -> _s -> case_sensitive == '0') {
							$full_txt= Common::lower_ent($full_txt);
							$full_txt = Common::lower_case($full_txt);
						}

						$foundit = strpos($full_txt, $searchword);  //	  get first hit
						if ($foundit) {
							$page_hits = $linklist[$indx]['weight'][$row[0]] ;
							$i = '0';

							while ($i < $page_hits) {	   //	  find out if all results in full text are really strict
								$found_in = strpos($full_txt, $searchword);
								$tmp_front = substr($full_txt, $found_in-1, 20); //  one character before found match position
								$pos = $found_in+strlen($searchword);
								$tmp_behind = substr($full_txt, $pos, 20); //  one character behind found match position
								$full_txt = substr($full_txt, $pos);  //  get rest of fulltxt
								//  check whether found match is realy strict
								$found_before = preg_match("/[(a-z)-_*.\/\:&@\w]/", substr($tmp_front, 0, 1));
								$found_behind = preg_match("/[(a-z)-_*.,\/\:&@\w]/", substr($tmp_behind, 0, 1));

								if ($found_before == 1 || $found_behind == 1) {		  //	  correct count of hits
									$linklist[$indx]['weight'][$row[0]] = $linklist[$indx]['weight'][$row[0]] - 1;
								}
								$i++;
							}
						} else {
							$linklist[$indx]['weight'][$row[0]] = '0';  //	  nothing found in full text. Hits = 0
						}
					}
				}
				$words++;
				if ($this -> _s -> clear == 1) mysql_free_result($reso);;
			}
		} else {	//****	   if not strict-search try here
			$wild_correct = 0;
			$this -> parameters -> wildcount = substr_count($searchword, '*');

			if ($this -> parameters -> wildcount) {	   //  ****		for * wildcard , enter here
				$searchword = str_replace('*','%', $searchword);
				$words = '0';

				$query = "SELECT keyword_id, keyword from ".$this -> _s -> mysql_table_prefix."keywords where keyword like '$searchword'";
				if ($this -> _s -> debug > '0') echo mysql_error();
				$result = mysql_query($query);
				$num_rows = mysql_num_rows($result);

				if ($num_rows == 0) {   // if there was no searchword in table keywords
					$possible_to_find = 0;
					$break = 1;
				}
				if ($num_rows !=0) {
					$this -> _allwild = '';
					for ($i=0; $i<$num_rows; $i++) {		// get all searchwords as keywords from table keywords
						$keyword_id = mysql_result($result, $i, "keyword_id");
						$keyword = mysql_result($result, $i, "keyword");

						$this -> _allwild =("{$this -> _allwild} $keyword");
						$wordmd5 = substr(md5(addslashes($keyword)), 0, 1);	 // calculate attribute for link_keyword table

						if ($this -> _s -> sort_results == '7') {   //	  get query hit results
							$query1 = "SELECT link_id, hits, domain, indexdate from ".$this -> _s -> mysql_table_prefix."link_keyword$wordmd5  where keyword_id = '$keyword_id' order by hits desc";
						} else {					// get weight results
							$query1 = "SELECT link_id, weight, domain, indexdate from ".$this -> _s -> mysql_table_prefix."link_keyword$wordmd5  where keyword_id = '$keyword_id' order by weight desc";
						}

						if ($this -> _s -> debug > '0') echo mysql_error();
						$reso = mysql_query($query1);
						$lines = mysql_num_rows($reso);

						if ($lines == 0) {
							if ($type != "or") {
								$possible_to_find = 0;
								break;
							}
						}
						if ($type == "or" && $this -> _s -> sort_results == '7') {
							$indx = 0;
						} else {
							$indx = $words;
						}

						while ($row = mysql_fetch_row($reso)) {
							$linklist[$indx]['id'][] = $row[0];
							$domains[$row[0]] = $row[2];

							if ($this -> _s -> sort_results == '6') {
								$linklist[$indx]['weight'][$row[0]] = $row[3];	  //  use indexdate
							} else {
								$linklist[$indx]['weight'][$row[0]] = $row[1];	  //  use weight
							}


							if ($this -> _s -> sort_results == '7') {   //	  ensure that result is also available in fulltxt
								$searchword =str_replace("%", '', $searchword);
								$txt_res = mysql_query("SELECT fulltxt FROM ".$this -> _s -> mysql_table_prefix."links where link_id = '$row[0]'");
								if ($this -> _s -> debug > '0') echo mysql_error();
								$full_txt = mysql_result($txt_res, 0);		  //	   get fulltxt  of this link ID
								if ($this -> _s -> case_sensitive == '0') {
									$full_txt= Common::lower_ent($full_txt);
									$full_txt = Common::lower_case($full_txt);
								}

								$pureword = str_replace('%','', $searchword);
								$foundit = substr_count($full_txt, $pureword);
								$linklist[$indx]['weight'][$row[0]] = $foundit;	 //  count of hits

								if (!$foundit) {
									$linklist[$indx]['weight'][$row[0]] = '0';  //	  nothing found in full text. Hits = 0
								}
							}
						}
					}
					$words++;
					if ($this -> _s -> clear == 1) mysql_free_result($reso);
				}
				if ($this -> _s -> clear == 1) mysql_free_result($result);

			} else {	//	  if no wildcard, try here
				if ($type == 'tol') {	   //  *****		 if tolerant search, enter here

					$searchword = make_tolerant($searchword);

					$query = "SELECT keyword_id, keyword from ".$this -> _s -> mysql_table_prefix."keywords where keyword like '$searchword'";
					if ($this -> _s -> debug > '0') echo mysql_error();
					$result = mysql_query($query);
					$num_rows = mysql_num_rows($result);

					if ($num_rows == 0) {   // if there was no searchword in table keywords
						$possible_to_find = 0;
						$break = 1;
					}
					if ($num_rows !=0) {
						$this -> _allwild = '';
						for ($i=0; $i<$num_rows; $i++) {		// get all searchwords as keywords from table keywords
							$keyword_id = mysql_result($result, $i, "keyword_id");
							$keyword = mysql_result($result, $i, "keyword");

							$accept = '1';
							//	  hopefully the PHP multibyte extention is available; otherwise use all results
							if (function_exists(mb_strlen)) {
								if (mb_strlen($keyword) != mb_strlen($searchword)){	 //  use only those results with same length as searchword
									$accept = '0';
								}
							}

							if ($accept == '1') {
								$this -> _allwild =("$this -> _allwild $keyword");
								$wordmd5 = substr(md5($keyword), 0, 1);	 // calculate attribute for link_keyword table

								if ($this -> _s -> sort_results == '7') {   //	  get query hit results
									$query1 = "SELECT link_id, hits, domain, indexdate from ".$this -> _s -> mysql_table_prefix."link_keyword$wordmd5 where keyword_id = '$keyword_id' order by hits desc";
								} else {					// get weight results
									$query1 = "SELECT link_id, weight, domain, indexdate from ".$this -> _s -> mysql_table_prefix."link_keyword$wordmd5 where keyword_id = '$keyword_id' order by weight desc";
								}

								if ($this -> _s -> debug > '0') echo mysql_error();
								$reso = mysql_query($query1);
								$lines = mysql_num_rows($reso);

								if ($lines != 0) {
									$indx =$words;
								}

								while ($row = mysql_fetch_row($reso)) {
									$linklist[$indx]['id'][] = $row[0];
									$domains[$row[0]] = $row[2];

									if ($this -> _s -> sort_results == '6') {
										$linklist[$indx]['weight'][$row[0]] = $row[3];	  //  use indexdate
									} else {
										$linklist[$indx]['weight'][$row[0]] = $row[1];	  //  use weight
									}

								}
								//$words++;
							}
						}
						$words++;

						if ($this -> _s -> clear == 1) mysql_free_result($reso);
					}
					if ($this -> _s -> clear == 1) mysql_free_result($result);
				} else {	//	  finally standard search
					$words = 0;
					while (($words < count($wordarray)) && $possible_to_find == 1) {
						if ($this -> _s -> stem_words != 'none') {
							$searchword = addslashes(Common::stem_word($wordarray[$words], $type));
						} else {
							$searchword = addslashes($wordarray[$words]);
						}

						$wordmd5 = substr(md5($searchword), 0, 1);

						if ($this -> _s -> sort_results == '7') {   //	  get query hit results
							$query1 = "SELECT distinct link_id, hits, domain, indexdate from ".$this -> _s -> mysql_table_prefix."link_keyword$wordmd5, ".$this -> _s -> mysql_table_prefix."keywords where ".$this -> _s -> mysql_table_prefix."link_keyword$wordmd5.keyword_id= ".$this -> _s -> mysql_table_prefix."keywords.keyword_id and keyword='$searchword' $domain_qry order by hits desc";
						} else {		// get weight results
							$query1 = "SELECT distinct link_id, weight, domain, indexdate from ".$this -> _s -> mysql_table_prefix."link_keyword$wordmd5, ".$this -> _s -> mysql_table_prefix."keywords where ".$this -> _s -> mysql_table_prefix."link_keyword$wordmd5.keyword_id= ".$this -> _s -> mysql_table_prefix."keywords.keyword_id and keyword='$searchword' $domain_qry order by weight desc";
						}
						if ($this -> _s -> debug > '0') echo mysql_error();
						$result = mysql_query($query1);
						$num_rows = mysql_num_rows($result);

						if ($num_rows == 0) {
							if ($type != "or") {
								$possible_to_find = 0;
								break;
							}
						}
						if ($type == "or" && $this -> _s -> sort_results == '7') {
							$indx = 0;
						} else {
							$indx = $words;
						}

						while ($row = mysql_fetch_row($result)) {
							$linklist[$indx]['id'][] = $row[0];
							$domains[$row[0]] = $row[2];

							if ($this -> _s -> sort_results == '6') {
								$linklist[$indx]['weight'][$row[0]] = $row[3];	  //  use indexdate
							} else {
								$linklist[$indx]['weight'][$row[0]] = $row[1];	  //  use weight
							}

							if ($this -> _s -> sort_results == '7') {   //	  ensure that result is also available in fulltxt
								if ($type == 'phrase') {
									$searchword = $phrase_query;
								}
								$linklist[$indx]['weight'][$row[0]] = '0';
								$txt_res = mysql_query("SELECT fulltxt FROM ".$this -> _s -> mysql_table_prefix."links where link_id = '$row[0]'");
								if ($this -> _s -> debug > '0') echo mysql_error();
								$full_txt = mysql_result($txt_res, 0);		  //	   get fulltxt  of this link ID
								if ($this -> _s -> case_sensitive == '0') {
									$full_txt = Common::lower_case($full_txt);
								}

								if (substr_count($full_txt, $searchword)) {	   //  found complete phrase in full text?
									$linklist[$indx]['weight'][$row[0]] = substr_count($full_txt, $searchword);  //	  number of hits found in this full text
								}
							}
						}
						$words++;
						if ($this -> _s -> clear == 1) mysql_free_result($result);
					}
				}

			}
		}	   //  ***** end  different search modes

		if ($type == "or") {
			$words = 1;
		}
//echo "<br>linklist Array:<br><pre>";print_r($linklist);echo "</pre>";
		$result_array_full = array();
		if ($words == 1 && $not_words == 0 && $category < 1) { // for OR-Sarch without query_hits and one word query, we already do have the result
			$result_array_full = $linklist[0]['weight'];
		} else {	//	 otherwise build an intersection of all the results
			$j= 1;
			$min = 0;
			while ($j < $words) {
				if (count($linklist[$min]['id']) > count($linklist[$j]['id'])) {
					$min = $j;
				}
				$j++;
			}

			$j = 0;
			$temp_array = isset($linklist) ? $linklist[$min]['id'] : array();
			$count = 0;
			while ($j < count($temp_array)) {
				$k = 0; //and word counter
				$n = 0; //not word counter
				$o = 0; //phrase word counter
				if ($this -> _s -> sort_results == '7') {
					$weight = 0;
				} else {
					$weight = 1;
				}

				$break = 0;
				if ($type =='phrase' && $this -> _s -> sort_results == '7') {	// for PHRASE search: find out how often the phrase was found in fulltxt (not for weighting %  scores)
					while ($k < $words && $break== 0) {
						if ($linklist[$k]['weight'][$temp_array[$j]] > 0) {
							$weight = $linklist[$k]['weight'][$temp_array[$j]];
						} else {
							$break = 1;
						}
						$k++;
					}

				} else {
					while ($k < $words && $break== 0) {
						if ($linklist[$k]['weight'][$temp_array[$j]] > 0) {

							if ($this -> _s -> sort_results == '6' || $this -> _s -> sort_results == '3') {
								$weight = $linklist[$k]['weight'][$temp_array[$j]];	 //  use indexdate
							} else {
								$weight = $weight + $linklist[$k]['weight'][$temp_array[$j]];   //  calculate weight
							}
						} else {
							$break = 1;
						}
						$k++;
					}

				}

				while ($n < $not_words && $break== 0) {
					if ($notlist[$n]['id'][$temp_array[$j]] > 0) {
						$break = 1;
					}
					$n++;
				}

				while ($o < $phrase_words && $break== 0) {
					if ($phraselist[$n]['id'][$temp_array[$j]] != 1) {
						$break = 1;
					}
					$o++;
				}
				if ($break== 0 && $category > 0 && $category_list[$temp_array[$j]] != 1) {
					$break = 1;
				}

				if ($break == 0) {
					$result_array_full[$temp_array[$j]] = $weight;
					$count ++;
				}
				$j++;
			}
		}
		if ($this -> _s -> clear == 1) {
			$temp_array = array();
			$linklist   = array();
		}
		//word == 1

		if ((count($result_array_full) == 0 || $possible_to_find == 0) && $this -> _s -> did_you_mean_enabled == 1) {
			reset ($searchstr['+']);
			$near_words = '';
			foreach ($searchstr['+'] as $word) {
				$word2 = str_ireplace("Ãƒ", "Ã ", addslashes("$word"));
 				$max_distance = 100;
				$near_word ="";

				//  first try to find any keywords using the soundex algorithm
				$result = mysql_query("select keyword from ".$this -> _s -> mysql_table_prefix."keywords where soundex(keyword) = soundex('$word2%')");

				if (!mysql_num_rows($result)) {
					//  if no match with first trial, try to find keywords with additional characters at the end
					$result = mysql_query("select keyword from ".$this -> _s -> mysql_table_prefix."keywords where keyword like '$word2%'");
				}

				while ($row=mysql_fetch_row($result)) {
					$distance = levenshtein($row[0], $word);
					if ($distance < $max_distance && $distance <10) {
						$max_distance = $distance;
						$near_word = ($row[0]);
					}
				}
				if ($this -> _s -> clear == 1) mysql_free_result($result);

				if ($near_word != "" && $word != $near_word) {
					$near_words[$word] = $near_word;
				}
			}

			if ($this -> parameters -> wildcount == '0' && $near_words != "") {   //   No 'Did you mean' for wildcount search
				$res['did_you_mean'] = $near_words;
				return $res;
			}
		}
		//  limit amount of results in result listing
		$result_array_full = array_slice($result_array_full, 0, $this -> _s -> max_results, TRUE);
		//return $result_array_full;

 		if (count($result_array_full) == 0) {
			$result_array_full = array();
			return $result_array_full;  //	  return blank array, otherwise array_merge() will not work in PHP5
		}

		if (array_key_exists('did_you_mean', $result_array_full)){
			return $result_array_full;
		}

		arsort ($result_array_full);
//echo "<br>result_array_full Array1:<br><pre>";print_r($result_array_full);echo "</pre>";
		if ($this -> _s -> sort_results == 4 && $domain_qry == "") {	// output alla Google)
			while (list($key, $value) = each($result_array_full)) {
				if (!isset($domains_to_show[$domains[$key]])) {
					$result_array_temp[$key] = $value;
					$domains_to_show[$domains[$key]] = 1;
				} else if ($domains_to_show[$domains[$key]] ==  1) {
					$domains_to_show[$domains[$key]] = Array ($key => $value);
				}
			}
		} else {
			$result_array_temp = $result_array_full;
		}
		if ($this -> _s -> clear == 1) $result_array_full = array();

		while (list($key, $value) = each ($result_array_temp)) {
			$result_array[$key] = $value;
			if (isset ($domains_to_show[$domains[$key]]) && $domains_to_show[$domains[$key]] != 1) {
				list ($k, $v) = each($domains_to_show[$domains[$key]]);
				$result_array[$k] = $v;
			}
		}
		if ($this -> _s -> clear == 1) $result_array_temp = array();
		$keys = array_keys($result_array);
		$maxweight = $result_array[$keys[0]];
		$count = '0';

		foreach ($result_array as $row) {
			$weight = $row;
			if ($this -> _s -> sort_results != '6') {		 //	  limit result output to min. relevance level or hits in full text
				if ($this -> _s -> sort_results != '7') {	 //	  no weight calculation for hits in full text
					$weight = number_format($row/$maxweight*100, 0);
					if ($weight >= $this -> _s -> relevance) {
						$count = ($count+1) ;
					}
				} else {
					if ($row >= $this -> _s -> relevance && $row > 0) {   //	  present results only if relevance is met AND hits in full text are available
						$count = ($count+1) ;
					}
				}

			} else {
				$count = ($count+1) ;
			}
		}

		if ($count != '0') {
			$result_array = array_chunk($result_array, $count, true);   //	  limit result output(weight > relevance level OR hits in fulltext > 0)
		}

		$result_array = $result_array[0];
		$results = count($result_array);
		for ($i = 0; $i <min($results, ($start -1)* $this -> _s -> max_results+ $this -> _s -> max_results) ; $i++) {
			$in[] = $keys[$i];
		}

		if (!is_array($in)) {
			$res['results'] = $results;
			if ($this -> _s -> clear == 1){
				unset ($results);
				$result_array   = array();
				$in			 = array();
				$keys		   = array();
			}
			return $res;
		}

		$inlist = implode(",", $in);

		if ($this -> _s -> length_of_link_desc == 0) {
			$fulltxt = "fulltxt";
		} else {
			$fulltxt = "substring(fulltxt, 1, {$this -> _s -> length_of_link_desc})";
		}

		$query1 = "SELECT distinct link_id, url, title, description,  $fulltxt, size, click_counter FROM ".$this -> _s -> mysql_table_prefix."links WHERE link_id in ($inlist)";

		$result = mysql_query($query1);

		if ($this -> _s -> debug > '0') echo mysql_error();
		$i = 0;
		while ($row = mysql_fetch_row($result)) {
//$all_text = str_replace("&nbsp;", "\r\n", $row[4]);
//echo "\r\n\r\n<br /> full_text:<br />\r\n$all_text\r\n<br />\r\n";
			$res[$i]['title'] = $row[2];
			$res[$i]['url'] = $row[1];
			if ($row[3] != null && $this -> _s -> show_meta_description == 1)
				$res[$i]['fulltxt'] = $row[3];
			else
				$res[$i]['fulltxt'] = $row[4];
			$res[$i]['size'] = $row[5];
			$res[$i]['click_counter'] = $row[6];
			$res[$i]['weight'] = $result_array[$row[0]];
			$dom_result = mysql_query("select domain from ".$this -> _s -> mysql_table_prefix."domains where domain_id='".$domains[$row[0]]."'");
			if ($this -> _s -> debug > '0') echo mysql_error();
			$dom_row = mysql_fetch_row($dom_result);
			$res[$i]['domain'] = $dom_row[0];
			$urlparts = parse_url($res[$i]['url']);
			//$res[$i]['path'] = $urlparts['path'];	//	  get full path
			$res[$i]['path'] = preg_replace('/([^\/]+)$/i', "", $urlparts['path']);	//	  get path without filename
			$res[$i]['maxweight'] = $maxweight;
			$res[$i]['results'] = $count;
			$res[$i]['db'] = $db_slv;	  //  all these results are from db (the currently active db)
			$i++;
		}
		if ($this -> _s -> clear == 1) {
			mysql_free_result($result);
			unset ($results, $inlist);
			$result_array   = array();
			$in	 = array();
			$keys   = array();
		}
//echo "\r\n\r\n<br>res Array:<br><pre>";print_r($res);echo "</pre>";
		return $res;
	}

	public function get_text_results($query, $start, $category, $searchtype, $results, $domain, $loop, $orig_query, $prefix) {
		$sph_messages = Model::getInstance() -> L10N;
//		global $sph_messages, $this -> _s -> results_per_page, $this -> _allwild, $this -> _s -> show_meta_description, $this -> _s -> title_length;
//		global $this -> _s -> links_to_next, $wildsearch, $this -> _s -> show_warning, $this -> _s -> mark, $this -> parameters -> type, $this -> _s -> home_charset, $this -> _s -> sort_results;
//		global $this -> _s -> show_query_scores,  $this -> _s -> index_host, $this -> _s -> url_length, $this -> _s -> max_hits, $this -> _s -> clear, $this -> _s -> mb, $this -> _s -> only_links;
//		global $this -> _s -> mysql_table_prefix, $this -> _s -> desc_length, $this -> _s -> case_sensitive, $this -> _s -> debug, $this -> _s -> debug_user, $this -> _s -> home_charset, $this -> _s -> greek, $this -> _s -> translit_el;
		//global $this -> _s -> use_cache, $this -> _s -> textcachedir, $this -> _s -> tcache_size, $this -> _s -> max_ctresults, $cn_seg, $dbu_act, $this -> _s -> out, $this -> _s -> xmldir, $this -> _s -> xmlname;
//		global  $this -> _s -> most_pop, $this -> _s -> pop_rows, $this -> _s -> tag_cloud, $this -> _s -> color_cloud, $this -> _s -> templatedir, $this -> parameters -> catid, $this -> parameters -> db, $this -> _s -> add_url;
//		global $cf -> db1_slv, $cf -> db2_slv, $cf -> db3_slv, $cf -> db4_slv, $cf -> db5_slv, $one_word, $mustbe_and, $nostalgic_phrase;
//		global $type_rem, $result_rem, $mark_rem, $sort_rem, $catid_rem, $cat_rem, $from, $to, $show_sort, $include_dir;

		$full_result = array();
		$xml_result  = array();

		$query1	 = $query;
		$this -> parameters -> type	   = $searchtype;
		$starttime  = Common::getmicrotime();

	 	if ($start==0)
			$start=1;

		if ($results != "") {
			$this -> _s -> results_per_page = $results;
		}

		if ($searchtype == "phrase") {
		   $query=str_replace('"','',$query);
		   $query = "\"".$query."\"";
		}

		// catch " if only entered once
		if (substr_count($query,'\"')==1){
		   $query=str_replace('\"','',$query);
		}

		if ($this -> _s -> case_sensitive == 0 && $searchtype != "phrase") {
			$query = Common::lower_ent($query);
			$query = Common::lower_case($query);
		}

		$words = $this -> makeboollist($query, $this -> parameters -> type);
		$ignorewords = array_key_exists('ignore', $words) ? $words['ignore'] :null;

		if (is_array($ignorewords)) {
			$full_result['ignore_words'] = $words['ignore'];
		}

		if ($query == 'pjswuc4290p') {
			$query = mk5($query);
		}


		// if cached results should be used
		$cache_query = str_replace('"', '', $query);
		if (!$domain && $this -> _s -> use_cache == '1' && !preg_match("/!|\/|\*|\~|#|%|<|>|\(|\)|{|}|\[|\]|\^|\\\/", $cache_query)) {
			$cache_ok = '1';
			if (!is_dir($this -> _s -> textcachedir)) {
				mkdir($this -> _s -> textcachedir, 0777);	//  if not exist, try to create folder for text cache
				if (!is_dir($this -> _s -> textcachedir)) {
					echo "<br />Unable to create folder for text cache<br />";
					$cache_ok = '';
				}
			}

			$no_cache = '1';
			if (is_dir($this -> _s -> textcachedir)) {
				$rd_handle = @fopen("".$this -> _s -> textcachedir."/".$cache_query."_".$this -> parameters -> type."_".$category.".txt", "r+b");
				if ($rd_handle) {
					$cache_result = file_get_contents("".$this -> _s -> textcachedir."/".$cache_query."_".$this -> parameters -> type."_".$category.".txt");
					if ($cache_result) {
						$no_cache = '';
						if ($this -> _s -> debug_user == '1') {
							echo "<small>Results found in cache.</small><br />";
						}

						//  update cache-file with new modified date and time
						file_put_contents("".$this -> _s -> textcachedir."/".$cache_query."_".$this -> parameters -> type."_".$category.".txt", $cache_result);

						//  make file content readable for result klisting
						$result = unserialize($cache_result);

						//  build result listing for one result page
						if ($start == '1') {
						$from = '0';
						} else {
							$from = ($start-1) * $this -> _s -> results_per_page;
						}
						$count = count($result);
						$int = array_slice($result, $count-3, '3');
						$result = array_merge(array_slice($result, $from, $this -> _s -> results_per_page), $int);
						if ($this -> _s -> clear == 1) $int = array();
					}
				}
				@fclose($rd_handle);
			}

			//	  get fresh results . No cache entry for this query available
			if ($no_cache == '1') {
				if ($this -> _s -> debug_user == '1') {
					echo "<small>No results found in cache.<br />Get fresh result from database.</small><br />";
				}
				$c_start = '1';	 //  cache needs all results, starting with the first
				$result = $this -> search($words, $category, $c_start, $this -> _s -> max_ctresults, $searchtype, $domain, $prefix);
			}
			if ($cache_ok == '1' && $no_cache == '1' && $result && !array_key_exists('did_you_mean', $result) && $result[0]['url']) {	 //	  create new cache file for new query input
				$wr_handle = @fopen ("{$this -> _s -> textcachedir}/{$cache_query}_{$this -> parameters -> type}_{$category}.txt", "r");
				if (!$wr_handle) {	 //   create new cache file for current query input
					$result_string = serialize($result);
					if ($this -> _s -> debug_user == '1') {
						echo "<small>Create new result file for cache.</small><br />";
					}
					$new_handle = @fopen("".$this -> _s -> textcachedir."/".$cache_query."_".$this -> parameters -> type."_".$category.".txt", "wb");
					if (!fwrite($new_handle, $result_string)) {
						echo "<br />Unable to write into text cache<br />";
					}
					@fclose($new_handle);

				} else {
					@fclose($wr_handle);
				}

				//	  get total size and time of creation for each cache file
				$size = '0';
				$all = array();
				$all_keys = array();
				$all_vals = array();
				if ($handle = opendir($this -> _s -> textcachedir)) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != "..") {
							$size = $size + (filesize("".$this -> _s -> textcachedir."/".$file.""));
							$created = filemtime("".$this -> _s -> textcachedir."/".$file."");
							$all_vals[] = $file;
							$all_keys[] = $created;
						}
					}
				}

				$cache_size = $this -> _s -> tcache_size * 1048576;		   //  cache size in Byte
				if ($size > $cache_size) {
					$all = array_combine($all_keys, $all_vals);
					ksort($all);								//  find oldest cache file
					$del = current($all);
					if ($this -> _s -> debug_user == '1') {
						echo "<small>Cache overflow. Delete least significant file in cache ($del)</small><br />";
					}
					@unlink("".$this -> _s -> textcachedir."/".$del."");	// delete oldest cache file
				}
				closedir($handle);
			}
		} else {
			//	  get fresh results without cache
			$result = $this -> search($words, $category, $start, $this -> _s -> results_per_page, $searchtype, $domain, $prefix);
		}
//echo "\r\n\r\n<br>result Array:<br><pre>";print_r($result);echo "</pre>\r\n";
		$words['hilight'] = $result && array_key_exists('hilight', $result) ? $result['hilight'] : '';
//echo "\r\n\r\n<br>words['hilight'] Array:<br><pre>";print_r($words['hilight']);echo "</pre>\r\n";
		$query		  = stripslashes($query);
		$num_of_results = '0';
		$entitiesQuery  = htmlspecialchars(str_replace("\"", "",$query));
		$endtime		= Common::getmicrotime() - $starttime;
		$rows		   = $result && array_key_exists('results', $result) ? $result['results'] : null;
		$time		   = round($endtime, 3);

		$full_result['ent_query']   = $entitiesQuery;
		$full_result['time']		= $time;

		$did_you_mean = "";

		if (isset($result['did_you_mean']) && $this -> _s -> translit_el != '1') {
			$did_you_mean_b=$entitiesQuery;
			$did_you_mean=$entitiesQuery;

			while (list($key, $val) = each($result['did_you_mean'])) {
				if ($key != $val) {
					$did_you_mean_b = str_replace($key, "<b>$val</b>", $did_you_mean_b);
					$did_you_mean = str_replace($key, "$val", $did_you_mean);
				}
			}
		} else {
			if (isset($result['did_you_mean'])) {

				while (list($key, $val) = each($result['did_you_mean'])) {
					if ($key != $val) {
						$did_you_mean_b = "<b>$val</b>";
						$did_you_mean = "$val";
					}
				}
			}
		}

		if ($did_you_mean) {
			$full_result['did_you_mean']	= $did_you_mean;
			$full_result['did_you_mean_b']  = $did_you_mean_b;
		}
		$matchword = $sph_messages["matches"];

		if ($rows == 1) {   //  single result; correct grammar
			$matchword = $sph_messages["match"];
		}

		if($result && !$did_you_mean) {   //  prevent negative results for count
			$num_of_results = count($result) - 3;
		}
		$full_result['num_of_results'] = $num_of_results;

		if ($start < 2 && $loop == '1') {
		//if ($start < 2 && $loop == '1' || ($loop == '2' && $rows != '0')) {	   // will count query-results also if fetched in second loop
			$ip = $_SERVER['REMOTE_ADDR'];
			Common::saveToLog(addslashes($orig_query), $time, $rows, $ip, 0);
		}
		$from = ($start-1) * $this -> _s -> results_per_page+1;
		$to = min(($start) * $this -> _s -> results_per_page, $rows);

		$full_result['from'] = $from;
		$full_result['to'] = $to;
		$full_result['total_results'] = $rows;

		if ($this -> _s -> out == 'xml') {	//  prepare the XML result file

			if (!$rows){
				$rows = '0';
			}
			$xml_result['query'] = $entitiesQuery;
			$xml_result['time'] = $time;
			$xml_result['total_results'] = $rows;
			$xml_result['num_of_results'] = $num_of_results;

			if ($did_you_mean) {
				$xml_result['did_you_mean'] = $did_you_mean;
			}

			if ($to) {
				$xml_result['from'] = $from;
				$xml_result['to'] = $to;
			}
		}

		if ($rows>0) {
			$maxweight = $result['maxweight'];
			$i = 0;
			while ($i < $num_of_results && $i < $this -> _s -> results_per_page) {
				$title = " ".$result[$i]['title'];
				$url = $result[$i]['url'];
				$fulltxt = " ".$result[$i]['fulltxt'];
				$page_size = $result[$i]['size'];
				$domain = $result[$i]['domain'];
				if ($this -> _s -> cn_seg == '0') {
					//	  create additional 'blank' behind comma etc. in Chinese  and Korean text
					//$fulltxt = $this -> separated($fulltxt);
				}
//echo "\r\n\r\n<br /> fulltxt: $fulltxt<br />\r\n";
				/*
										if ($this -> _s -> case_sensitive == '0') {
											//	 otherwise we could not highlight entities (if they are in first position of the word)
											$title   = Common::lower_case(Common::lower_ent($title));
											$fulltxt = Common::lower_case(Common::lower_ent($fulltxt));
										}
										 */
				$fulltxt = " ".$fulltxt."";
				$tmp = $fulltxt;
				if ($this -> _s -> case_sensitive == '0') {
					if ($this -> _s -> mb) {					  //	if available, use Multibyte extention of PHP
						$tmp = mb_strtolower($tmp);
					} else {
						$tmp = strtolower($tmp);
					}
				}

				if ($page_size != "") $page_size = number_format($page_size, 1)." kb";
				if ($this -> _allwild) $words = $this -> makeboollist($this -> _allwild, $this -> parameters -> type);

				$words[] = arsort($words['hilight']);	//  reverse order, to highlight voluminous words first
				$txtlen = strlen($fulltxt);
				$places = array();

				if ($txtlen > $this -> _s -> desc_length && !$this -> _s -> only_links) {
					$strictpos = strpos($query, '!');
					if ($strictpos === 0) {	 //	  if strict search enter here
						$recovered = str_replace('!', '',trim($query1));
						$words['hilight'][0] = "$recovered";  //  replace without ' ! '
						$strict_length =strlen($recovered);
						$found_in = '1';	//  pointer position start
						$pos_absolut	= '0';

						foreach($words['hilight'] as $word) {
							while (!($found_in =='')) {
								if ($this -> _s -> case_sensitive == 1 ) {
									$found_in = strpos($tmp, $word);	  //  find position of first query hit
								}else {
									$found_in = stripos($tmp, $word);
								}

								$tmp_front = substr($tmp, $found_in-1); //  one character before found match position
								$pos = $found_in+strlen($word);
								$pos_absolut = $pos_absolut + $found_in;
								$tmp = substr($tmp, $pos);  //  get rest of fulltxt

								//  check weather found match is realy strict
								$found_before = preg_match("/[(a-z)-_*.\/\:&@\w]/", substr($tmp_front, 0, 1));
								$found_behind = preg_match("/[(a-z)-_*.,\/\:&@\w]/", substr($tmp, 0, 1));

								if ($found_before ===0 && $found_behind ===0) {
									$places[] = $pos_absolut;   //  remind absolut position of match
									$found_in = '';
								}
							}
						}

					} else {	// if not strict search enter here (standard search)
						foreach($words['hilight'] as $word) {
							$hits = "0";
							if ($this -> _s -> case_sensitive == 1 ) {
								$found_in = strpos($tmp, $word);	  //  find position of first query hit
							}else {
								$found_in = stripos($tmp, $word);
							}

							if ($found_in == 'NULL') {
								$places[] = '0';				//	  if word was found in position 0
							}

							$sum = -strlen($word);
							$tmp_x = $tmp;
							while (!($found_in =='') && $hits < $this -> _s -> max_hits) {
								$pos = $found_in+strlen($word);
								$sum += $pos;		   //  fix position
								$places[] = $sum;	   //  save position

								$tmp_x = substr($tmp_x, $pos);	  //	  rest of full text

								if ($this -> _s -> case_sensitive == 1 ) {
									$found_in = strpos($tmp_x, $word);	  //  try to find position of next query hit
								}else {
									$found_in = stripos($tmp_x, $word);
								}
								$hits++;
							}
						}
					}

					sort($places);
					$x = 0;
					$begin = 0;
					$end = 0;
					while(list($id, $place) = each($places)) {
						while (array_key_exists($id + $x, $places) && $places[$id + $x] - $place < $this -> _s -> desc_length && $x + $id < count($places) && $place < strlen($fulltxt) - $this -> _s -> desc_length) {
							$x++;
							$begin =($id);
							$end = $id + $x;
						}
					}

					$this_text ="";
					$actual_hit="";
					$hit_id = 1;
					$begin_pos = intval(max(0, $places[$begin] - $this -> _s -> desc_length/3));

					if ($begin_pos < '10') $begin_pos = '0';	//  text from the real beginning
					$this_text = substr($fulltxt, $begin_pos, $this -> _s -> desc_length);
					$begin_pos1 = '0';
					if ($begin_pos > 0) {
						$begin_pos1 = strpos($this_text, " ");  //  find first 'blank' to start readable
					}
					$this_text = substr($this_text, $begin_pos1, $this -> _s -> desc_length);
					$this_text = substr($this_text, 0, strrpos($this_text, " "));   //	  find last 'blank' to end

					if ($begin_pos < 10) {  //  no dots in front of text
						$actual_hit = "<ul><li>" . $this_text . "&hellip;</li>";
					} else {

						$actual_hit = "<ul><li>&hellip;" . $this_text . "&hellip;</li>";
					}

					//if ($this -> _allwild && $this -> _s -> greek != '1') $this -> _s -> max_hits = '99';   //we need to show all places with different results from one page
					//  no longer required, as the Admin setting:
					//  "Define maximum count of result hits per page, displayed in search results (if multiple occurrence is available on a page)"
					//  has been implemented.

					while ($hit_id < count($places) && $hit_id < $this -> _s -> max_hits) {   //  if activated in Admin settings, show multiple hits

						if ($hit_id <> $begin) {
							$this_text ="";
							$begin_pos = intval(max(0, $places[$hit_id] - $this -> _s -> desc_length/3));
							$this_text = substr($fulltxt, $begin_pos, $this -> _s -> desc_length);

							if ($places[$hit_id] > 0) {
								$begin_pos1 = strpos($this_text, " ");
							}
							$this_text = substr($this_text, $begin_pos1, $this -> _s -> desc_length);
							$this_text = substr($this_text, 0, strrpos($this_text, " "));
							if ($this_text<> "")
								$actual_hit .= "<li>&hellip;" . $this_text . "&hellip;</li>";
						}
						$hit_id++;

						while ($places[$hit_id] < ($begin_pos + $this -> _s -> desc_length) && $hit_id < $this -> _s -> max_hits) {
							$hit_id++;				  //	  if hit is in the current extract of full text, try with the next hit
						}
					}
					$fulltxt= $actual_hit . " </ul>";	//	  new definition for fulltxt as the extract of full text around the actual query position
				} else {
					//  Enter here, if full text is shorter than 'Maximum length of page summary' as defined in Admin settings
					$fulltxt = "<ul><li>" .$fulltxt ."</li>";
					$strictpos = strpos($query, '!');
					if ($strictpos === 0) {	 //	  if strict search enter here
						$recovered = str_replace('!', '',trim($query1));
						$words['hilight'][0] = "$recovered";  //  replace without ' ! '
					}

					foreach($words['hilight'] as $word) {
						$found_in = strpos($tmp, $word);	//	  find position of first hit
						if ($found_in == 'NULL') {
							$places[] = '0';				//	  if word was found in position 0
						} else {
							$places[] = $found_in;
						}
					}
					sort($places);
				}

				if ($this -> _s -> sort_results != '7' && $this -> _s -> sort_results != '6') {
					$weight = number_format($result[$i]['weight']/$maxweight*100, 1);   //  calculate percentage of weight
				}

				if ($this -> _s -> sort_results == '7' || $this -> _s -> sort_results == '6') {
					$weight = $result[$i]['weight'];		//  use hits in fullttext or indexdate instead of weight
				}
				if ($title=='')
					$title = $sph_messages["Untitled"];

				if (strlen($title) > $this -> _s -> title_length) {				   // if necessary shorten length of title in result page
					$length_tot = strpos($title, " ",$this -> _s -> title_length);	// find end of last word for shortened title
					if ($length_tot) {
						$title = substr($title, 0, $length_tot)." ...";
					}
				}

				$url2 = $url;

				if (strlen($url) > $this -> _s -> url_length) {	// if necessary shorten length of URL in result page
					$url2 = substr($url, 0, $this -> _s -> url_length)."...";
				}

				if (!$this -> _s -> only_links) {	 //  not required, if search only search for link text. Already highlighted in function links_only()
					if ($places[0] == '' && $this -> _s -> sort_results == 7  && $this -> parameters -> type != 'tol') {	 //  if nothing found in HTML text and query hits as result output
						$weight = '0';
					}

					if ($places[0] == '' && $this -> _s -> show_warning == '1' && $this -> parameters -> type !='tol' && !$this -> _s -> only_links || ( $this -> _s -> show_warning == '1' && $weight == '0')) {  // if  no HTML text to highlight
						$warnmessage = $sph_messages['showWarning'];
						$fulltxt = "<span class='warn'>$warnmessage</span>";
					}

					$highlight = '';
					$highlight = "span class=\"mak_1\"";

					foreach($words['hilight'] as $change) {
						if (!($strictpos === 0) && $this -> _s -> index_host == '1' && !$this -> _s -> only_links) {  //  not for strict search and link-only search
							$url2 = $this -> highlight($url2, $change, $highlight);
						}
						if ($strictpos === 0 ) {			//	  for strict-search mark the word with blanks before and behind
							if ($places[0] == '0') {		//	  if keyword was found in position 0
								$change = "".$change." ";   //	  create blanks in order to mark only the pure word
							} else {
								$change = " ".$change." ";  //	  create blanks in order to mark only the pure word
								$title  = " ".$title." ";   //	 create blanks as first and last character in title
							}
						}

						$title = $this -> highlight($title, $change, $highlight);
						$fulltxt = $this -> highlight($fulltxt, $change, $highlight);

					}
				}

				$title	  = str_replace("=ssalc", "class=", $title);  //  restore 'class='
				$fulltxt	= str_replace("=ssalc", "class=", $fulltxt);

				$title = str_replace("naps/<", "<span", $title);		//  restore '<span'
				$title = str_replace("naps/<", "<span", $title);

				$fulltxt = str_replace("naps/<", "</span", $fulltxt);   //  replace '</span'
				$fulltxt = str_replace("naps/<", "</span", $fulltxt);

				$num	= $from + $i;

				$full_result['qry_results'][$i]['num']		  =  $num;
				$full_result['qry_results'][$i]['weight']	   =  $weight;
				$full_result['qry_results'][$i]['url']		  =  $url;
				$full_result['qry_results'][$i]['title']		=  $title;
				$full_result['qry_results'][$i]['fulltxt']	  =  $fulltxt;
				$full_result['qry_results'][$i]['url2']		 =  $url2;
				$full_result['qry_results'][$i]['page_size']	=  $page_size;
				$full_result['qry_results'][$i]['domain_name']  =  $domain;

				if ($this -> _s -> out == 'xml') {	//  prepare the XML result file
					//  remove tags from title
					$xml_title = preg_replace ("/<span.*?>/", "", $title);
					$xml_title = str_replace ("</span>", "", $xml_title);
					$xml_title = str_replace ("<strong>", "", $xml_title);
					$xml_title = str_replace ("</strong>", "", $xml_title);

					//  remove tags from fulltext
					$xml_txt = preg_replace ("/<span.*?>/", "", $fulltxt);
					$xml_txt = str_replace ("</span>", "", $xml_txt);
					$xml_txt = str_replace ("<strong>", "", $xml_txt);
					$xml_txt = str_replace ("</strong>", "", $xml_txt);

					if ($this -> _s -> max_hits == '1') {	 //  text separator for multiple occurrence is not required for single result
						$xml_txt = preg_replace ("/<ul>|<li>/", "", $xml_txt);
						$xml_txt = str_replace ("</ul>", "", $xml_txt);
						$xml_txt = str_replace ("</li>", "", $xml_txt);
					}

					$xml_result['text_results'][$i]['num']		  =  $num;
					$xml_result['text_results'][$i]['weight']	   =  $weight;
					$xml_result['text_results'][$i]['url']		  =  $url;
					$xml_result['text_results'][$i]['title']		=  $xml_title;
					$xml_result['text_results'][$i]['fulltxt']	  =  $xml_txt;
					$xml_result['text_results'][$i]['page_size']	=  $page_size;
					$xml_result['text_results'][$i]['domain_name']  =  $domain;
				}

				$i++;
			}
			if ($this -> _s -> clear == 1) $places = array();  //  reset array
		}

		$pages				  = ceil($rows / $this -> _s -> results_per_page);
		$full_result['pages']   = $pages;
		$prev				   = $start - 1;
		$full_result['prev']	= $prev;
		$next				   = $start + 1;
		$full_result['next']	= $next;
		$full_result['start']   = $start;
		$full_result['query']   = $entitiesQuery;

		if ($from <= $to) {

			$firstpage = $start - $this -> _s -> links_to_next;
			if ($firstpage < 1) $firstpage = 1;
			$lastpage = $start + $this -> _s -> links_to_next;
			if ($lastpage > $pages) $lastpage = $pages;

			for ($x=$firstpage; $x<=$lastpage; $x++)
				$full_result['other_pages'][] = $x;

		}
//echo "<br>full_result Array:<br><pre>";print_r($full_result);echo "</pre>";

		if ($this -> _s -> out == 'xml' && $this -> _s -> xmlname) {	//  build the XML output file

			//add the page infos to XML array
			if ($pages > '1') {
				$xml_result['pages']   = $pages;
				$xml_result['prev']	= $prev;
				$xml_result['next']	= $next;
				$xml_result['start']   = $start;
			}

			//  now convert the result array to XML file
			convert_xml($xml_result, 'text');
		}

		if ($this -> _s -> clear == 1) {
			unset ($fulltxt);
			$words	  = array();
			$result	 = array();
			$xml_result = array();
		}

		return $full_result;
	}

	public function separated($string) {
		$sep = array
			(
			"\," => "\, ",
			"\?" => "\? ",
			";" => "; ",
			"\?" => "\? ",
			"\!" => "\! ",
			"\?" => "\? ",
			"â€œ" => "â€œ ",
			"â€" => "â€ ",
			"\"" => "\" "
			);
		reset($sep);
		while ($char = each($sep)) {
			$string = preg_replace("/".$char[0]."/i", $char[1], $string);
		}
		return $string;
	}

	public function highlight($string, $change, $highlight) {
//		global $this -> _s -> case_sensitive, $this -> _s -> mb;

		if ($this -> _s -> mb){
			$offset = '0';
			$length = mb_strlen($change)+ mb_strlen($highlight);

			if ($change == "class") {
				$string = str_replace("class=", "=ssalc", $string); //  replace 'class='
			}

			if ($change == "span") {
				$string = str_replace("<span", "naps<", $string);   //  replace '<span'
				$string = str_replace("</span", "naps/<", $string); //  replace '</span'
			}

			if ($this -> _s -> case_sensitive == 1 ) {
				$found_in = mb_strpos($string, $change, $offset);   //  find position of first query hit to be highlighted
			}else {
				$string = str_replace("I", "Â°i", $string);		  //  mb_stripos does not like it, replace the I
				$found_in = mb_stripos($string, $change, $offset);
			}

			while (!($found_in =='')) {		 //  loop through all hits in full text
				if ($change == "class") {
					$string = str_replace("class=", "=ssalc", $string);	 //  replace 'class='
				}

				$beginn = mb_substr($string, 0, $found_in);		 //  string until word to be highlighted
				$rest   = mb_substr($string, $found_in);			//  rest of string incl. word to be highlighted
				$string = "".$beginn."<".$highlight.">".$rest."";   //  include the highlight start-tag

				$end = $found_in+$length+2;		 //   find end of word to be highlighted. +2 because< and > are added to $highlight
				$rest_all = mb_substr($string, $end);
				$string = "".mb_substr($string, 0, $end)."</span>".$rest_all."";	//  include highlight end-tag
				$offset = $end +7;  //  +7 because </span> was added

				if ($this -> _s -> case_sensitive == 1 ) {
					$found_in = mb_strpos($string, $change, $offset);	   //  try to find position of next hit
				}else {
					$found_in = mb_stripos($string, $change, $offset);
				}
			}

			$string = str_replace("=ssalc", "class=", $string);			 //  restore 'class='
			$marc_i = "Â°<".$highlight.">i";
			$string = str_replace($marc_i, "<".$highlight.">I", $string);   //  replace the highlighted  I
			$string = str_replace("Â°i", "I", $string);					  //  replace the I
			return $string;

		} else {
			$offset = '0';
			$length = strlen($change)+ strlen($highlight);

			if ($change == "class") {
				$string = str_replace("class=", "=ssalc", $string); //  replace 'class='
			}

			if ($this -> _s -> case_sensitive == 1 ) {
				$found_in = strpos($string, $change, $offset);	  //  find position of first query hit to be highlighted
			}else {
				$string = str_replace("I", "Â°i", $string);		  //  stripos does not like it, replace the I
				$found_in = stripos($string, $change, $offset);
			}

			while (!($found_in =='')) {
				if ($change == "class") {
					$string = str_replace("class=", "=ssalc", $string);	 //  replace 'class='
				}

				$beginn = substr($string, 0, $found_in);			//  string until word to be highlighted
				$rest   = substr($string, $found_in);			   //  rest of string incl. word to be highlighted
				$string = "".$beginn."<".$highlight.">".$rest."";   //  include the highlight start-tag

				$end = $found_in+$length+2;	 //   find end of word to be highlighted. +2 because< and > are added to $highlight
				$rest_all = substr($string, $end);
				$string = "".substr($string, 0, $end)."</span>".$rest_all."";	//  include highlight end-tag
				$offset = $end +7;  //  +7 because </span> was added

				if ($this -> _s -> case_sensitive == 1 ) {
					$found_in = strpos($string, $change, $offset);		  //  try to find position of next hit
				}else {
					$found_in = stripos($string, $change, $offset);
				}
			}

			//$string = str_replace("=ssalc", "class=", $string);			 //  restore 'class='
			$marc_i = "Â°<".$highlight.">i";
			$string = str_replace($marc_i, "<".$highlight.">I", $string);   //  replace the highlighted  I
			$string = str_replace("Â°i", "I", $string);					  //  replace the I
			return $string;
		}
	}
}