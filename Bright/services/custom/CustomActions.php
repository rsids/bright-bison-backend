<?php

/**
 * Handles all the actions customized for a specific project.
 * @author bs10
 * @version 1.0
 * @package Bright
 * @subpackage custom
 */
class CustomActions extends Permissions {

	function __construct() {
		parent::__construct();	
		$this -> _conn = Connection::getInstance();
	}
	
	/**
	 * @var StdClass A reference to the Connection instance
	 */
	private $_conn;

    /**
     * Calls a custom action
     * @param string $class The class the method is defined in
     * @param string $action The method to call
     * @param array $arguments An array of arguments to pass to the method
     * @return mixed The result of the custom action
     * @throws Exception
     */
	public function callAction($class, $action, $arguments = null) {
		if(!is_file(BASEPATH . 'bright/site/actions/' . $class . '.php'))
			throw $this -> throwException(9001, array($class));
			
		include_once(BASEPATH . 'bright/site/actions/' . $class . '.php');
		
		$custom = new $class;
		
		if(!method_exists($custom, $action))
			throw $this -> throwException(9002, array($action, $class));
		
		if($arguments) {
			if(!is_array($arguments)) {
				$arguments = array($arguments);
			}
			return call_user_func_array(array($custom, $action), $arguments);
		}
		
		return call_user_func(array($custom, $action));
	}
}