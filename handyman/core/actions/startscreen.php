<?php
    
    class startscreen extends HandyMan {
        public $actionMap;
        
        function __construct() {
            $this->actionMap = array(
                array(
                    'action' => 'resourcelist',
                    'linktext' => 'List Resources',
                ),
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
            $o .= $this->processActions($this->actionMap);
            $o .= '</ul>';
            return $o;
        }

    }

?>