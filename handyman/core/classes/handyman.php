<?php
/* HandyMan main class */
    
    /* Prevent direct access */
    if (!defined('HANDYMAN')) { die ('Do not access this file directly.'); }
    define('IN_MANAGER_MODE',true);
    define('MODX_CORE_PATH','c:\wamp\www\handyman\core\\');

    /* Declare the class */
    class HandyMan {
        public $basedir;
        public $webroot;
        public $modx;
        public $authorized;
        public $user_fullname;
        public $action = 'startscreen';
        public $errors = array();
        
        function __construct() {
            $this->basedir = realpath('.').'\\';
            $this->webroot = 'http://localhost/handyman/handyman/';
            
            if (!(include_once MODX_CORE_PATH . 'model/modx/modx.class.php')) {
                include MODX_CORE_PATH . 'error/unavailable.include.php';
                die('Site temporarily unavailable!');
            }
            $this->modx = new modX;
            $this->modx->initialize('mgr');
            $this->authorized = $this->modx->checkSession('mgr');
            
            // If not yet logged in...
            if (!$this->authorized) { 
                // Check if there is a login attempt, and if so validate it
                if ($_POST['hm_action'] == 'login') {
                    $return = $this->processor(array(
                        'action' =>'login',
                        'location' => 'security'));
                    if ($return['success'] == 1) {
                        $this->action = 'startscreen';
                    } else {
                        $msg = $return['message'];
                        $this->action = 'login';
                    }

                // Show the "login" action -> a login form.
                } else {
                    $this->action = 'login';
                }
            }
            
            // If logged in..
            elseif ($this->authorized) {
                // Check if it needs to log out
                if ($_GET['session'] == 'logout') {
                    $logout = $this->modx->fromJSON($this->processor(array(
                    'action' => 'logout',
                    'location' => 'security')));
                    $this->action = $logout;
                } else {
                    $this->action = ($_GET['hma']) ? $_GET['hma'] : 'startscreen'; 
                }
            }
        } // End of method __construct()

        function processAction ($actionname = '') {
            if ($actionname == '') { return false; }
            
            if (file_exists($this->basedir.'core\actions\\'.$actionname.'.php')) {
                include_once ($this->basedir.'core\actions\\'.$actionname.'.php');  
                $this->$actionname = new $actionname;
                return $this->$actionname->run();
            }
            else {
                return 'Uh oh, unable to find the '.$actionname.' action!';
            }
        } // End of function processAction
        
        
        
        function loadClass($classname = '') {
            if ($classname == '') { return false; }
            
            if (file_exists($this->basedir.'core\classes\\'.$classname.'.php')) {
                include_once ($this->basedir.'core\classes\\'.$classname.'.php');
                $this->$classname = new $classname;
            } else {
                return false;
            }
        } // End of function loadClass($classname)
        
        
        function parseMarkup($header = array('title' => 'HandyMan'), $body, $footer = '',$id = '') {   
            $id = ($id != '') ? $id : $this->action;
            return '<!DOCTYPE HTML>
            <html lang="en-US">
            <head>
            	<meta charset="UTF-8">
            	<title>'.$header['title'].'</title>
                <link rel="stylesheet" href="http://code.jquery.com/mobile/1.0a3/jquery.mobile-1.0a3.min.css" />
                <script src="http://code.jquery.com/jquery-1.5.min.js"></script>
                <script src="http://code.jquery.com/mobile/1.0a3/jquery.mobile-1.0a3.min.js"></script>
            </head>
            <body>
                <div data-role="page" id="'.$id.'">
                    <div data-role="header">
                        <h1>'.$header['title'].'</h1>
                    </div>
                    <div data-role="content">
                        '.$body.'
                    </div>
                    <div data-role="footer">
                        '.$footer.'
                    </div>
                    
                    <div align="CENTER" data-role="content" id="contentDialog" name="contentDialog">  
  </div>  
  
                    <div data-role="header" id="hdrConfirmation" name="hdrConfirmation" data-nobackbtn="true">
                    
                    </div>  
                    <div data-role="content" id="contentConfirmation" name="contentConfirmation" align="center">  
                    
                    </div>  
                    <div data-role="footer" id="ftrConfirmation" name="ftrConfirmation">
                    </div>  
                </div>
            </body>
            </html>';
        }
        

    public function processor(array $options = array()) {
        $processor = isset($options['processors_path']) && !empty($options['processors_path']) ? $options['processors_path'] : $this->modx->config['processors_path'];
        if (isset($options['location']) && !empty($options['location'])) $processor .= $options['location'] . '/';
        $processor .= str_replace('../', '', $options['action']) . '.php';
        if (file_exists($processor)) {
            if (!isset($this->modx->lexicon)) $this->modx->getService('lexicon', 'modLexicon');
            if (!isset($this->modx->error)) $this->modx->getService('error','error.modError');

            /* create scriptProperties array from HTTP GPC vars */
            if (!isset($_POST)) $_POST = array();
            if (!isset($_GET)) $_GET = array();
            $scriptProperties = array_merge($_GET,$_POST,$options);
            if (isset($_FILES) && !empty($_FILES)) {
                $scriptProperties = array_merge($scriptProperties,$_FILES);
            }

            $modx =& $this->modx;
            $login =& $this;
            $result = include $processor;
        } else {
            //$this->modx->error->failure(modX::LOG_LEVEL_ERROR, "Processor {$processor} does not exist; " . print_r($options, true));
            $result = 'error'.$processor;
        }
        return $result;
    }
    } // End of class HandyMan
?>