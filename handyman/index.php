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

/*This file handles all incoming requests. It instantiates the main HandyMan
 * class and loads other classes depending on the specific request.
 ***/

/**
 * Include the main MODX Class
 */
require_once(dirname(dirname(__FILE__))).'/config.core.php';
if (!(include_once MODX_CORE_PATH . 'model/modx/modx.class.php')) {
    include MODX_CORE_PATH . 'error/unavailable.include.php';
    die('HandyMan temporarily unavailable!');
}

/**
 * Instantiate the main MODX class for the manager context, load the parser and the lexicon service.
 */
$modx = new modX;
$modx->initialize('mgr');
$modx->getParser();
$modx->getService('lexicon','modLexicon');
$modx->setOption('modRequest.class','modRequest');
$modx->getRequest();

/* Include the main HandyMan class.
 * This class takes care of authentication and provides the extension
 * with functions to execute the requests.
 *
 * After inclusion, set up the $hm variable as the main object.
 **/
define('HANDYMAN', true);

$hmPath = $modx->getOption('handyman.core_path');
if (empty($hmPath)) { $hmPath = MODX_CORE_PATH.'components/handyman/'; }
require_once $hmPath.'classes/handyman.class.php';
$hm = new HandyMan($modx);
$hm->initialize();

/* The $hm->action variable is an array set with the request and additional
 * information. Take it, and parse it with the $hm->processAction function.
 * Set it to the $hmo variable for later output.
 **/
$output = $hm->request->handle();

/* Use the collected data to output the HandyMan UI with the parseMarkup
 * function. Takes three properties: meta data, body and footer text.
 **/
$hm->end($output);