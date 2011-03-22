<?php
    
    class login extends HandyMan {
        
        function __construct() {
            
        }
        public function run() {
            $o = '<p>Please login to access your MODX Mobile Manager, powered by HandyMan.</p>';
            $o .= $this->displayForm();
            return $o;
        }
        
        public function displayForm($user = '', $rememberme = false, $lifetime = 0) {
            $df = '<form action="">
                <fieldset>
                    <label for="login_username">Username</label>
                    <input type="text" name="username" id="login_username" />
                    
                    <label for="login_password">Password</label>
                    <input type="password" name="password" id="login_password" />
                    
                    <label for="login_rememberme">Stay logged in</label>
                    <input type="checkbox" name="rememberme" id="login_rememberme" />
                    
                    <input type="submit" name="submit" value="Login">
                </fieldset>
                </form>';
            return $df;
        }
    }

?>