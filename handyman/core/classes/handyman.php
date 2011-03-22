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
            
            if (!$this->authorized) { 
                $this->action = 'login';
                if ($_POST['hm_action'] == 'login') {
                    $this->authorized = $this->modx->runProcessor('login',$_POST,array('location' => 'security'));
                }
            }
            
            
            if ($_GET['hma'] && $this->authorized) { 
                $this->action = $_GET['hma']; 
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
                    
                    
                    <div data-role="header"  id="hdrConfirmation" name="hdrConfirmation" data-nobackbtn="true">...</div>  
  <div data-role="content" id="contentConfirmation" name="contentConfirmation" align="center">  
    ...  
  </div>  
  <div data-role="footer" id="ftrConfirmation" name="ftrConfirmation"></div>  
  <!-- ====== confirmation content ends here ===== -->  
                </div>
            </body>
            </html>';
        }
        


    } // End of class HandyMan
?>