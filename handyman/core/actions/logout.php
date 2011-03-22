<?php
    
    class logout extends HandyMan {
        function __construct() {
            
        }
        public function run() {
            $logout = $this->processor(array(
                    'action' => 'logout',
                    'location' => 'security'));
            if ($logout['success'] != true) {
                $o = '<p>Something went wrong logging you out >:( '.$logout.'</p>';
            } 
            else {
                $o = '<p>Thank you for using HandyMan. Like what you are seeing so far?</p>
                <div data-inline="true">
                    <a href="'.$this->webroot.'index.php" data-role="button" data-ajax="false">Back to Login</a> 
                    <a href="http://www.modxmobile.com" data-role="button" data-theme="b">Fund Development</a>
                </div>';
            }
            return $o;
        }
        
    }

?>