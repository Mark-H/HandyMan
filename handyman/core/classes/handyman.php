<?php
/* HandyMan main class */
    
    /* Prevent direct access */
    if (!defined('HANDYMAN')) { die ('Do not access this file directly.'); }
    define('IN_MANAGER_MODE',true);
    define('MODX_CORE_PATH','c:\wamp\www\handyman\core\\');

    /* Declare the class */
    class HandyMan {
        public $basedir;
        public $modx;
        public $authorized;
        public $action = 'startscreen';
        
        function __construct() {
            $this->basedir = realpath('.').'\\';
            
            if (!(include_once MODX_CORE_PATH . 'model/modx/modx.class.php')) {
                include MODX_CORE_PATH . 'error/unavailable.include.php';
                die('Site temporarily unavailable!');
            }
            $this->modx = new modX;
            $this->modx->initialize('mgr');
            $this->authorized = $this->modx->checkSession('mgr');
            if ($_GET['hma']) { $this->action = $_GET['hma']; }
        } // End of method __construct()

        function processAction ($actionname = '') {
            if ($actionname == '') { return false; }
            
            if (file_exists($this->basedir.'core\actions\\'.$actionname.'.php')) {
                include_once ($this->basedir.'core\actions\\'.$actionname.'.php');  
                $this->$actionname = new $actionname;
                $this->$actionname->run();
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
        
        
        function parseMarkup($header = array('title' => 'HandyMan - Mobile Manager for MODX'),$body,$footer) {   
            return '<!DOCTYPE HTML>
            <html lang="en-US">
            <head>
            	<meta charset="UTF-8">
            	<title>'.$header['title'].'</title>
            </head>
            <body>
            	'.$body.'
                <div id="footer">'.$footer.'</div>
            </body>
            </html>';
        }
        
    } // End of class HandyMan
?>