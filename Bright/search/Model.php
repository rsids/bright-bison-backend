<?php
/**
 * Holds values from textfiles, like common words e.d.
 * @author Ids
 *
 */
class Model  {
	
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
	
	/**
	 * 
	 * @var array Holds localized sentences
	 */
	public $L10N;
	
	/**
	 * @var array  intermediate array fo ignored words
	 **/
	public $all = array(); 
	/**
	 * @var array  array fo ignored words
	 **/
	public $common = array(); 
	/**
	 * @var array  array for ignored file suffixes
	 **/
	public $ext = array(); 
	/**
	 * @var array  array for whitelist
	 **/
	public $whitelist = array(); 
	public $white = array();
	public $white_in = array();
	/**
	 * @var array  array for blacklist
	 **/
	public $blacklist = array(); 
	public $black_in = array();
	public $black = array();
	/**
	 * @var array 	array for image suffixes
	 **/
	public $image = array();	 
	/**
	 * @var array 	array for audio suffixes
	 **/
	public $audio = array();		
	/**
	 * @var array 	array for video suffixes
	 **/
	public $video = array();	 
	/**
	 * @var array  array for divs not to be indexed
	 **/
	public $divs_not = array(); 
	/**
	 * @var array  array for divs to be indexed
	 **/
	public $divs_use = array(); 
	/**
	 * @var array  array of most common Second Level Domains
	 **/
	public $slv = array(); 
}