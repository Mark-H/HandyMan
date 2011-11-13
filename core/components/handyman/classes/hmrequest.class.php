<?php
/**
 * @package handyman
 */
class hmRequest {
    /** @var boolean $authorized */
    public $authorized;
    /** @var HandyMan $hm */
    public $hm;
    /** @var modX $modx */
    public $modx;
    /** @var string $action */
    public $action = 'home';
    /** @var hmController $controller */
    public $controller;

    /**
     * @param \HandyMan $hm
     * @param array $config
     * @return \hmRequest
     */
    function __construct(HandyMan &$hm,array $config = array()) {
        $this->hm =& $hm;
        $this->modx =& $hm->modx;
        $this->config = array_merge(array(),$config);
    }

    /**
     * Checks authentication for the current MODX User / Session.
     * Sets hmRequest::authorized with a boolean value indicating authentication status.
     * Also processes logout and login.
     */
    public function checkAuthentication() {
        $this->authorized = $this->modx->user && $this->modx->user->hasSessionContext('mgr');
        
        /* If we are authorized, the $this->authorized variable will have a value
         ***/
        if ($this->authorized) {
            // Check if it needs to log out
            if ($_GET['hma'] == 'logout') {
                $return = $this->hm->runProcessor(array(
                    'action' => 'logout',
                    'location' => 'security'),
                $this->modx);
                if ($return['success'] == 1) {
                    $this->action = array('hma' => 'login','options' => array('message' => 'Successfully logged out.'));
                    $this->authorized = false;
                } else {
                    $this->action = array('hma' => 'home','options' => array('message' => $return['message']));
                }
            }
            // Set the action
            else {
                $this->action = ($_GET['hma']) ?
                    array('hma' => $_GET['hma'],'options' => array('source' => 'get')) :
                    array('hma' => 'home','options' => array('source' => 'default'));
            }
        }

        // If not yet logged in...
        else if (!$this->authorized) {
            // Check if there is a login attempt, and if so validate it
            if ($_POST['hm_action'] == 'login') {
                $return = $this->hm->runProcessor(array(
                    'action' =>'login',
                    'location' => 'security'),
                $this->modx);
                if ($return['success'] == 1) {
                    $this->action = array('hma' => 'home','options' => array('source' => 'login'));
                } else {
                    $msg = $return['message'];
                    $this->action = array('hma' => 'login','options' => array('message' => $msg));
                }
            // Show the "login" action -> a login form.
            } else {
                $this->action = array('hma' => 'login','options' => array('source' => 'default'));
            }
        }
    }


    /**
     * Handle the request.
     * @return bool|string
     */
    public function handle() {
        if ((!is_array($this->action)) OR (count($this->action) < 1)) {
            return false;
        }

        $actionName = $this->action['hma'];
        $actionPath = $actionName;
        if (strlen($actionPath) < 1) { return 'Oops, hma failure.'; }
        $actionName = 'hmc'.str_replace(array('/','.'),'',$actionName);

        if (count($this->action['options']) > 0) {
            $actionOptions = $this->action['options'];
        }

        $actionOptions['get'] = array_merge($_GET,$_POST);
        foreach ($actionOptions['get'] as $k => $v) {
            $actionOptions['get'][$k] = htmlentities($v,ENT_QUOTES,'UTF-8');
        }
        if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
            array_walk_recursive($actionOptions['get'], create_function('&$val', '$val = stripslashes($val);'));
        }
        $this->action['gpc'] = $actionOptions['get'];

        $output = '';
        $this->modx->loadClass('hmController',$this->hm->config['controllersPath'],true,true);
        $included = include_once $this->hm->config['controllersPath'].$actionPath.'.class.php';
        if (!$included) {
            $this->modx->loadClass('empty',$this->hm->config['controllersPath'],true,true);
            $actionName = 'hmcEmpty';
        }
        $this->action['actionName'] = $actionName;
        $this->action['actionPath'] = $actionPath;
        $this->controller = new $actionName($this->hm,$this->action);
        /* attempt to initialize (setup) the page */
        $initialized = $this->controller->initialize();
        /* assuming all went well, process and render the page */
        if ($initialized === true) {
            if ($this->controller->meta) {
                $this->action['meta'] = $this->controller->meta;
            } else {
                $this->action['meta'] = array(
                    'title' => 'HandyMan'
                );
            }
            $output = $this->controller->render($actionOptions);
        } else {
            /* simulate a page for the error by wrapping with header/footer */
            $output = $this->controller->wrap($initialized);
        }

        $output = $this->stripMODXTags($output);
        return $output;
    }

    /**
     * Sanitizes MODX tags from $string.
     * 
     * @param $string
     * @return string
     */
    public function stripMODXTags($string) {
        $targets = array($string);
        $targets = modX::sanitize($targets,array(
            '@\[\[(.[^\[\[]*?)\]\]@si',
        ));
        return $targets[0];
    }
}
