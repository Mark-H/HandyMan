<?php
    
    class startscreen extends HandyMan {
        public $actionMap;
        
        function __construct() {
            $this->actionMap = array(
                array(
                    'action' => 'logout',
                    'linktext' => 'Logout',
                    //'linkparams' => array ('session' => 'logout')
                )
            );
        }
        
        public function run() {
            $o = '<p>Welcome to HandyMan - please choose something to do below.</p>';
            $o .= '<ul data-inset="true" data-role="listview">';
            $o .= $this->processActionMap($this->actionMap);
            $o .= '</ul>';
            return $o;
        }
        public function processActionMap($actionMap) {
            $ret = '';
            foreach ($actionMap as $a) {
                $ret .= '<li>
                    <a href="'.$this->webroot.'index.php?hma='.$a['action'];
                if (count($a['linkparams']) > 0) { 
                    foreach ($a['linkparams'] as $lp => $lpv) { 
                        $ret .= '&'.$lp.'='.$lpv; 
                    }
                }
                $ret .= '" data-ajax="false">'.$a['linktext'].'</a></li>';
            }
            return $ret;
        }
    }

?>