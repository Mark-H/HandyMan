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

class hmcResourceDelete extends hmController {
    protected $cache = false;
    protected $templateFile = 'dialog';
    protected $viewType = hmController::VIEW_DIALOG;

    /** @var modResource $resource */
    public $resource;

    public function getPageTitle() {
        return 'Delete Resource';
    }
    public function setup() {
        if (empty($_REQUEST['rid'])) {
            return 'No valid resource id passed.';
        }
        $this->resource = $this->modx->getObject('modResource',intval($_REQUEST['rid']));
        if (empty($this->resource)) {
            return 'Resource not found.';
        }
        return true;
    }

    public function process() {
        $action = $this->resource->get('deleted') ? 'undelete' : 'delete';

        $return = $this->hm->runProcessor(array(
            'action' => 'resource/'.$action,
            'id' => $this->resource->get('id'),
        ));
        if ($return['success'] == 1) {
            if ($action == 'undelete') {
                $message = 'Successfully undeleted resource '.$this->resource->get('id').'.';
            } else {
                $message = 'Successfully deleted resource '.$this->resource->get('id').'.';
            }
        } else {
            $message = 'Something went wrong. '.$return['message'];
        }
        $this->setPlaceholder('message',$message);
    }
}