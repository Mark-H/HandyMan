<?php
    
    class startscreen extends HandyMan {
        
        public function run() {
            $o = '<p>Welcome to HandyMan - please choose something to do below.</p>';
            $o .= '<ul data-inset="true" data-role="listview">
                <li><a href="'.$this->webroot.'index.php?session=logout" data-ajax="false">Logout</a></li>
                </ul>';
            return $o;
        }
    }

?>