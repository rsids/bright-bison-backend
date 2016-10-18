<?php

require_once('BaseSearch.php');

/**
 * Basic settings for the search
 * @author Ids
 *
 */
class Settings  {
	
	function __construct() {
		$this -> commondir = dirname(__FILE__ ) . '/common/';
		$this -> stemdir = dirname(__FILE__ ) . '/stemming/';
		$this -> imagedir = dirname(__FILE__ ) . '/images/';
		$this -> textcachedir = dirname(__FILE__ ) . '/textcache/';
		$this -> templatedir = dirname(__FILE__ ) . '/templates/';
		$this -> mediacachedir = dirname(__FILE__ ) . '/mediacache/';
		$this -> xml = dirname(__FILE__ ) . '/xml/';
		
		$this -> log_dir = BASEPATH . 'bright/cache/sphider/log/';
	
	}
	
	static private $instance;
	
	/**
	 * Gets a single instance of the connection class
	 * @static
	 * @return StdClass An instance of the connction class
	 */
	public static function getInstance(){
		if(!isset(self::$instance)){
			$object= __CLASS__;
			self::$instance= new $object;
		}
		return self::$instance;
	}

	
	public $mysql_table_prefix = 'index_';
	
	
	/*********************** 
	 * Fur Added Settings
	********************* **/
	/**
	 * @var array list of common words
	 */
	public $common = array();
	/**
	 * 
	 * @var string The directory holding the template files
	 */
	public $templatedir; 
	/**
	 * 
	 * @var string The directory holding the common words files
	 */
	public $commondir; 
	/**
	 * 
	 * @var string The directory holding the stem files
	 */
	public $stemdir; 
	/**
	 * 
	 * @var string The directory holding the text cache files
	 */
	public $textcachedir; 
	/**
	 * 
	 * @var string The directory holding the image cache files
	 */
	public $imagecachedir; 
	/**
	 * 
	 * @var string The directory holding the media cache files
	 */
	public $mediacachedir; 
	/**
	 * 
	 * @var string The directory holding the xml files
	 */
	public $xmldir; 
	
	/**
	 * 
	 * @var string Defines the output method, set to 'xml' for XML output
	 */
	public $out = '';
	
	/**
	 * 
	 * @var string Defines the name of the xml files
	 * @default results.xml
	 */
	public $xmlname = 'results.xml';

	public $user_db;
	/*********************** 
	General settings 
	********************* **/
	
	/**
	 * @var string  Sphider-plus version 
	 **/
	public $plus_nr = '2.7';
	
	/**
	 * @var string  Original Sphider version 
	 **/
	public $version_nr = '1.3.5';
	
	/**
	 * @var string  IDS impact warn level
	 **/
	public $ids_warn = '14';
	
	/**
	 * @var string  IDS impact stop traffic level
	 **/
	public $ids_stop = '25';
	
	/**
	 * @var string Standard charset of your location (e.g. ISO-8859-1)
	 **/
	public $home_charset = 'utf-8';
	
	/**
	 * @var string Administrators email address (logs and info emails will be sent there)
	 **/
	public $admin_email = 'admin@localhost';
	
	/**
	 * @var string Dispatcher email address (info emails will be sent from this account)
	 **/
	public $dispatch_email = 'postmaster@localhost';
	
	/**
	 * @var string Address to localhost document root 
	 **/
	public $local = 'http://localhost/publizieren/';
	
	/**
	 * @var int  Show complete list of URLs during import and export
	 **/
	public $show_url = 1;
	
	/**
	 * @var int  Default for number of sites per page in Admin backend
	 **/
	public $sites_per_page = 20;
	
	/**
	 * @var int  Sort Sites table in Admin section in alphabetic order
	 **/
	public $sites_alpha = 1;
	
	/**
	 * @var int  Sort Sites table in Admin section by indexdate, latest on top
	 **/
	public $sites_latest = 0;
	
	/**
	 * @var int  Sort Sites table in Admin section by indexdate, oldest on top
	 **/
	public $sites_oldest = 0;
	
	/**
	 * @var int  Sort Sites table in Admin section by title
	 **/
	public $sites_title = 0;
	
	/**
	 * @var int   MySQL query cache
	 **/
	public $qcache = 1;
	
	/**
	 * @var int   Don't erase URLs containing 'Must Not include' words
	 **/
	public $not_erase = 1;
	
	/**
	 * @var int   .htaccess protection for admin folder
	 **/
	public $htaccess = 0;
	
	/**
	 * @var int  Admin debug mode - Show details from index / re-index procedure, MySQL and PHP error messages
	 **/
	public $debug = 2;
	
	/**
	 * @var int  User debug mode - Show details concerning database and cache activity
	 **/
	public $debug_user = 0;
	
	/**
	 * @var int  Use 'Intrusion Detection System'
	 **/
	public $use_ids = 1;
	
	/**
	 * @var int  Block Internet traffic of IP's detected by 'Intrusion Detection System'
	 **/
	public $ids_blocked = 1;
	
	/**
	 * @var int  Free resources when indexing large amount of URLs 
	 **/
	public $clear = 0;
	
	/**
	 * @var int  Reset query log during erase and index procedures 
	 **/
	public $clear_query = 1;
	
	/**
	 * @var int  Remember new domains found during index procedure 
	 **/
	public $auto_add = 1;
	
	/**
	 * @var int  Delete related keywords and links after site delete 
	 **/
	public $del_related = 1;
	
	/**
	 * @var string  Template directory
	 **/
	public $templ_dir = 'templates';
	
	/**
	 * @var string  Temporary directory, this should be readable and writable
	 **/
	public $tmp_dir = 'tmp';
	
	/**
	 * @var string  Name of search script
	 **/
	public $search_script = 'search.php';
	
	/**
	 * @var string  Embedded application of Sphider-plus HTML code 
	 **/
	public $embedded = '';
	
	/**
	 * @var string  Operating System
	 **/
	public $op_system = 'win';
	
	/**
	 * @var int  Availability of cURL library 
	 **/
	public $curl = '1';
	
	
	/*********************** 
	Logging settings 
	********************* **/
	
	/**
	 * @var int   Disable output of logging data during index / re-index 
	 **/
	public $no_log = 0;
	
	/**
	 * @var int   Enable real-time output of logging data 
	 **/
	public $real_log = 0;
	
	/**
	 * @var int   Interval for real-time Log file update [seconds]
	 **/
	public $refresh = 5;
	
	/**
	 * @var int   Interval until next click will be accepted to increase popularity of a link [seconds]
	 **/
	public $click_wait = 60;
	
	/**
	 * @var int  Should log files be kept
	 **/
	public $keep_log = 1;
	
	/**
	 * @var string Log directory, this should be readable and writable
	 **/
	public $log_dir = 'log';
	
	/**
	 * @var string  Log format
	 **/
	public $log_format = 'html';
	
	/**
	 * @var int   Send log file by email 
	 **/
	public $email_log = 0;
	
	
	/*********************** 
	Spider settings 
	********************* **/
	
	/**
	 * @var int  Separate between upper- and lower-case queries
	 **/
	public $case_sensitive   = 0;
	
	/**
	 * @var string  Sitemap directory, this should be readable and writable 
	 **/
	public $smap_dir = 'sitemaps';
	
	/**
	 * @var string  Count of threads for indexing 
	 **/
	public $multi_indexer = '1';
	
	/**
	 * @var string  Max. links to be followed per site 
	 **/
	public $max_links = '9999';
	
	/**
	 * @var int  Min words per page required for indexing 
	 **/
	public $min_words_per_page = 5;
	
	/**
	 * @var int  Words shorter than this will not be indexed
	 **/
	public $min_word_length = 3;
	
	/**
	 * @var int  Keyword weight depending on the number of times it appears in a page is capped at this value
	 **/
	public $word_upper_bound = 100;
	
	/**
	 * @var int  If available follow 'sitemap.xml'
	 **/
	public $follow_sitemap		= 1;
	
	/**
	 * @var int  Create sitemap.xml file of each indexed site
	 **/
	public $create_sitemap		= 0;
	
	/**
	 * @var int  Sitemap name unique for all .xml files 
	 **/
	public $smap_unique = '0';
	
	/**
	 * @var int  Index numbers as well
	 **/
	public $index_numbers = 1;
	
	/**
	 * if this value is set to 1, word in domain name and url path are also indexed,
	 * so that for example the index of www.php.net returns a positive answer to query 'php' even
	 * if the word is not included in the page itself.
	 * @var int  
	 **/
	public $index_host = 0;
	
	
	/**
	 * @var int  Whether to index the Meta tag: Keywords
	 **/
	public $index_meta_keywords = 1;
	
	/**
	 * @var int  Whether to index the Meta tag: Description
	 **/
	public $index_meta_description = 1;
	
	/**
	 * @var int  Do not index the full text
	 **/
	public $ignore_fulltxt = 0;
	
	/**
	 * @var int  Index PDF files
	 **/
	public $index_pdf = 1;
	
	/**
	 * @var int  Index DOC files
	 **/
	public $index_doc = 0;
	
	/**
	 * @var int  Index RTF files
	 **/
	public $index_rtf = 0;
	
	/**
	 * @var int  Index XLS files
	 **/
	public $index_xls = 0;
	
	/**
	 * @var int  Index CSV files
	 **/
	public $index_csv = 0;
	
	/**
	 * @var int  Index PPT files
	 **/
	public $index_ppt = 0;
	
	/**
	 * @var int  Index ODS files
	 **/
	public $index_ods = 0;
	
	/**
	 * @var int  Index ODT files
	 **/
	public $index_odt = 0;
	
	/**
	 * @var string Path to PDF converter
	 **/
	public $pdftotext_path = '..\converter\pdftotext';
	
	/**
	 * @var string Path to DOC converter
	 **/
	public $catdoc_path = '..\converter\catdoc.exe';
	
	/**
	 * @var string Path to PPT converter
	 **/
	public $catppt_path = '..\converter\catppt.exe';
	
	/**
	 * @var string Multibyte extention of PHP
	 **/
	public $mb = '1';
	
	/**
	 * @var string  Index media files (general selection)
	 **/
	public $index_media = '0';
	
	/**
	 * @var string  Index image files 
	 **/
	public $index_image = '0';
	
	/**
	 * @var string  Minimum size for image files (width)
	 **/
	public $min_image_x = '50';
	
	/**
	 * @var string  Minimum size for image files (height)
	 **/
	public $min_image_y = '50';
	
	/**
	 * @var string  Create thmbnails as *.gif 
	 **/
	public $thumb_gif = '1';
	
	/**
	 * @var string  Index audio files 
	 **/
	public $index_audio = '0';
	
	/**
	 * @var string  Index video files 
	 **/
	public $index_video = '0';
	
	/**
	 * @var string  Index embeded media files 
	 **/
	public $index_embeded = '1';
	
	/**
	 * @var string  Index alt tag of images 
	 **/
	public $index_alt = '1';
	
	/**
	 * @var string  Index ID3 tags 
	 **/
	public $index_id3 = '1';
	
	/**
	 * @var string  Index duplicate media on different pages 
	 **/
	public $dup_media = '1';
	
	/**
	 * @var string  Index external hosted media content 
	 **/
	public $ex_media = '1';
	
	/**
	 * @var string  Index RSS and Atom feeds 
	 **/
	public $index_rss = '1';
	
	/**
	 * @var string  Follow CDATA tags in feeds 
	 **/
	public $cdata = '1';
	
	/**
	 * @var string  Index Dublin Core tags in RDF feeds 
	 **/
	public $dc = '1';
	
	/**
	 * @var string  Follow PREFERRED directive in RSD feeds 
	 **/
	public $preferred = '1';
	
	/**
	 * @var string  Index RAR files and archives 
	 **/
	public $index_rar = '0';
	
	/**
	 * @var string  Index ZIP files and archives 
	 **/
	public $index_zip = '0';
	
	/**
	 * @var string  Index framesets 
	 **/
	public $index_framesets = '1';
	
	/**
	 * @var string  Index iframes 
	 **/
	public $index_iframes = '1';
	
	/**
	 * @var string  Jump to iframe directly 
	 **/
	public $iframe_link = '1';
	
	/**
	 * @var string  Index media conent with respect toframe/iframe position 
	 **/
	public $abslinks = '0';
	
	/**
	 * @var string  Time interval for auto re-index 
	 **/
	public $interval = 'never';
	
	/**
	 * @var string  Auto re-index counter 
	 **/
	public $intv_count = '9999';
	
	/**
	 * @var string  Language of 'common word' list 
	 **/
	public $use_common = 'none';
	
	/**
	 * @var string  Use any word in whitelist to enable page indexing
	 **/
	public $use_white1 = '0';
	
	/**
	 * @var string  Use all words of whitelist to enable page indexing
	 **/
	public $use_white2 = '0';
	
	/**
	 * @var string  Use blacklist to prevent page indexing
	 **/
	public $use_black = '1';
	
	/**
	 * @var string  Use blacklist to delete query input
	 **/
	public $kill_query = '1';
	
	/**
	 * @var string  Use div-list to ignore some divs
	 **/
	public $not_divs = '0';
	
	/**
	 * @var string  Use div-list to index only div content
	 **/
	public $use_divs = '0';
	
	/**
	 * @var string  Delete secondary characters at the beginning and end of words
	 **/
	public $del_secchars = '1';
	
	/**
	 * @var string  Use simple quotes 
	 **/
	public $quotes = '1';
	
	/**
	 * @var string  User agent string 
	 **/
	public $user_agent = 'Sphider-plus';
	
	/**
	 * @var string  Browser http_agent_string 
	 **/
	public $browser_string = '1';
	
	/**
	 * @var int  Minimal delay between page downloads 
	 **/
	public $min_delay = 0;
	
	/**
	 * @var string  Use word stemming for language 
	 **/
	public $stem_words = 'none';
	
	/**
	 * @var int  Strip session ids (PHPSESSID, JSESSIONID, ASPSESSIONID, sid) 
	 **/
	public $strip_sessids = 1;
	
	/**
	 * @var int  Allow other hosts in same domain for all found links, and also ignore www. 
	 **/
	public $other_host = 0;
	
	/**
	 * @var int  For redirected links allow other hosts in same domain, and also ignore www. 
	 **/
	public $redir_host = 1;
	
	/**
	 * @var int  Index only links and their titles 
	 **/
	public $only_links = 0;
	
	/**
	 * @var int  Enable link-check instead of reindex 
	 **/
	public $link_check = 0;
	
	/**
	 * @var int  Enable index and re-index for pages with duplicate content 
	 **/
	public $dup_content = 0;
	
	/**
	 * @var int  Split words into their basic parts, separated at hyphens, dots and commas 
	 **/
	public $div_all = 0;
	
	/**
	 * @var int  Split words into their basic parts, separated only at hyphens 
	 **/
	public $div_hyphen = 1;
	
	/**
	 * @var int  Decode BBcode during index 
	 **/
	public $bb_decode = 0;
	
	/**
	 * @var int  Decode UTF-8 HTML entities during index 
	 **/
	public $ent_decode = 1;
	
	/**
	 * @var int  Support Greek language 
	 **/
	public $greek = 0;
	
	/**
	 * @var int  Support Cyrillic language 
	 **/
	public $cyrillic = 0;
	
	/**
	 * @var int  Perform a segmentation of Chinese text during index 
	 **/
	public $cn_seg = 0;
	
	/**
	 * @var int  Perform a segmentation of Japanese text during index 
	 **/
	public $jp_seg = 0;
	
	/**
	 * @var string  User name1 for password protected pages 
	 **/
	public $user1 = '0';
	
	/**
	 * @var string  Password1 for password protected pages 
	 **/
	public $pwd1 = '0';
	
	/**
	 * @var string  User name2 for password protected pages 
	 **/
	public $user2 = '0';
	
	/**
	 * @var string  Password2 for password protected pages 
	 **/
	public $pwd2 = '0';
	
	/**
	 * @var string  User name3 for password protected pages 
	 **/
	public $user3 = '0';
	
	/**
	 * @var string  Password3 for password protected pages 
	 **/
	public $pwd3 = '0';
	
	
	/*********************** 
	Search settings 
	********************* **/
	
	/**
	 * @var string Language of the search page 
	 **/
	public $language = 'en';
	
	/**
	 * @var string Auto detect client language
	 **/
	public $auto_lng = '1';
	
	/**
	 * @var string Use cache for search results
	 **/
	public $use_cache = '0';
	
	/**
	 * @var string Cache size [MB] for text search results
	 **/
	public $tcache_size = '2';
	
	/**
	 * @var string Cache size [MB] for media search results
	 **/
	public $mcache_size = '2';
	
	/**
	 * @var string Max results/links per query in text cache
	 **/
	public $max_ctresults = '9999';
	
	/**
	 * @var string Max results/links per query in media cache
	 **/
	public $max_cmresults = '9999';
	
	/**
	 * @var string Clear text and media cache during re-index procedure
	 **/
	public $clear_cache = '1';
	
	/**
	 * @var string  Template design/Directory in templates dir
	 **/
	public $template = 'Sphider-plus';
	
	/**
	 * @var string  Title for Results Page
	 **/
	public $mytitle = 'Sphider-plus';
	
	/**
	 * @var string Type of highlighting for found keywords 
	 **/
	public $mark = 'markblue';
	
	/**
	 * @var string  Default for number of results per page
	 **/
	public $results_per_page = 10;
	
	/**
	 * @var int  Can speed up searches on large database (should be 0)
	 **/
	public $bound_search_result = 0;
	
	/**
	 * The length of the description string queried when displaying search results. 
	 * If set to 0 (default), run a query against the whole page text, 
	 * otherwise it queries this many bytes. This can significantly speed up searching on very slow machines 
	 * @var int  
	 **/
	public $length_of_link_desc = 0;
	
	/**
	 * @var int  Number of links shown to next pages
	 **/
	public $links_to_next = 1;
	
	/**
	 * @var int  Show meta description in results page if it exists, otherwise show an extract from the page text.
	 **/
	public $show_meta_description = 0;
	
	/**
	 * @var int  Show warning message if search string was found only in title or url.
	 **/
	public $show_warning = 1;
	
	/**
	 * @var int  Advanced query form, shows and/or buttons
	 **/
	public $advanced_search = 1;
	
	/**
	 * @var int  Query scores are not shown if set to 0
	 **/
	public $show_query_scores = 1;	
	
	/**
	 * @var int  Search without quotes
	 **/
	public $no_quotes= 1;	
	
	/**
	 * @var int  Queries with numbers become wildsearch
	 **/
	public $wild_num= 1;	
	
	/**
	 * @var int  Translitate queries from English characters to Greek 
	 **/
	public $translit_el= 0;	
	
	/**
	 * @var int Accept Greek vowels without accents 
	 **/
	public $noacc_el= 0;	
	
	/**
	 * @var int Display category list
	 */
	public $show_categories = 0;
	
	/**
	 * @var int  Display category selection at each result
	 */
	public $more_catres = 0;
	
	/**
	 * @var int  Max length of page title given in results page
	 **/
	public $title_length		= 80;
	
	/**
	 * @var int  Max length of URL given in results page
	 **/
	public $url_length		= 80;
	
	/**
	 * @var int  Length of page description given in results page
	 **/
	public $desc_length = 250;
	
	/**
	 * @var int  Max hits shown per link in results page
	 **/
	public $max_hits		= 1;
	
	/**
	 * @var int  Show order of result listing as headline
	 **/
	public $show_sort = 0;
	
	/**
	 * @var int  Show media results as well as text results in result page
	 **/
	public $show_media = 0;
	
	/**
	 * @var int  Search for media results also in EXIF and ID3 info
	 **/
	public $search_id3 = 0;
	
	/**
	 * @var int  Show 'Most popular searches' at the bottom of result pages
	 **/
	public $most_pop = 1;
	
	/**
	 * @var int  Suppress zero results in 'Most popular searches'
	 **/
	public $no_zeros = 1;
	
	/**
	 * @var int  Show 3D tag cloud in 'Most popular searches'
	 **/
	public $tag_cloud = 0;
	
	/**
	 * @var int  Show 3D tag cloud in differernt colors for each query hit
	 **/
	public $color_cloud = 0;
	
	/**
	 * @var int  Number of rows for 'Most popular searches'
	 **/
	public $pop_rows = 5;
	
	/**
	 * @var int  Min. relevance level (%) to be shown at result pages
	 **/
	public $relevance = 0;
	
	/**
	 * @var int  Max. quantity of results for result listing
	 **/
	public $max_results = 999;
	
	/**
	 * @var int  Show 'User may suggest a Url' at the bottom of result pages'
	 **/
	public $add_url = 1;
	
	/**
	 * @var int  Use authentification for suggested URLs
	 **/
	public $add_auth = 0;
	
	/**
	 * @var int  Inform about user suggestion by e-mail
	 **/
	public $addurl_info = 1;
	
	/**
	 * @var int  Use Captcha for Addurl-form
	 **/
	public $captcha = 0;
	
	
	/*********************** 
	Suggest framework
	********************* **/
	
	/**
	 * @var int  Enable spelling suggestions (Did you mean...)
	 **/
	public $did_you_mean_enabled = 1;
	
	/**
	 * @varint  Define min. character input for suggestion 
	 **/
	public $min_sug_chars = 1;
	
	/**
	 * @var int  Search for suggestions in query log 
	 **/
	public $suggest_history = 0;
	
	/**
	 * @var int  Search for suggestions in keywords 
	 **/
	public $suggest_keywords = 1;
	
	/**
	 * @var int  Build suggestions also for phrases 
	 **/
	public $suggest_phrases = 1;
	
	/**
	 * @var int  Search for suggestions in id3 tags 
	 **/
	public $suggest_id3 = 0;
	
	/**
	 * @var int  Show number of results in suggestion table 
	 **/
	public $show_hits = 0;
	
	/**
	 * @var int  Limit number of suggestions 
	 **/
	public $suggest_rows = 20;
	
	
	/*********************** 
	Weights and result order
	********************* **/
	
	/**
	 * @var int  Relative weight of a word in the title of a webpage
	 **/
	public $title_weight = 20;
	
	/**
	 * @var int  Relative weight of a word in the domain name
	 **/
	public $domain_weight = 60;
	
	/**
	 * @var int  Relative weight of a word in the path name
	 **/
	public $path_weight = 10;
	
	/**
	 * @var int  Relative weight of a word in meta_keywords
	 **/
	public $meta_weight = 5;
	
	/**
	 * @var int  Defines multiplier for words in main URLs (domains)
	 **/
	public $domain_mul = 1;
	
	/**
	 * @var int  Defines method of chronological order for result listing
	 **/
	public $sort_results = 1;
	
	/**
	 * @var string  Name of promoted domain
	 **/
	public $dompromo = '';
	
	/**
	 * @var string  Name of promoted catchword
	 **/
	public $keypromo = '';
	
	/**
	 * @var string  Max results per domain in result listing
	 **/
	public $dom_count = '3';

}