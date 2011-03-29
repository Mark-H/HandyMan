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

    class startscreen extends HandyMan {
        public $actionMap;
        
        function __construct() {
            $this->actionMap = array(
                array(
                    'action' => 'resourcelist',
                    'linktext' => 'List Resources',
                    'icon' => 'grid',
                ),
                array(
                    'action' => 'res_create',
                    'linktext' => 'Create resource',
                    'icon' => 'plus'
                ),
                array(
                    'action' => 'logout',
                    'linktext' => 'Logout',
                    'reset' => true,
                    // 'icon' => 'arrow-r',
                    // 'linkparams' => array ('session' => 'logout'),
                    // 'transition' => 'pop',
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