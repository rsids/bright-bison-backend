<?php
class SearchMedia extends BaseSearch {
	//  search for images and present them as part of the result listing (text + media)
	public function image($query, $url, $media_type, $all, $urlx, $title1, $image_dir, $info, $info2, $thumb, $mode, $media_only, $type, $category, $catid, $mark, $db, $prefix, $domain) {
		global $include_dir, $admin_dir, $sph_messages, $mysql_table_prefix, $template, $template_dir, $index_id3;

		$query		  = str_replace('*', '', $query);	  //	  kill wildcards, as media search already includes it
		$media_results  = get_media_results($query, $url, $media_type, $all, $domain, $prefix);
		reactivate_dbuact();

		if ($media_results) {
			//   display header for image results
			include "".$template_dir."/html/130_image-results header.html";

			$i = '0';
			while (list($key, $value) = each($media_results)) {
				//	  prepare current object-link for click counter
				$link_crypt  = str_replace("&", "-_-", $value[3]);	//  crypt the & character
				$link_click  = "$include_dir/media_counter.php?url=$link_crypt&amp;query=$query&amp;db=$db&amp;prefix=$prefix";   //  redirect users click in order to update Most Media Popular Links
				$thumb_link	   = str_replace('./', $admin_dir, $value[4]);
				//   display  image results
				include "".$template_dir."/html/140_image-results.html";
			}
				//   display  end image results table
			include "".$template_dir."/html/150_end image-results.html";
		}
		return ($media_results);
	}

	//  search for audio and video streams and present them as part of the result listing (text + media)
	public function media($query, $url, $media_type, $all, $urlx, $title1, $image_dir, $info, $info2, $thumb, $mode, $media_only, $type, $category, $catid, $mark, $db, $prefix, $domain) {
		global $include_dir, $admin_dir, $sph_messages, $template, $template_dir, $index_id3;

		$orig_query	 = $query;
		$starttime	  = $this -> common -> getmicrotime();
		$query		  = str_replace('*', '', $query);	  //	  kill wildcards, as media search already includes it
		$media_results  = get_media_results($query, $url, $media_type, $all, $domain, $prefix);

		reactivate_dbuact();
		//  save info to query_log
		$endtime = $this -> common -> getmicrotime() - $starttime;
		$rows = count($media_results);
		$time = round($endtime*100)/100;
		$ip = $_SERVER['REMOTE_ADDR'];
		//saveToLog(addslashes($orig_query), $time, $rows, $ip, 1);

		if ($media_results) {
			//  display header for stream results
			include "".$template_dir."/html/160_stream-results header.html";

			while (list($key, $value) = each($media_results)) {
				$id3_array = explode("<br />",$value[12]);   //  separate ID3 and EXIF data
				$time = $id3_array[7];
				$playtime = substr($time, strrpos($time, ';;')+3);  // get play time
				if ($playtime) {
					$minutes = $sph_messages['minutes'];
					$playtime = "".$playtime."&nbsp;&nbsp;".$minutes."";
				}

				//	  prepare current object-link for click counter
				$link_crypt  = str_replace("&", "-_-", $value[3]);	//  crypt the & character
				$link_click  = "$include_dir/media_counter.php?url=$link_crypt&amp;query=$query&amp;db=$db&amp;prefix=$prefix";   //  redirect users click in order to update Most Media Popular Links

			//   display  stream results
			include "".$template_dir."/html/170_stream-results.html";
			}
			//   display end of stream result table
			include "".$template_dir."/html/180_end stream-results.html";

		}
		return ($media_results);
	}

	//  if 'query' meets only media results or 'Search Media only' is selected in Search Field, enter here
	public function media_only($query, $start, $media_only, $type, $category, $catid, $mark, $db, $prefix, $domain) {
		global $db_con, $mysql_table_prefix, $debug, $debug_user, $admin_dir, $include_dir, $case_sensitive;
		global $results_per_page, $image_dir, $sph_messages, $dbu_act, $template, $template_dir, $index_id3;
		global $use_cache, $mediacache_dir, $mcache_size, $max_cmresults, $max_results;
		global $dbu_act, $db1_slv, $db2_slv, $db3_slv, $db4_slv, $db5_slv;
		global $mytitle, $show_categories, $has_categories, $checked_cat, $tpl, $checked_all;
		global $adv, $advanced_search, $show_media, $description, $embedded;
		global $out, $xml_dir, $xml_name;

		$orig_query  = $query;
		$starttime   = $this -> common -> getmicrotime();
		$query = str_replace('*', '', $query);	  //	  kill wildcards, as media search already includes it

		//  get name of category for current Search User db with given catid as defined in Search Form
		$result =  mysql_query("select category from ".$mysql_table_prefix."categories
									where category_id ='$catid'");
		$row = mysql_fetch_row($result);	//	  arry contains category name

		if ($domain) {  //  prepare the mysql query for domain search
			$domain_qry = "AND link_addr like '%".$domain."%'";
		} else {
			$domain_qry = "";
		}

		if ($debug_user == '1') {
			$slv1 = '';
			$slv2 = '';
			$slv3 = '';
			$slv4 = '';
			$slv5 = '';
			if ($db1_slv == 1)  $slv1 = '1,';
			if ($db2_slv == 1)  $slv2 = '2,';
			if ($db3_slv == 1)  $slv3 = '3,';
			if ($db4_slv == 1)  $slv4 = '4,';
			if ($db5_slv == 1)  $slv5 = '5';

			echo "	  <small>Results from database ".$slv1." ".$slv2." ".$slv3." ".$slv4." ".$slv5."</small>
	  <br />
";
		}

		$q1 = $query;
		$result ='';
		if ($query == '')   $q1 = '&nbsp;'; //	prevent blank results for media search
		if ($query == 'media:')  $q1 = '';  //	search for all media files in database /category

		// if cached results should be used
		$cache_query = str_replace('"', '', $query);
		if (!$domain && $use_cache == '1' && !preg_match("/!|\/|\*|\~|#|%|<|>|\(|\)|{|}|\[|\]|\^|\\\/", $cache_query)) {
			$cache_ok = '1';
			if (!is_dir($mediacache_dir)) {
				mkdir($mediacache_dir, 0777);	//if not exist, try to create folder for media cache
				if (!is_dir($mediacache_dir)) {
					echo "<br />Unable to create folder for media cache<br />";
					$cache_ok = '';
				}
			}

			$no_cache = '1';
			if (is_dir($mediacache_dir)) {
				$rd_handle = fopen("".$mediacache_dir."/".$cache_query."_".$type."_".$category.".txt", "r+b");
				if ($rd_handle) {
					$cache_result = file_get_contents("".$mediacache_dir."/".$cache_query."_".$type."_".$category.".txt");
					if ($cache_result) {
						$no_cache = '';
						 if ($debug_user == '1') {
							echo "<small>Results found in cache</small><br />";
						}
					   //  update cache-file with new modified date and time
						file_put_contents("".$mediacache_dir."/".$cache_query."_".$type."_".$category.".txt", $cache_result);
						//  make file content readable for result klisting
						$media_results = unserialize($cache_result);
					}
				}
				fclose($rd_handle);
			}

			//	  get fresh results . No cache for this query available
			if ($no_cache == '1') {
				if ($debug_user == '1') {
					echo "<small>No results found in cache.<br />Get fresh result from database.</small><br />";
				}

				$media_results = all_fresh($query, $q1, $domain_qry, $mysql_table_prefix, $catid, $prefix);

				$media_count = count($media_results);
				//	  if query did not match any media object
				if ($media_count < '1'){
					$msg = str_replace ('%query', htmlentities(utf8_decode($query)), $sph_messages["noMediaMatch"]);
//   display no media results found
					include "".$template_dir."/html/200_no media found.html";
					return('');
				}

			}
			$media_results = array_slice($media_results, 0, $max_cmresults);	//  reduce to max allowed results per query
			if ($cache_ok == '1' && $no_cache == '1' && $media_results[0][2]) {	 //	  create new cache file for new query input
				$wr_handle = fopen ("".$mediacache_dir."/".$cache_query."_".$type."_".$category.".txt", "r");
				if (!$wr_handle) {	 //   create new cache file for current query input
					$result_string = serialize($media_results);
					if ($debug_user == '1') {
						echo "<small>Create new result file for cache.</small><br />";
					}
					$new_handle = fopen("".$mediacache_dir."/".$cache_query."_".$type."_".$category.".txt", "wb");
					if (!fwrite($new_handle, $result_string)) {
						echo "<br />Unable to write into media cache<br />";
					}
					fclose($new_handle);

				} else {
					fclose($wr_handle);
				}

				//	  get total size and time of creation for each cache file
				$size = '0';
				$all = array();
				$all_keys = array();
				$all_vals = array();
				if ($handle = opendir($mediacache_dir)) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != "..") {
							$size = $size + (filesize("".$mediacache_dir."/".$file.""));
							$created = filemtime("".$mediacache_dir."/".$file."");
							$all_vals[] = $file;
							$all_keys[] = $created;
						}
					}
				}

				$cache_size = $mcache_size * 1048576;		   //  cache size in Byte
				if ($size > $cache_size) {
					$all = array_combine($all_keys, $all_vals);
					ksort($all);								//  find oldest cache file
					$del = current($all);
					@unlink("".$mediacache_dir."/".$del."");	// delete oldest cache file
					if ($debug_user == '1') {
						echo "<small>Cache overflow. Delete least significant file in cache ($del)</small><br />";
					}

				}
				closedir($handle);
			}
		} else {	//	  get fresh results without cache

			$media_results = all_fresh($query, $q1, $domain_qry, $mysql_table_prefix, $catid, $prefix);
		}

		//  limit amount of results in result listing shown for pure media search
		$media_results = array_slice($media_results, 0, $max_results, TRUE);

		//  save info to query_log
		$endtime	= $this -> common -> getmicrotime() - $starttime;
		$media_count = count($media_results);
		$time	   = round($endtime, 3);
		$ip		 = $_SERVER['REMOTE_ADDR'];
		$orig_query = str_replace ("*", "", $orig_query);   //  remove wildcard character

		saveToLog(addslashes($orig_query), $time, $media_count, $ip, 1);

		//  if activated, prepare the XML result file
		if ($out == 'xml' && $xml_name) {
			media_xml($media_results, $media_count, $orig_query, $time);
		}

		//	  if query did not match any media object
		if ($media_count < '1'){
			$msg = str_replace ('%query', htmlentities(utf8_decode($query)), $sph_messages["noMediaMatch"]);
//   display no media results found
			include "".$template_dir."/html/200_no media found.html";
			return('');
		}

		//Prepare results for listing
		$pages  = ceil($media_count / $results_per_page);   // Calculate count of required pages
		$class  = "odrow";

		if (empty($start)) $start = '1';				// As $start is not jet defined this is required for the first result page
		if ($start == '1') {
			$from = '0';								// Also for first page in order not to multipy with 0
		}else{
		$from = ($start-1) * $results_per_page;		 // First $num_row of actual page
		}

		$to = $media_count;							 // Last $num_row of actual page
		$rest = $media_count - $start;
		if ($media_count > $results_per_page) {		 // Display more then one page?
			$rest = $media_count - $from;
			$to = $from + $rest;						// $to for last page
			if ($rest > $results_per_page) $to = $from + ($results_per_page); // Calculate $num_row of actual page
		}

		//  result listing starts here
		if ($media_count > '0') {

			$fromm = $from+1;
			$result = $sph_messages['Results'];
			$result = str_replace ('%from', $from, $result);
			$result = str_replace ('%to', $to, $result);
			$result = str_replace ('%all', $media_count, $result);
			$matchword = $sph_messages["matches"];

			if ($media_count== 1) {
				$matchword= $sph_messages["match"];
			} else {
				$matchword= $sph_messages["matches"];
			}

			$result = str_replace ('%matchword', $matchword, $result);
			$result = str_replace ('%secs', $time, $result);

//   display header for media-only results
			include "".$template_dir."/html/110_media-only header.html";
			//  loop through all results
			for ($i=$from; $i<$to; $i++) {
				$this_media=$media_results[$i];
				//	  prepare current object-link for media counter
				$media_crypt  = str_replace("&", "-_-", $this_media[3]);	//  crypt the & character
				$media_click  = "$include_dir/media_counter.php?url=$media_crypt&amp;query=$query&amp;db=$db&amp;prefix=$prefix";	 //  redirect users click in order to update Most Popular Media
				//	  prepare current page-link for click counter
				$link_crypt  = str_replace("&", "-_-", $this_media[2]);
				$link_click  = "$include_dir/click_counter.php?url=$link_crypt&amp;query=$query&amp;db=$db&amp;prefix=$prefix";	   //  redirect users click in order to update Most Popular Links

				$thumb_link = str_replace("./", "", $this_media[4]);
				$i_1 = $i+1;					//  so table output does not start with zero

				$title = array();
				$result = mysql_query("select title from ".$mysql_table_prefix."links
										where link_id = ".$this_media[1]." ");  //   if available get title of current page
				if ($debug > '0') echo mysql_error();

				if (mysql_num_rows($result) > '0') {
					$title = mysql_fetch_row($result);
				}

				if ($class =="odrow")
					$class = "evrow";
				else
					$class = "odrow";
//   display  media-only result listing
				include "".$template_dir."/html/120_media-only results.html";
			}

//   display  end of result listing and links to other result pages
			include "".$template_dir."/html/190_more media-results.html";
		}
	}

	public function get_media_results($query, $link, $media_type, $all, $domain, $prefix) {
		global $dbu_act, $user_db, $db1_slv, $db2_slv, $db3_slv, $db4_slv, $db5_slv;
		global $database1, $database2, $database3, $database4, $database5;
		global $mysql_table_prefix1, $mysql_table_prefix2, $mysql_table_prefix3, $mysql_table_prefix4, $mysql_table_prefix5;
		global $db_con, $debug;

		$media_results = array();
		$valid = "1";

		if ($db1_slv == 1 && !$user_db || $user_db == 1) {
			$db_con	 = db1_connect() ;
			$valid = "1";
			$found = "0";
			if ($prefix > '0' ) {	   //	  if requested by the Search Form, we need to use the shifted table-suffix
				$valid = "0";
				$result = mysql_query("SHOW TABLES");
				$num_rows = mysql_num_rows($result);

				for ($i = 0; $i < $num_rows; $i++) {	//  the shifted table-suffix is part of this database?
					$found = strstr(mysql_tablename($result, $i), $prefix);	//  will create a non-zero value if tablename found

					if ($found) {
						$valid = "1";
					}
				}

				mysql_free_result($result);
				$mysql_table_prefix = $prefix;	  //  replace the tablesuffix
			} else {
				$mysql_table_prefix = $mysql_table_prefix1;
			}

			if ($valid) { //   for standard table-suffix, or if shifted suffix is valid for this db

				$media_results = thislink_media($query, $link, $media_type, $all, $domain, $mysql_table_prefix);
			}
		}

		if ($db2_slv == 1 && !$user_db || $user_db == 2) {
			$db_con = db2_connect() ;
			$valid = "1";
			$found = "0";
			$media_resultx = array();
			if ($prefix > '0' ) {
				$valid = "0";
				$result = mysql_query("SHOW TABLES");
				$num_rows = mysql_num_rows($result);

				for ($i = 0; $i < $num_rows; $i++) {
					$found = strstr(mysql_tablename($result, $i), $prefix);	 //  will create a non-zero value if tablename found

					if ($found) {
						$valid = "1";
					}
				}

				mysql_free_result($result);
				$mysql_table_prefix = $prefix;
			} else {
				$mysql_table_prefix = $mysql_table_prefix2;
			}
			if ($valid) {
				$media_resultx = thislink_media($query, $link, $media_type, $all, $domain, $mysql_table_prefix);
				if ($media_results && is_array($media_resultx)) {
					$media_results = array_merge($media_results, $media_resultx);
				} else{
					if (is_array($media_resultx)) {
						$media_results = $media_resultx;
					}
				}
			}
		}

		if ($db3_slv == 1 && !$user_db || $user_db == 3) {
			$db_con = db3_connect() ;
			$valid = "1";
			$found = "0";
			$media_resultx = array();
			if ($prefix > '0' ) {
				$valid = "0";
				$result = mysql_query("SHOW TABLES");
				$num_rows = mysql_num_rows($result);

				for ($i = 0; $i < $num_rows; $i++) {
					$found = strstr(mysql_tablename($result, $i), $prefix);	 //  will create a non-zero value if tablename found

					if ($found) {
						$valid = "1";
					}

				}
				mysql_free_result($result);
				$mysql_table_prefix = $prefix;
			} else {
				$mysql_table_prefix = $mysql_table_prefix3;
			}
			if ($valid) {
				$media_resultx = thislink_media($query, $link, $media_type, $all, $domain, $mysql_table_prefix);
				if ($media_results && is_array($media_resultx)) {
					$media_results = array_merge($media_results, $media_resultx);
				} else{
					if (is_array($media_resultx)) {
						$media_results = $media_resultx;
					}
				}
			}
		}

		if ($db4_slv == 1 && !$user_db || $user_db == 4) {
			$db_con = db4_connect() ;
			$valid = "1";
			$found = "0";
			$media_resultx = array();
			if ($prefix > '0' ) {
				$valid = "0";
				$result = mysql_query("SHOW TABLES");
				$num_rows = mysql_num_rows($result);

				for ($i = 0; $i < $num_rows; $i++) {
					$found = strstr(mysql_tablename($result, $i), $prefix);	 //  will create a non-zero value if tablename found

					if ($found) {
						$valid = "1";
					}

				}
				mysql_free_result($result);
				$mysql_table_prefix = $prefix;
			} else {
				$mysql_table_prefix = $mysql_table_prefix4;
			}
			if ($valid) {
				$media_resultx = thislink_media($query, $link, $media_type, $all, $domain, $mysql_table_prefix);
				if ($media_results && is_array($media_resultx)) {
					$media_results = array_merge($media_results, $media_resultx);
				} else{
					if (is_array($media_resultx)) {
						$media_results = $media_resultx;
					}
				}
			}
		}

		if ($db5_slv == 1 && !$user_db || $user_db == 5) {
			$db_con = db5_connect() ;
			$valid = "1";
			$found = "0";
			$media_resultx = array();
			if ($prefix > '0' ) {
				$valid = "0";
				$result = mysql_query("SHOW TABLES");
				$num_rows = mysql_num_rows($result);

				for ($i = 0; $i < $num_rows; $i++) {
					$found = strstr(mysql_tablename($result, $i), $prefix);	 //  will create a non-zero value if tablename found

					if ($found) {
						$valid = "1";
					}

				}
				mysql_free_result($result);
				$mysql_table_prefix = $prefix;
			} else {
				$mysql_table_prefix = $mysql_table_prefix5;
			}
			if ($valid) {
				$media_resultx = thislink_media($query, $link, $media_type, $all, $domain, $mysql_table_prefix);
				if ($media_results && is_array($media_resultx)) {
					$media_results = array_merge($media_results, $media_resultx);
				} else{
					if (is_array($media_resultx)) {
						$media_results = $media_resultx;
					}
				}
			}
		}

		return $media_results;
	}

	//  search for media files in one link
	public function thislink_media($query, $link, $media_type, $all, $domain, $mysql_table_prefix) {
		global $db_con, $debug, $case_sensitive, $max_results;

		$media_results = array();
		if ($all =='1') {   //  find all media-type of this page
			$result = mysql_query("select * from ".$mysql_table_prefix."media
									where link_addr = '$link' AND type = '$media_type'
									order by type AND title ");
		} else {
			//  search for results in title of media
			if ($case_sensitive =='0') {
				$result = mysql_query("select * from ".$mysql_table_prefix."media
										where link_addr = '$link' AND type = '$media_type' AND LOWER(title) like LOWER('%".(Connection::getInstance() -> escape_string($query))."%')
										order by title ");
			} else {
				$result = mysql_query("select * from ".$mysql_table_prefix."media
										where link_addr = '$link' AND type = '$media_type' AND title like ('%".(Connection::getInstance() -> escape_string($query))."%')
										order by title ");
			}
		}
		if ($debug > '0') echo mysql_error();

		if (mysql_num_rows($result) == '0') {
			$media_results = '';

			return $media_results;
		}
		while ($row = mysql_fetch_row($result)) {
			$media_results[] = $row;				//  collect all results into one array
		}

		//  limit amount of results in result listing shown per page/link for combined text and media search
		$media_results = array_slice($media_results, 0, $max_results, TRUE);
		return $media_results;
	}


	public function all_fresh($query, $q1, $domain_qry, $mysql_table_prefix, $catid, $prefix){
		global $dbu_act, $db1_slv, $db2_slv, $db3_slv, $db4_slv, $db5_slv;
		global $database1, $database2, $database3, $database4, $database5, $sph_messages;
		global $mysql_table_prefix1, $mysql_table_prefix2, $mysql_table_prefix3, $mysql_table_prefix4, $mysql_table_prefix5;

		$res = array();
		//  get results from all involved databases
		if ($db1_slv == 1) {
			$db_con	 = db1_connect() ;
			$valid = "1";
			$found = "0";
			$media_results = array();
			if ($prefix > '0' ) {	   //	  if requested by the Search Form, we need to use the shifted table-suffix
				$valid = "0";
				$result = mysql_query("SHOW TABLES");
				$num_rows = mysql_num_rows($result);

				for ($i = 0; $i < $num_rows; $i++) {	//  the shifted table-suffix is part of this database?
					$found = strstr(mysql_tablename($result, $i), $prefix);	//  will create a non-zero value if tablename found

					if ($found) {
						$valid = "1";
					}
				}

				mysql_free_result($result);
				$mysql_table_prefix = $prefix;	  //  replace the tablesuffix
			} else {
				$mysql_table_prefix = $mysql_table_prefix1;
			}

			if ($valid) {
				$db_slv = '1';   // active db
				$res = fresh_media($query, $q1, $domain_qry, $mysql_table_prefix, $catid);
			}
		}

		if ($db2_slv == 1) {
			$db_con = db2_connect() ;
			$valid = "1";
			$found = "0";
			if ($prefix > '0' ) {
				$valid = "0";
				$result = mysql_query("SHOW TABLES");
				$num_rows = mysql_num_rows($result);
				for ($i = 0; $i < $num_rows; $i++) {	//  the shifted table-suffix is part of this database?
					$found = strstr(mysql_tablename($result, $i), $prefix);	//  will create a non-zero value if tablename found

					if ($found) {
						$valid = "1";
					}
				}
				mysql_free_result($result);
				$mysql_table_prefix = $prefix;	  //  replace the tablesuffix
			} else {
				$mysql_table_prefix = $mysql_table_prefix2;
			}
			if ($valid) {
				$db_slv = '2';   // active db
				$res2 = fresh_media($query, $q1, $domain_qry, $mysql_table_prefix, $catid);
				$res = array_merge($res, $res2);
			}
		}

		if ($db3_slv == 1) {
			$db_con = db3_connect() ;
			$valid = "1";
			$found = "0";
			if ($prefix > '0' ) {
				$valid = "0";
				$result = mysql_query("SHOW TABLES");
				$num_rows = mysql_num_rows($result);
				for ($i = 0; $i < $num_rows; $i++) {	//  the shifted table-suffix is part of this database?
					$found = strstr(mysql_tablename($result, $i), $prefix);	//  will create a non-zero value if tablename found

					if ($found) {
						$valid = "1";
					}
				}
				mysql_free_result($result);
				$mysql_table_prefix = $prefix;	  //  replace the tablesuffix
			} else {
				$mysql_table_prefix = $mysql_table_prefix3;
			}
			if ($valid) {
				$db_slv = '3';   // active db
				$res3 = fresh_media($query, $q1, $domain_qry, $mysql_table_prefix, $catid);
				$res = array_merge($res, $res3);
			}
		}

		if ($db4_slv == 1) {
			$db_con = db4_connect() ;
			$valid = "1";
			$found = "0";
			if ($prefix > '0' ) {
				$valid = "0";
				$result = mysql_query("SHOW TABLES");
				$num_rows = mysql_num_rows($result);
				for ($i = 0; $i < $num_rows; $i++) {	//  the shifted table-suffix is part of this database?
					$found = strstr(mysql_tablename($result, $i), $prefix);	//  will create a non-zero value if tablename found

					if ($found) {
						$valid = "1";
					}
				}
				mysql_free_result($result);
				$mysql_table_prefix = $prefix;	  //  replace the tablesuffix
			} else {
				$mysql_table_prefix = $mysql_table_prefix4;
			}
			if ($valid) {
				$db_slv = '4';   // active db
				$res4 = fresh_media($query, $q1, $domain_qry, $mysql_table_prefix, $catid);
				$res = array_merge($res, $res4);
			}
		}

		if ($db5_slv == 1) {
			$db_con = db5_connect() ;
			$valid = "1";
			$found = "0";
			if ($prefix > '0' ) {
				$valid = "0";
				$result = mysql_query("SHOW TABLES");
				$num_rows = mysql_num_rows($result);
				for ($i = 0; $i < $num_rows; $i++) {	//  the shifted table-suffix is part of this database?
					$found = strstr(mysql_tablename($result, $i), $prefix);	//  will create a non-zero value if tablename found

					if ($found) {
						$valid = "1";
					}
				}
				mysql_free_result($result);
				$mysql_table_prefix = $prefix;
			} else {
				$mysql_table_prefix = $mysql_table_prefix5;
			}
			if ($valid) {
				$db_slv = '5';   // active db
				$res5 = fresh_media($query, $q1, $domain_qry, $mysql_table_prefix, $catid);
				$res = array_merge($res, $res5);
			}
		}
		reactivate_dbuact($prefix);
//echo "\r\n\r\n<br>res array all:<br><pre>";print_r($res);echo "</pre>";
		if (!is_array($res)) {
		   $msg = str_replace ('%query', htmlentities(utf8_decode($query)), $sph_messages["noMediaMatch"]);

			echo "<div class='mainlist'>
					<div class='warnadmin cntr'>$msg</div>
				</div>
			";
		}
		return $res;
	}

	public function fresh_media($query, $q1, $domain_qry, $mysql_table_prefix, $catid){
		global  $case_sensitive, $debug, $category, $search_id3;

		$all_media = array();
		
		if ($search_id3 == '1') {	// search also in EXIF and ID3 info	
			if ($case_sensitive =='0') {
				$result = mysql_query("select * from ".$mysql_table_prefix."media
										where (LOWER(title) like LOWER('%".(Connection::getInstance() -> escape_string($q1))."%')) $domain_qry
										OR (LOWER(id3) like LOWER('%".(Connection::getInstance() -> escape_string($q1))."%')) $domain_qry
									   order by title, id3 ");
			} else {
				//  distinct results for UTF-8
				$result = mysql_query("select * from ".$mysql_table_prefix."media
										where title like '%".(Connection::getInstance() -> escape_string($q1))."%'
										order by title, id3 ");
			}
		} else {	//  search only in media title
			if ($case_sensitive =='0') {
				$result = mysql_query("select * from ".$mysql_table_prefix."media
									   where (LOWER(title) like LOWER('%".(Connection::getInstance() -> escape_string($q1))."%')) $domain_qry
									   order by title, id3 ");
			} else {
				//  distinct results for UTF-8
				$result = mysql_query("select * from ".$mysql_table_prefix."media
										where title like '%".(Connection::getInstance() -> escape_string($q1))."%'
										or id3 like '%".(Connection::getInstance() -> escape_string($q1))."%'
										order by title, id3 ");
			}		
		}
		
		if ($debug > '0') echo mysql_error();

		//	  if query did not match any media object
		if (mysql_num_rows($result) == 0){
			return $all_media;  //	  return blank array, otherwise array_merge() will not work in PHP5
		}

		//  collect all results
		while ($row = mysql_fetch_row($result)) {
			$all_media[] = $row;
		}

		$fresh_media = array();
		//  if necessary, reduce to category valid links
		if ($category != '-1') {
			//  get name of current category
			$result =  mysql_query("select category from ".$mysql_table_prefix."categories
										where category_id ='$catid'");
			$row = mysql_fetch_row($result);	//	  contains category name
			while (list($key, $value) = each($all_media)) {
				//  get site_id corresponding to this page
				$result =  mysql_query("select site_id from ".$mysql_table_prefix."links
											where url = '$value[2]'");
				if ($debug > '0') echo mysql_error();

				$site_id = mysql_fetch_row($result);

				//  check for valid catid
				$result =  mysql_query("select * from ".$mysql_table_prefix."site_category
											where site_id = '$site_id[0]' AND category_id ='$catid'");
				if ($debug > '0') echo mysql_error();

				//  add valid link to result array
				if (mysql_num_rows($result) == '1' ) {
					$fresh_media[] = $value;
				}
			}
		} else {
			$fresh_media = $all_media;	//  no category search
		}
//echo "\r\n\r\n<br>fresh_media array:<br><pre>";print_r($fresh_media);echo "</pre>";
		return $fresh_media;
	}

	public function reactivate_dbuact($prefix) {
		global $dbu_act, $db1_slv, $db2_slv, $db3_slv, $db4_slv, $db5_slv;
		global $database1, $database2, $database3, $database4, $database5;
		global $mysql_table_prefix1, $mysql_table_prefix2, $mysql_table_prefix3, $mysql_table_prefix4, $mysql_table_prefix5;

		//	  re-active default db for 'Search User'
		if ($dbu_act == '1') {
			$db_con	 = db1_connect() ;
			if ($prefix > '0' ) {
				$mysql_table_prefix = $prefix;
			} else {
				$mysql_table_prefix = $mysql_table_prefix1;
			}
		}

		if ($dbu_act == '2') {

			$db_con = db2_connect() ;
			if ($prefix > '0' ) {
				$mysql_table_prefix = $prefix;
			} else {
				$mysql_table_prefix = $mysql_table_prefix2;
			}
		}

		if ($dbu_act == '3') {
			$db_con = db3_connect() ;
			if ($prefix > '0' ) {
				$mysql_table_prefix = $prefix;
			} else {
				$mysql_table_prefix = $mysql_table_prefix3;
			}
		}

		if ($dbu_act == '4') {
			$db_con = db4_connect() ;
			if ($prefix > '0' ) {
				$mysql_table_prefix = $prefix;
			} else {
				$mysql_table_prefix = $mysql_table_prefix4;
			}
		}

		if ($dbu_act == '5') {
			$db_con = db5_connect() ;
			if ($prefix > '0' ) {
				$mysql_table_prefix = $prefix;
			} else {
				$mysql_table_prefix = $mysql_table_prefix5;
			}
		}
		return ;
	}

}