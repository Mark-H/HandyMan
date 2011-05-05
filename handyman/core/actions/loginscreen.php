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
    
    class loginscreen extends HandyMan {
        public $meta = array(
                'title' => 'Please login'
            ); 
        function __construct() {

        }
        public function run($options = array(),&$modx) {
            $o = '';
            if ($options['message']) { $o .= '<p>'.$options['message'].'</p>'; }
            $o .= '<p>Please login to access your MODX Mobile Manager, powered by HandyMan.</p>';
            if (count($options) > 0) {
                $opts = array();
                foreach ($options as $opt => $val) {
                    $opts[$opt] = $val;
                }
            }
            $o .= $this->displayForm($opts);
            return $o;
        }

        public function displayForm($options) {
            $df = '<form action="index.php" method="post">
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
                        <input type="submit" value="Login" data-transition="slide" />
                    </div>

                </fieldset>
                </form>';
            return $df;
        }
    }

?>