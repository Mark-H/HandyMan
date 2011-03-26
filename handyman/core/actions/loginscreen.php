<?php
    
    class loginscreen extends HandyMan {
        function __construct() {
            
        }
        public function run($options = array()) {
            $o = '<p>Please login to access your MODX Mobile Manager, powered by HandyMan.</p>';
            $o .= $this->displayForm();
            return $o;
        }

        public function displayForm() {
            $df = '<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
                <fieldset>
                    <div data-role="fieldcontain">
                        <label for="login_username">Username</label>
                        <input type="text" name="username" id="login_username" />
                    </div>
                    
                    <div data-role="fieldcontain">
                        <label for="login_password">Password</label>
                        <input type="password" name="password" id="login_password" />
                    </div>
                    
                    <div data-role="fieldcontain">
                        <label for="login_rememberme">Stay logged in</label>
                        <input type="checkbox" name="rememberme" id="login_rememberme" />
                    </div>
                    
                    <div data-role="fieldcontain">
                        <input type="hidden" name="hm_action" value="login" />
                        <input type="submit" value="Login" />
                    </div>

                </fieldset>
                </form>';
            return $df;
        }
    }

?>