<?php

/**
 * Base class of the Bright backend. Almost every class extends this class, mostly to check the permissions
 * @author Fur
 * @version 1.7
 * @package Bright
 */
class Permissions
{

    /**
     * @var boolean Indicates whether the user is authenticated
     */
    protected $IS_AUTH = false;
    /**
     * @var boolean Indicates whether the administrator may create, update and delete other administrators
     */
    protected $MANAGE_ADMIN = false;
    /**
     * @var boolean Indicates whether the administrator may create or delete users
     */
    protected $MANAGE_USER = false;
    /**
     * @var boolean When true, a administrator can create new content
     */
    protected $CREATE_PAGE = false;
    /**
     * @var boolean This permission is needed to delete pages (from both tree and from database)
     */
    protected $DELETE_PAGE = false;
    /**
     * @var boolean Indicates whether a administrator can edit existing content
     */
    protected $EDIT_PAGE = false;
    /**
     * @var boolean Indicates whether a administrator may move pages in the tree
     */
    protected $MOVE_PAGE = false;
    /**
     * @var boolean Indicates whether a administrator can upload files to the server
     */
    protected $UPLOAD_FILE = false;
    /**
     * @var boolean Indicates whether a administrator can delete files from the server. Only files uploaded with the cms can be deleted
     */
    protected $DELETE_FILE = false;
    /**
     * @var boolean Indicates whether a administrator can edit templates. Only developers should have this permission
     */
    protected $MANAGE_TEMPLATE = false;
    /**
     * @var boolean Indicates whether a administrator can edit settings
     * @since 1.2 - 19 feb 2010
     */
    protected $MANAGE_SETTINGS = false;
    /**
     * @var boolean Indicates whether a administrator is allowed to create and send mailings
     * @since 1.3 - 23 jun 2010
     */
    protected $MANAGE_MAILINGS = false;

    /**
     * @var boolean Indicates whether a administrator is allowed to create and update calendars
     * @since 1.4 - 19 oct 2010
     */
    protected $MANAGE_CALENDARS = false;
    /**
     * @var boolean Indicates whether a administrator is allowed to create and update elements
     * @since 1.4 - 29 oct 2010
     */
    protected $MANAGE_ELEMENTS = false;
    /**
     * @var boolean Indicates whether a administrator is allowed to create and update maps
     * @since 1.5 - 18 nov 2010
     */
    protected $MANAGE_MAPS = false;

    protected $APPROVE_ISSUE = false;

    /**
     * @var array An indexbased array of exceptions. Call throwExceptions with the corresponding ID to access it.
     * @version 1.1
     */
    private $_exceptions = array();

    private $availablePermissions = [
        'IS_AUTH',
        'MANAGE_ADMIN',
        'MANAGE_USER',
        'CREATE_PAGE',
        'DELETE_PAGE',
        'EDIT_PAGE',
        'MOVE_PAGE',
        'DELETE_FILE',
        'APPROVE_ISSUE',
        'MANAGE_TEMPLATE',
        'MANAGE_SETTINGS',
        'UPLOAD_FILE',
        'MANAGE_MAILINGS',
        'MANAGE_CALENDARS',
        'MANAGE_ELEMENTS',
        'MANAGE_MAPS'
    ];

    function __construct()
    {

        foreach ($this->availablePermissions as $permission) {
            if (isset($_SESSION[$permission]) && $_SESSION[$permission] == true) {
                $this->{$permission} = true;
            }
        }
        $ar = array();
        $ar[1000] = 'An error occurred';

        $ar[1001] = 'No administrator was authenticated';
        $ar[1002] = 'You are not allowed to create administrators';
        $ar[1003] = 'Could not insert the administrator into the database';
        $ar[1004] = 'You cannot delete yourself';
        $ar[1005] = 'You are not allowed to update administrator accounts';
        $ar[1006] = 'A administrator with that e-mail address already exists';

        $ar[2001] = 'No slashes allowed';
        $ar[2002] = 'Incorrect parameter type #0, must be an integer';
        $ar[2003] = 'Incorrect parameter type #0, must be a string';
        $ar[2004] = 'Incorrect parameter type #0, must be a double';
        $ar[2005] = 'Incorrect parameter type #0, must be a boolean';
        $ar[2006] = 'Incorrect parameter type #0, must be a valid e-mail address';
        $ar[2007] = 'Incorrect parameter type #0, must be an array';
        $ar[2008] = 'Incorrect parameter type #0, must be an object';

        $ar[3001] = 'Unknown variable';
        $ar[3002] = 'You are not allowed to manage settings';

        $ar[4001] = 'Folder not found';
        $ar[4002] = 'Parent folder not found';
        $ar[4003] = 'Could not create folder. (Duplicate name?)';
        $ar[4004] = 'Could not delete folder. Is it empty?';
        $ar[4005] = 'File not found';
        $ar[4006] = 'A file with the same name already exists in the target folder';
        $ar[4007] = 'You are not allowed to delete files';
        $ar[4008] = 'Could not create folder.';

        $ar[5001] = 'You are not allowed to delete pages';
        $ar[5002] = 'You are not allowed to remove pages';
        $ar[5003] = 'Cannot delete this page since it\'s still present in the navigation tree:' . "\n- #0";
        $ar[5004] = 'Backup not found';

        $ar[6001] = 'The selected node has a maximum of #0 children.';
        $ar[6002] = 'Cannot unlock this node because it has locked children.';
        $ar[6003] = 'Cannot add the page, it already exists in the target node';
        $ar[6004] = 'The object must have a title';
        $ar[6005] = 'Cannot create sitemap';

        $ar[7001] = 'You are not allowed to edit templates.';
        $ar[7002] = 'You are not allowed to set the maximum children.';
        $ar[7003] = 'You are not allowed to set the lifetime of a template.';
        $ar[7004] = 'A template with that name already exists.';
        $ar[7005] = 'Invalid template name.';
        $ar[7006] = 'Failed to insert the template.';
        $ar[7007] = 'Cannot delete the template, it\'s still in use by some pages.';
        $ar[7008] = 'The page must be based on a Mail Template';
        $ar[7009] = 'The plugin directory could not be found. It should be in bright/cms/assets/plugins/';
        $ar[7010] = 'Template #0 not found.';

        $ar[8001] = 'You are not allowed to manage users';
        $ar[8002] = 'A user with that e-mail address already exists';
        $ar[8003] = 'Could not insert the user into the database';
        $ar[8004] = 'No user was authenticated';
        $ar[8005] = 'The usergroup already exists';
        $ar[8006] = 'Missing property \'groupname\'';
        $ar[8007] = 'Cannot open csv file';
        $ar[8008] = 'Invalid csv file';
        $ar[8009] = 'No user was found';


        $ar[9001] = 'The class #0 does not exist';
        $ar[9002] = 'The method #0 does not exist in class #1';
        $ar[9003] = 'The Swift mailing package is not installed on this server, include the Swift package in the /bright/externallibs/Swift folder';
        $ar[9004] = 'The TCPDF package is not installed on this server, include the TCPDF package in the /bright/externallibs/tcpdf folder';
        $ar[9005] = 'The Sphider search package is not installed on this server, include the Sphider search package in the /bright/externallibs/sphider folder';

        $this->_exceptions = $ar;
    }

    public function authApikey()
    {
        $key = filter_input(INPUT_GET, 'apikey', FILTER_SANITIZE_STRING);
        if ($key === APIKEY) {
            $this->_setPermissions($this->availablePermissions);
        }
    }

    public function authCLI()
    {
        if(!BrightUtils::inBrowser(false)) {
            $this->_setPermissions($this->availablePermissions);
        }
    }

    /**
     * Returns an array with the permissions of the administrator
     * @return array An array with permissions
     */
    protected function getPermissions()
    {
        $permissions = array();
        foreach ($this->availablePermissions as $permission) {
            if ($this->{$permission}) {
                $permissions[] = $permission;
            }
        }

        return $permissions;
    }

    /**
     * Authenticates a administrator by e-mail and password
     * @param string $email The e-mail address of the administrator
     * @param string $password An SHA1 hash of the password
     * @return array The rows with matching administratorid's
     */
    protected function auth($email, $password)
    {

        $query = "SELECT u.*, up.permission " .
            "FROM administrators u, administratorpermissions up " .
            "WHERE u.email = '" . Connection::getInstance()->escape_string($email) . "' " .
            "AND u.password = '" . Connection::getInstance()->escape_string($password) . "' " .
            'AND u.id = up.administratorId';
        $co = Connection::getInstance();

        $result = $co->getRows($query);
        return $result;
    }

    /**
     * Sets the permissions of a administrator
     * @param array $permissions An array containing the permissions of a administrator
     */
    private function _setPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            $this->{$permission} = $_SESSION[$permission] = true;
        }
    }

    /**
     * Places the administrator in the session
     * @param OAdministratorObject $administrator The administrator to set
     */
    protected function setAdministrator(OAdministratorObject $administrator)
    {
        $_SESSION['administratorId'] = $administrator->id;
        $_SESSION['administratorEmail'] = $administrator->email;
        $this->_setPermissions($administrator->permissions);
    }

    /**
     * Gets the currently authenticated administrator
     * @return OAdministratorObject The authenticated administrator
     */
    public function getAdministrator()
    {
        $administrator = new OAdministratorObject();
        if (isset($_SESSION['administratorId'])) {
            $administrator->id = $_SESSION['administratorId'];
            $administrator->email = $_SESSION['administratorEmail'];
        }
        $administrator->sessionId = session_id();
        $administrator->permissions = $this->getPermissions();
        return $administrator;
    }

    /**
     * Gets the settings for the currently authenticated administrator
     * @return Object
     */
    public function getSettings()
    {
        if (!isset($_SESSION['administratorId'])) {
            return null;
        }
        $sql = 'SELECT `settings` FROM `administrators` WHERE `id`=' . (int)$_SESSION['administratorId'];
        $current = Connection::getInstance()->getField($sql);

        if ($current) {
            $current = json_decode($current);
        } else {
            $current = new stdClass();
        }

        return $current;
    }

    /**
     * Resets the session
     */
    protected function resetAll()
    {
        $this->resetPermissions();
        $_SESSION = array();
        session_destroy();
        session_start();
    }

    /**
     * Updates the permissions of the administrator<br/>
     * Only the permissions in the session are updated, the database is not updated
     * @param array $permissions An array of permissions
     */
    protected function updatePermissions($permissions)
    {
        $this->resetPermissions();
        $this->_setPermissions($permissions);
    }

    /**
     * Sets all the permissions to false
     */
    protected function resetPermissions()
    {
        foreach ($this->availablePermissions as $permission) {
            $this->{$permission} = $_SESSION[$permission] = false;
        }
    }

    public function throwException($id, $vars = null)
    {
        Connection::getInstance()->addTolog("Error $id " . print_r($vars, true));
        if (array_key_exists($id, $this->_exceptions)) {
            $exc = $this->_exceptions[$id];
            if ($vars) {
                if (!is_array($vars))
                    $vars = array($vars);

                for ($i = 0; $i < count($vars); $i++)
                    $exc = str_replace('#' . $i, $vars[$i], $exc);
            }
            return new Exception($exc, $id);
        }
        return new Exception('An unspecified error occured', $id);
    }
}
