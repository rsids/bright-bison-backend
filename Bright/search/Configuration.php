<?php
/**
 * Holds values from textfiles, like common words e.d.
 * @author Ids
 *
 */
class Configuration  {
	
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
	
	
	// Count of successfully created databases
	public $db_count = "1";
	
	// Currently activated Admin database
	public $dba_act = "1";
	
	// Currently activated Search User database
	public $dbu_act = "1";
	
	// Currently activated Suggest URL User database
	public $dbs_act = "1";
	
	// Activated databases that should deliver search results
	public $db1_slv = "1";
	public $db2_slv = "0";
	public $db3_slv = "0";
	public $db4_slv = "0";
	public $db5_slv = "0";
	
	
	
	/*********************** 
	Database 1 settings
	***********************/
	// Name of database
	public $database1 = DB_DATABASE;
	
	// MySQL User
	public $mysql_user1 = DB_USER;
	
	// MySQL Password
	public $mysql_password1 = DB_PASSWORD;
	
	// MySQL Host
	public $mysql_host1 = DB_HOST;
	
	// Prefix for tables
	public $mysql_table_prefix1 = "index_";
	
	// Status of database
	public $db1_set = "1";
	
	// Activation status
	public $db1_act = "";
	
	
	/*********************** 
	Database 2 settings
	***********************/
	
	// Name of database
	public $database2 = "";
	
	// MySQL User
	public $mysql_user2 = "";
	
	// MySQL Password
	public $mysql_password2 = "";
	
	// MySQL Host
	public $mysql_host2 = "";
	
	// Prefix for tables
	public $mysql_table_prefix2 = "";
	
	// Status of database
	public $db2_set = "0";
	
	// Activation status
	public $db2_act = "";
	
	
	/*********************** 
	Database 3 settings
	***********************/
	
	// Name of database
	public $database3 = "";
	
	// MySQL User
	public $mysql_user3 = "";
	
	// MySQL Password
	public $mysql_password3 = "";
	
	// MySQL Host
	public $mysql_host3 = "";
	
	// Prefix for tables
	public $mysql_table_prefix3 = "";
	
	// Status of database
	public $db3_set = "0";
	
	// Activation status
	public $db3_act = "";
	
	
	/*********************** 
	Database 4 settings
	***********************/
	
	// Name of database
	public $database4 = "";
	
	// MySQL User
	public $mysql_user4 = "";
	
	// MySQL Password
	public $mysql_password4 = "";
	
	// MySQL Host
	public $mysql_host4 = "";
	
	// Prefix for tables
	public $mysql_table_prefix4 = "";
	
	// Status of database
	public $db4_set = "0";
	
	// Activation status
	public $db4_act = "";
	
	
	/*********************** 
	Database 5 settings
	***********************/
	
	// Name of database
	public $database5 = "";
	
	// MySQL User
	public $mysql_user5 = "";
	
	// MySQL Password
	public $mysql_password5 = "";
	
	// MySQL Host
	public $mysql_host5 = "";
	
	// Prefix for tables
	public $mysql_table_prefix5 = "";
	
	// Status of database
	public $db5_set = "0";
	
	// Activation status
	public $db5_act = "";
	
	
	public function getTablePrefix($type = 'admin') {
		if($type == 'admin') {
			return $this -> {'mysql_table_prefix' . $this -> dba_act};
		}
		return $this -> {'mysql_table_prefix' . $this -> dbu_act};
	}
}