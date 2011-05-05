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

    class res_delete extends HandyMan {
        public $meta = array(
            'title' => 'Delete resource',
            'view' => 'dialog'
        );
        function __construct() {

        }
        public function run($options = array(),&$modx) {
            $o = '';

            if (is_numeric($options['get']['rid'])) {
                $rid = $options['get']['rid'];
            } else {
                return 'No valid resource id passed.';
            }

            $resource = $modx->getObject('modResource',$rid);

            if (empty($resource)) {
                return 'Resource not found.';
            }

            $action = ($resource->get('deleted')) ? 'undelete' : 'delete';
            $return = $this->processor(array(
                'action' => 'resource/'.$action,
                'id' => $rid),$modx);
            if ($return['success'] == 1) {
                if ($action == 'undelete') {
                    return 'Succesfully undeleted resource '.$rid.'.';
                } else {
                    return 'Succesfully deleted resource '.$rid.'.';
                }
            }
            else { return 'Something went wrong. '.$return['message']; }
        }

    }