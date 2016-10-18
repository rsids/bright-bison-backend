<?php
class IDSHandler {
	private $_include_dir;
	
	function __construct() {
		if(!is_dir(BASEPATH . "bright/cache/ids/tmp")) {
			@mkdir(BASEPATH . "bright/cache/ids/tmp", 0777, true);
		}
		$this -> _include_dir = BASEPATH . 'bright/externallibs/sphider/include/';
		set_include_path(
	        get_include_path()
	        . PATH_SEPARATOR
	        . $this -> _include_dir
	    );
	
	    if (!session_id()) {
	        session_start();
	    }
	
	    require_once 'IDS/Init.php';
	}
	
	public function setupIds() {
		
	    $result = '';
	    try {
	    
	        //  define what to scan
	        $request = array(
	            'REQUEST' => $_REQUEST,
	            'GET' => $_GET,
	            'POST' => $_POST,
	            'COOKIE' => $_COOKIE
	        );
	
	        // Initiate the IDS and fetch the results        
	        $init = IDS_Init::init($this -> _include_dir ."IDS/Config/Config.ini.php");
	        
	        $init->config['General']['base_path'] = $this -> _include_dir . "IDS/";
	        $init->config['General']['use_base_path'] = true;
	        $init->config['Caching']['caching'] = true;
	
	        $ids = new IDS_Monitor($request, $init);
	        $result = $ids->run();
	
	        if (!$result->isEmpty()) {
	            //  prepare the log file
	            require_once 'IDS/Log/File.php';
	            //require_once 'IDS/Log/Email.php';
	            require_once 'IDS/Log/Composite.php';
	
	            $compositeLog = new IDS_Log_Composite();
	            $compositeLog->addLogger(IDS_Log_File::getInstance($init));            
	            //$compositeLog->addLogger(IDS_Log_File::getInstance($init),IDS_Log_Email::getInstance($init));           
	            $compositeLog->execute($result);
	        }
	        
	    } catch (Exception $e) {
	        //  if the IDS init went wrong
	        printf(
	            'An internal error occured in the \'Intrusion Detection System\': %s',
	            $e->getMessage()
	        );
	        die ();
	    }
	    return $result;
	}
	
	public function checkBlockedIDS() {
		if (Settings::getInstance() -> ids_blocked == 1) {
			$blocked = false;
			if ( isset ( $_SERVER['REMOTE_ADDR'] ) ) {      //  get actual IP from user
				$new_ip = htmlspecialchars($_SERVER['REMOTE_ADDR']);
				$handle = @fopen (BASEPATH . "bright/cache/ids/tmp/phpids_log.txt","r");
				if ($handle) {      //      read IDS log-file
					$lines = @file(BASEPATH . "bright/cache/ids/tmp/phpids_log.txt");
					@fclose($handle);
				
					foreach ($lines as $thisline) {                             //  analyze all stored intrusion attempts
						preg_match("@\"(.*?)\",(.*?),(.*?),@",$thisline, $regs);
						if ($new_ip == $regs[1] && $regs[3] >= Settings::getInstance() -> ids_stop) {     //  if actual IP is known to be eval and impact was significant
							$blocked = true;
						}
					}
				}
				if ($blocked === true) {
					throw new IDSException(IDSException::$IDSBLACKLIST);
				}
			}
		}
	}
}

class IDSException extends Exception {
	public static $IDSBLOCKED = 1;
	public static $IDSBLACKLIST = 2;
	
	private $_msg = array(	1 => "IDS result message\r\nFurther input blocked by the Sphider-plus supervisor, because the Intrusion Detection System noticed the above attempt to attack this search engine.", 
							2 => "IDS message: known eval IP due to former attacks\r\n
								Further access blocked by the Sphider-plus supervisor, because the Intrusion Detection System already noticed an attempt to attack this search engine.");
	
	function __construct ($code = 0 , $severity = 1 , $filename = __FILE__ , $lineno = __LINE__ , Exception $previous = NULL ) {
		parent::__construct($this -> msg[$code], $code, $severity, $filename, $lineno, $previous);
	}
	
}