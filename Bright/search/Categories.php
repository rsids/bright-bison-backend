<?php
require_once(dirname(__FILE__) . '/Settings.php');
require_once(dirname(__FILE__) . '/BaseSearch.php');

class Categories extends BaseSearch {

	private $_s;

	function __construct() {
		$this -> _s = Settings::getInstance();
	}

    public function get_categories_view() {
    	$categories['main_list'] = Connection::getInstance() -> getRowsIndexedArray('SELECT * FROM '.$this -> _s -> mysql_table_prefix.'categories WHERE parent_num=0 ORDER BY category');

    	if (is_array($categories['main_list'])) {
    		foreach ($categories['main_list'] as $_key => $_val) {
    			$categories['main_list'][$_key]['sub'] =  Connection::getInstance() -> getRowsIndexedArray('SELECT * FROM '.$this -> _s -> mysql_table_prefix.'categories WHERE parent_num='.$_val['category_id']);
    		}
    	}
    	return $categories;
    }

    public function get_category_info($catid) {

    	$categories['main_list'] = Connection::getInstance() -> getRowsIndexedArray("SELECT * FROM ".$this -> _s -> mysql_table_prefix."categories ORDER BY category");

    	if (is_array($categories['main_list'])) {
    		foreach($categories['main_list'] as $_val) {
    			$categories['categories'][$_val['category_id']] = $_val;
    			$categories['subcats'][$_val['parent_num']][] = $_val;
    		}
    	}

    	/* count sites */
    	if (array_key_exists($catid, $categories['subcats'])) {
	    	$categories['subcats'] = $categories['subcats'][$catid];
    		foreach ($categories['subcats'] as $_key => $_val) {
    			$categories['subcats'][$_key]['count'] = Connection::getInstance() -> getRowsIndexedArray('SELECT count(*) FROM '.$this -> _s -> mysql_table_prefix.'site_category WHERE 	category_id='.(int)$_val['category_id']);
    		}
    	}

    	/* make tree */
    	$_parent = $catid;
    	while ($_parent) {
    		$categories['cat_tree'][] = $categories['categories'][$_parent];
    		$_parent = $categories['categories'][$_parent]['parent_num'];
    	}
    	$categories['cat_tree'] = array_reverse($categories['cat_tree']);


    	/* list category sites */
    	$categories['cat_sites'] = Connection::getInstance() -> getRowsIndexedArray('SELECT url, title, short_desc FROM '.$this -> _s -> mysql_table_prefix.'sites, '.$this -> _s -> mysql_table_prefix.'site_category WHERE category_id='.$catid.' AND '.$this -> _s -> mysql_table_prefix.'sites.site_id='.$this -> _s -> mysql_table_prefix.'site_category.site_id order by title');

        $count = '0';
        if ($categories['cat_sites'] != '') {
            foreach ($categories['cat_sites'] as $value) {
                $mytitle = $categories['cat_sites'][$count][1];     // try to fetch title as defined in admin settings for each site

                if ($mytitle == '') {   //  if no personal title is available, try to take title and description from HTML header

                    $thisurl =  ($categories['cat_sites'][$count][0]);

            		$result = mysql_query("select * from ".$this -> _s -> mysql_table_prefix."links where url like '$thisurl%'");
             		if ($this -> _s -> debug > '0') echo mysql_error();
            		$num_rows = mysql_num_rows($result);

            		if ($num_rows > 0) {    //      hopefully the webmaster included some title and description into the site header
            			$thisrow = mysql_fetch_array($result);

            			$thistitle = $thisrow[3];
                        if ($thistitle == '' ) {   //   if no HTML title available, alternative output
                            $thistitle = "No title available for this site.";
                        }

                     	$thisdescr = $thisrow[4];
                        if ($thisdescr == '' ) {   //   if no HTML description available, alternative output
                            $thisdescr = "No description available for this site.";
                        }

                        //      now include HTML title and description into array, so we may output them
                        $categories['cat_sites'][$count][1] = $thistitle;
                        $categories['cat_sites'][$count]['title'] = $thistitle;
                        $categories['cat_sites'][$count][2] = $thisdescr;
                        $categories['cat_sites'][$count]['short_desc'] = $thisdescr;
                    }
                }
                $count++;
            }
        }
    	return $categories;
    }

    public function findcats($url, $category, $catidx, $mysql_table_prefix) {
    	$allcats = array ();
    	$catlist = array ();
        $host = parse_url($this -> blank_url($url));
        $hostname = $host['host'];
		$host1 = '';
        //  rebuild domain for localhost applications
        if ($hostname == 'localhost') {
            $host1 = str_replace($this -> _s -> local,'',$url);
        }
        $pos = strpos($host1, "/");         //      on local server delete all behind the /
        if ($pos) {
            $host1 = substr($host1,0,$pos); //      build full adress again, now only local domain
        }
        if ($hostname == 'localhost') {
            $url = ("".$this -> _s -> local."".$host1."/");
        }else {
            $url = ("{$host['scheme']}://{$hostname}/");
        }
        $paths = explode('/', $host['path']);
        $found = false;
        $site_id = -1;
        //  find according site_id
        while(!$found && count($paths) > 0) {
        	$purl = $url . "/" . implode('/', $paths);
        	$purl = preg_replace('#/+#', '/', $purl);
        	$purl = str_replace('http:/', 'http://', $purl);
        	$result = Connection::getInstance()-> getField("select site_id from {$mysql_table_prefix}sites where url like '$purl%'");
        	if(!$result) {
        		array_pop($paths);
        	} else {
        		$found = true;
        		$site_id = $result;
        	}
        }
        if(!$found) {
        	$result = mysql_query("select site_id from ".$mysql_table_prefix."sites where url like '$url%'");
            $row = mysql_fetch_row($result);
    	    $site_id = $row[0];
        }

        //  find cat_id for this domain
        $result = mysql_query("select * from ".$mysql_table_prefix."site_category where site_id like '$site_id'");
        if ($this -> _s -> debug > '0') echo mysql_error();
        $rows = mysql_num_rows($result);

        //  find category names
        if (mysql_num_rows($result)>0) {
            while ($row = mysql_fetch_row($result)) {
                if ($category == '-1') {  //  find all categories according to this domain
                $res = mysql_query("select category from ".$mysql_table_prefix."categories where category_id like '$row[1]'");
                } else {
                    $res = mysql_query("select category from ".$mysql_table_prefix."categories where parent_num = '$catidx'");   //  find only sub-categories
                    //$res = mysql_query("select category from ".$mysql_table_prefix."categories where category_id = '$row[1]' OR parent_num = '$catidx'");   //  find all categories
                }
                if ($this -> _s -> debug > '0') echo mysql_error();
                $cat = mysql_fetch_row($res);
                $allcats[] = $cat[0];     //  collect all categories
            }
        }
        $catlist = array_unique($allcats);
        sort($catlist);
        return $catlist;
    }

    /**
     * Removes the parameters from an url
     * @param unknown_type $url
     */
    public function blank_url($url) {
    	$url = str_replace("&amp;", "&", $url);
    	$url = str_replace(" ", "%20", $url);
    	return $url;
    }

}