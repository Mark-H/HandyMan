<?php
    /* HandyMan - a Mobile Manager for MODX 
     *
     * Copyright 2010-2011 by Mark Hamstra (contact via www.markhamstra.nl)
     *
     * This file is part of HandyMan, a Mobile Manager for MODX.
     *
     * HandyMan is free software; you can redistribute it and/or modify it under the
     * terms of the GNU General Public License as published by the Free Software
     * Foundation; either version 2 of the License, or (at your option) any later
     * version.
     *
     * HandyMan is distributed in the hope that it will be useful, but WITHOUT ANY
     * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
     * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License along with
     * HandyMan; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
     * Suite 330, Boston, MA 02111-1307 USA
     *
     * @package HandyMan
     ***/

    /* Check against direct access (which we don't want).
     ***/
    if (!defined('HANDYMAN')) { die ('Do not access this file directly.'); }
    
    /* Next, define the necessary mode for MODX to work, and define the core path.
     * @TO-DO: Set it somewhere instead of hardcode in the main class.
     ***/
    require_once(dirname(dirname(dirname(dirname(__FILE__))))).'/config.core.php';

    /* Start defining the main HandyMan class.
     ***/
    class HandyMan {
        public $basedir;
        public $webroot;
        public $modx;
        public $authorized;
        public $user_fullname;
        public $action = array('hma' => 'startscreen','options' => array('source' => 'default'));
        public $errors = array();
        
        /* The construct method is called when the class is instantiated, so we
         * can use that to set some variables to the appropriate values and check
         * authorization.
         ***/
        function __construct() {
            /* Attempt to include the main MODX class to get access to xPDO
             * and the required MODX information. If this fails, halt the process.
             ***/
            if (!(include_once MODX_CORE_PATH . 'model/modx/modx.class.php')) {
                include MODX_CORE_PATH . 'error/unavailable.include.php';
                die('Site temporarily unavailable!');
            }
            
            /* Instantiate the main MODX class for the manager context.
             ***/
            $this->modx = new modX;
            $this->modx->initialize('mgr');

            /* Set some paths to use throughout HandyMan
             ***/
            $this->basedir = realpath('.').'/';
            $this->webroot = $this->modx->getOption('handyman.webroot','',MODX_SITE_URL.'handyman/');

            /* Use the MODX session checker to see if we are authorized.
             ***/
            $this->authorized = $this->modx->checkSession('mgr');

            /* If we are authorized, the $this->authorized variable will have a value
             ***/
            if ($this->authorized) {
                // Check if it needs to log out
                if ($_GET['hma'] == 'logout') {
                    $return = $this->processor(array(
                    'action' => 'logout',
                    'location' => 'security'));
                    if ($return['success'] == 1) {
                        $this->action = array('hma' => 'loginscreen','options' => array('message' => 'Succesfully logged out.'));
                        $this->authorized = false;
                    } else {
                        $this->action = array('hma' => 'startscreen','options' => array('message' => $return['message']));
                    }
                } 
                // Set the action
                else {
                    $this->action = ($_GET['hma']) ? 
                        array('hma' => $_GET['hma'],'options' => array('source' => 'get')) :
                        array('hma' => 'startscreen','options' => array('source' => 'default'));
                }
            }       
            
            // If not yet logged in...
            else if (!$this->authorized) { 
                // Check if there is a login attempt, and if so validate it
                if ($_POST['hm_action'] == 'login') {
                    $return = $this->processor(array(
                        'action' =>'login',
                        'location' => 'security'));
                    if ($return['success'] == 1) {
                        $this->action = array('hma' => 'startscreen','options' => array('source' => 'login'));
                    } else {
                        $msg = $return['message'];
                        $this->action = array('hma' => 'loginscreen','options' => array('message' => $msg));
                    }
                // Show the "login" action -> a login form.
                } else {
                    $this->action = array('hma' => 'loginscreen','options' => array('source' => 'default'));
                }
            }
            
        } // End of method __construct()

        function processAction ($action = array()) {
            if ((!is_array($action)) OR (count($action) < 1)) {
                return 'Oops. Failure.';
            }
            
            $actionname = $action['hma'];
            if (strlen(actionname) < 1) { return 'Oops, hma failure.'; }
            if (count($action['options']) > 0) {
                $actionOptions = $action['options'];
            }
            // @TODO: $_GET[] validation, sanitation
            $actionOptions['get'] = $_GET;
            
            if (file_exists($this->basedir.'core/actions/'.$actionname.'.php')) {
                include_once ($this->basedir.'core/actions/'.$actionname.'.php');
                $this->$actionname = new $actionname;
                return $this->$actionname->run($actionOptions,$this->modx);
            }
            else {
                return 'Uh oh, unable to find the '.$actionname.' action! (Requested path: '.$this->basedir.'core/actions/'.$actionname.'.php)';
            }
        } // End of function processAction
        
        
        
        function loadClass($classname = '') {
            if ($classname == '') { return false; }
            
            if (file_exists($this->basedir.'core/classes/'.$classname.'.php')) {
                include_once ($this->basedir.'core/classes/'.$classname.'.php');
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
                <link rel="stylesheet" href="assets/jqm/jquery.mobile-1.0a4.1.min.css" />
                <script src="assets/jqm/jquery-1.5.2.min.js"></script>
                <script src="assets/jqm/jquery.mobile-1.0a4.1.min.js"></script>
                <link href="assets/css/override.css" rel="stylesheet" type="text/css" />
            </head>
            <body>
                <div data-role="page" id="'.$id.'">
                    <div data-role="header">
                        <a href="javascript: history.go(-1);" data-icon="arrow-l" data-rel="back" data-direction="reverse">Back</a>
                        <h1>'.$header['title'].'</h1>
                        <a href="index.php" data-icon="home" data-iconpos="notext">Home</a>
                    </div>
                    <div data-role="content">
                        '.$body.'
                    </div>
                    <div data-role="footer">
                        <h4>'.$footer.'</h4>
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
    
    
    public function processActions($actionMap) {
        $ret = '';
        foreach ($actionMap as $a) {
            $transition = ($a['transition']) ? $a['transition'] : 'slide';
            $icon = ($a['icon']) ? $a['icon'] : 'arrow-r';
            $ajaxreset = ($a['reset']) ? ' data-ajax="false"' : '';
            if (count($a['linkparams']) > 0) { 
                $lps = '';
                foreach ($a['linkparams'] as $lp => $lpv) { 
                    $lps .= '&'.$lp.'='.$lpv; 
                }
            }
            $link = $this->webroot.'index.php?hma='.$a['action'].$lps;
            if ((isset($a['count'])) && ($a['count'] > 0)) { $count = '<p class="ui-li-count">'.$a['count'].'</p>'; }
            if (isset($a['aside'])) { $aside = '<p>'.$a['aside'].'</p>'; }
            $ret .= '<li data-icon="'.$icon.'">
                <a href="'.$link.'" data-transition="'.$transition.'"'.$ajaxreset.'>
                    <h3>'.$a['linktext'].'</h3>'.
                    $aside.
                    $count.
                    '</a>
                </li>';
            unset ($lps,$lp,$link,$count,$aside);
        }
        return $ret;
    }
} // End of class HandyMan
?>