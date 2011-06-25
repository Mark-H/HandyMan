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
                        'location' => 'security'),
                    $this->modx);
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
                        'location' => 'security'),
                    $this->modx);
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
            if (strlen($actionname) < 1) { return 'Oops, hma failure.'; }
            if (count($action['options']) > 0) {
                $actionOptions = $action['options'];
            }
            // @TODO: $_GET[] validation, sanitation
            $actionOptions['get'] = $_GET;
            
            if (file_exists($this->basedir.'core/actions/'.$actionname.'.php')) {
                include_once ($this->basedir.'core/actions/'.$actionname.'.php');
                $this->$actionname = new $actionname;
                if ($this->$actionname->meta) {
                    $this->action['meta'] = $this->$actionname->meta;
                } else {
                    $this->action['meta'] = array(
                        'title' => 'HandyMan'
                    );
                }
                return $this->$actionname->run($actionOptions,$this->modx);
            }
            else {
                $this->action['meta'] = array(
                    'title' => 'An error occurred'
                );
                return 'Uh oh, unable to find the '.$actionname.' action! (Requested path: '.$this->basedir.'core/actions/'.$actionname.'.php)
                <br /><br />During development, some actions may not have been included and this error may show up.';
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
        
        
        function parseMarkup($meta = array('title' => 'HandyMan'), $body, $footer = '',$id = '') {
            $id = ($id != '') ? $id : $this->action['hma'];
            $o = '<!DOCTYPE HTML>
            <html lang="en-US">
            <head>
            	<meta charset="UTF-8">
            	<title>'.$meta['title'].' - HandyMan Mobile Manager</title>
                <link rel="stylesheet" href="assets/jqm/jquery.mobile-1.0a4.1.min.css" />
                <script src="assets/jqm/jquery-1.5.2.min.js"></script>
                <script src="assets/jqm/jquery.mobile-1.0a4.1.min.js"></script>
                <link href="assets/css/handyman.css" rel="stylesheet" type="text/css" />
            </head>
            <body>
                <div data-role="page" id="'.$id.'">';
            // Depending on the type of page (determined by the $meta['page'] option) we'll output something here.
            switch ($meta['view']) {
                // First "view" is a dialog window, which doesn't need as many buttons and stuff. We do add a "Close window" button here.
                case 'dialog':
                    $o .= '<div data-role="header" class="redGradient">
                        <h1>'.$meta['title'].'</h1>
                    </div>
                    <div data-role="content">
                        '.$body.'
                        <br />
                        <a href="#" data-icon="delete" data-rel="back" data-transition="pop"
                            data-role="button" data-inline="true">Close window</a>
                    </div>

					<div data-role="footer">
                        '.$footer.'
                    </div>';
                    break;
                // The default view is the "page" one, which has a back & home button and just the main content after that.
                case 'page':
                default:
                    $o .= '
                    <div data-role="header" class="redGradient">
                        <a href="javascript: history.go(-1);" data-icon="arrow-l" data-rel="back" data-direction="reverse">Back</a>
                        <h1>'.$meta['title'].'</h1>
                        <a href="index.php" data-icon="home" data-iconpos="notext" data-transition="flip">Home</a>
                    </div>
                    <div data-role="content">
                        '.$body.'
                    </div>
                    <!--<div data-role="footer">
                        '.$footer.'
                    </div>-->
					
								<!-- new footer fixed nav -->
					
					
					<div data-role="footer" data-position="fixed">
    <div data-role="navbar">
    <ul>
        <li><a href="' . $this->webroot . 'index.php?hma=res_create" id="create" data-icon="custom">Create Resource</a></li>
        <li><a href="' . $this->webroot . 'index.php?hma=resourcelist" id="manage" data-icon="custom">Manage Resource</a></li>
        <li><a href="' . $this->webroot . 'index.php?hma=logout" id="logout" data-icon="custom">Logout</a></li>
    </ul>
    </div>
</div>

<!-- end fixed nav -->
					
					';
                    break;
            }
            // Some debugging & closing the tags
            $o .= print_r($this->action,true).'
                </div>
            </body>
            </html>';
            
            return $o;
        }
        

        public function processor(array $options = array(),&$modx) {
            $processor = isset($options['processors_path']) && !empty($options['processors_path']) ? $options['processors_path'] : MODX_PROCESSORS_PATH;
            if (isset($options['location']) && !empty($options['location'])) $processor .= $options['location'] . '/';
            $processor .= str_replace('../', '', $options['action']) . '.php';
            if (file_exists($processor)) {
                if (!isset($modx->lexicon)) $modx->getService('lexicon', 'modLexicon');
                if (!isset($modx->error)) $modx->getService('error','error.modError');

                /* create scriptProperties array from HTTP GPC vars */
                if (!isset($_POST)) $_POST = array();
                if (!isset($_GET)) $_GET = array();
                $scriptProperties = array_merge($_GET,$_POST,$options);
                if (isset($_FILES) && !empty($_FILES)) {
                    $scriptProperties = array_merge($scriptProperties,$_FILES);
                }
                $result = include $processor;
            } else {
                //$this->modx->error->failure(modX::LOG_LEVEL_ERROR, "Processor {$processor} does not exist; " . print_r($options, true));
                $result = 'Processor not found: '.$processor;
            }
            return $result;
        }
    
    
        public function processActions($actionMap) {
            $ret = '';
            foreach ($actionMap as $a) {
                if (isset($a['dialog'])) {
                    $dialog = ' data-rel="dialog"';
                    $transition = ($a['transition']) ? $a['transition'] : 'pop';
                } else {
                    $transition = ($a['transition']) ? $a['transition'] : 'slide';
                }
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
                    <a href="'.$link.'" data-transition="'.$transition.'"'.$ajaxreset.$dialog.'>
                        <h3>'.$a['linktext'].'</h3>'.
                        $aside.
                        $count.
                        '</a>
                    </li>';
                unset ($lps,$lp,$link,$count,$aside);
            }
            return $ret;
        }

        public function getLicense() {
            return true;
        }

        public function getLicenseName() {
            return 'Early Contributors';
        }

} // End of class HandyMan
?>