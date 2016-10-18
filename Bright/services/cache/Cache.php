<?php
/**
 * This class handles the serverside caching of bright CMS
 * Version history
 * 2.3 20130129
 * - Added forceCache for testing purposes
 * 2.2 20120912
 * - .htaccess is created if non-existant, cache folder is NOT READABLE ANYMORE!
 * 2.1 20120723
 * - Added deleteCacheByPrefix
 * @version 2.3
 * @author Fur
 * @package Bright
 * @subpackage cache
 */
class Cache extends Permissions {

	private $cache;
	private $_forceCache;

	function __construct($forceCache = false) {
		parent::__construct();

		$this -> _forceCache = $forceCache;
		if(CACHE_MODE == BaseConstants::CACHE_MODE_APC) {
			$this -> cache = new \Bright\services\cache\APCCache();

		} else{
			$this -> cache = new \Bright\services\cache\FileCache();

		}
	}
	
	/**
	 * Caches a string on the server
	 * @param string value The string to cache
	 * @param string name The name of the cached file
	 * @param int expdate The UNIX-timestamp of it's expiration date
	 * @param mixed header Additional headers
	 * @return boolean True when successful, otherwise false
	 */
	public function setCache($value, $name, $expdate, $headers = null) {
		if($expdate <= time() || (!LIVESERVER && !$this -> _forceCache))
			return true;
		return $this -> cache -> setCache($value, $name, $expdate, $headers);
	}

	/**
	 * Deletes a cached file on the server<br/>
	 * Required permissions:<br/>
	 * <ul>
	 * <li>IS_AUTH</li>
	 * </ul>
	 * @param string name The name of the cached files
	 * @return bool
	 */
	public function deleteCache($name) {
		return $this -> cache -> deleteCache($name);
	}
	
	/**
	 * Deletes all the cached files where Page '$label' is in the path<br/>
	 */
	public function deleteCacheByLabel($label) {
		if(count(explode('/', $label)) > 1 || count(explode('\\', $label)) > 1)
			throw $this -> throwException(2001);

		return $this -> cache -> deleteCacheByLabel($label);

	}

	/**
	 * Removes all the cached files starting with $prefix
	 * @since 2.1
	 * @param string $prefix The prefix
	 * @throws Exception
	 * @return void
	 */
	public function deleteCacheByPrefix($prefix) {		
		$prefix = filter_var($prefix, FILTER_SANITIZE_STRING);

		if(count(explode('/', $prefix)) > 1 || count(explode('\\', $prefix)) > 1)
			throw $this -> throwException(2001);

		return $this -> cache -> deleteCacheByPrefix($prefix);
	}
	/**
	 * Deletes all the cached files<br/>
	 * Required permissions:<br/>
	 */
	public function flushCache() {
		return $this -> cache -> flushCache();
	}
	
	/**
	 * Gets a cached file by it's name<br/>
	 * @param string name The name of the cached file
	 * @return mixed The cached string, or false when not found
	 */
	public function getCache($name) {
		
		if(!LIVESERVER && !$this -> _forceCache) // We don't cache while developing!
			return false;
		$result = $this -> cache -> getCache($name);
//		$found = $result !== null;
//		Connection::getInstance() -> addTolog("Getting cache for $name, found something? $found");
		return $result;
	}
	
}