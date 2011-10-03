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

/* Check against direct access (which we don't want).
 ***/
if (!defined('HANDYMAN')) { die ('Do not access this file directly.'); }

/* Include the MODX core config file.
 ***/
require_once(dirname(dirname(dirname(dirname(__FILE__))))).'/config.core.php';

/**
 * HandyMan main class
 */
class HandyMan {
    public $basedir;
    /* @var string $webroot The web accessible URL to HandyMan. */
    public $webroot;
    /* @var modX $modx */
    public $modx;
    /* @var array $action Contains a hma key with the requested controller, and an option key. */
    public $action = array('hma' => 'home','options' => array('source' => 'default'));
    /* @var array $errors */
    public $errors = array();
    /* @var array $config An array with configuration options for HandyMan */
    public $config = array();
    /** @var hmRequest $request */
    public $request;

    /**
     * The construct method is called when the class is instantiated, so we
     * can use that to set some variables to the appropriate values and check
     * authorization.
     * @param array $config
     * @return \HandyMan
     */
    function __construct(array $config = array()) {
        /** Attempt to include the main MODX class to get access to xPDO
         * and the required MODX information. If this fails, halt the process.
         */
        if (!(include_once MODX_CORE_PATH . 'model/modx/modx.class.php')) {
            include MODX_CORE_PATH . 'error/unavailable.include.php';
            die('Site temporarily unavailable!');
        }

        /**
         * Instantiate the main MODX class for the manager context, load the parser and the lexicon service.
         */
        $this->modx = new modX;
        $this->modx->initialize('mgr');
        $this->modx->getParser();
        $this->modx->getService('lexicon','modLexicon');

        /**
         * Set some paths to use throughout HandyMan
         */
        $this->basedir = realpath('.').'/';
        $this->webroot = $this->modx->getOption('handyman.webroot','',MODX_SITE_URL.'handyman/');

        $basePath = dirname(dirname(dirname(__FILE__))).'/';
        $this->config = array_merge(array(
            'baseUrl' => $this->webroot,
            'basePath' => $basePath,
            'corePath' => $basePath.'core/',
            'modelPath' => $basePath.'core/classes/',
            'controllersPath' => $basePath.'core/controllers/',
            'templatesPath' => $basePath.'core/templates/',
            'assetsPath' => $basePath.'assets/',
            'tplSuffix' => '.tpl',
        ),$config);

        /**
         * Echo errors with a level of ERROR or higher.
         */
        $this->modx->setLogTarget('ECHO');
        $this->modx->setLogLevel(modX::LOG_LEVEL_ERROR);
        error_reporting(E_ALL); ini_set('display_errors',true);
    } // End of method __construct()

    /**
     * Separate out initialization methods for better abstraction.
     * Loads the request handler and checks authentication.
     * 
     * @return void
     */
    public function initialize() {
        $this->loadRequest();
        if (empty($this->request)) {
            $this->end('Could not find request handler at: '.$this->config['basePath']);
        }
        $this->request->checkAuthentication();
    }

    /**
     * Load the hmRequest class into {$hm::request}
     * @return \hmRequest
     */
    public function loadRequest() {
        if (empty($this->request)) {
            if ($this->modx->loadClass('hmRequest',$this->config['corePath'].'classes/',true,true)) {
                $this->request = new hmRequest($this);
            } else {
                $this->modx->log(modX::LOG_LEVEL_ERROR,'[HandyMan] Could not load request class from: '.$this->config['corePath'].'classes/');
            }
        }
        return $this->request;
    }

    /**
     * Close session and die processing
     * @param string $message
     */
    public function end($message = '') {
        @session_write_close();
        die($message);
    }

    /**
     * Load a class from core/classes.
     * @param string $classname
     * @return bool
     */
    public function loadClass($classname = '') {
        if ($classname == '') { return false; }

        if (file_exists($this->basedir.'core/classes/'.$classname.'.php')) {
            include_once ($this->basedir.'core/classes/'.$classname.'.php');
            $this->$classname = new $classname;
        } else {
            return false;
        }
        return true;
    }


    /**
     * Gets a Template and caches it; also falls back to file-based templates.
     *
     * @access public
     * @param string $name The name of the Chunk
     * @param array $properties The properties for the Chunk
     * @return string The processed content of the Chunk
     */
    public function getTpl($name,array $properties = array()) {
        if (!$properties['webroot']) $properties['webroot'] = $this->webroot;
        if (!$properties['basedir']) $properties['basedir'] = $this->basedir;
        $chunk = null;
        if (!isset($this->templates[$name])) {
            $chunk = $this->modx->getObject('modChunk',array('name' => $name),true);
            if (empty($chunk)) {
                $chunk = $this->_getTpl($name,$this->config['tplSuffix']);
                if ($chunk == false) return false;
            }
            $this->templates[$name] = $chunk->getContent();
        } else {
            $o = $this->templates[$name];
            $chunk = $this->modx->newObject('modChunk');
            $chunk->setContent($o);
        }
        $chunk->setCacheable(false);
        return $chunk->process($properties);
    }
    /**
     * Returns a modChunk object from a template file.
     *
     * @access private
     * @param string $name The name of the Chunk. Will parse to name.chunk.tpl by default.
     * @param string $suffix The suffix to add to the chunk filename.
     * @return modChunk/boolean Returns the modChunk object if found, otherwise
     * false.
     */
    private function _getTpl($name,$suffix = '.tpl') {
        $chunk = false;
        $f = $this->config['templatesPath'].strtolower($name).$suffix;
        if (file_exists($f)) {
            $o = file_get_contents($f);
            $chunk = $this->modx->newObject('modChunk');
            $chunk->set('name',$name);
            $chunk->setContent($o);
        }
        return $chunk;
    }

    /**
     * Returns the name of the license.
     * Due to be refactored in a later release.
     *
     * @return string
     */
    public function getLicenseName() {
        return 'Early Contributors';
    }

    /**
     * Runs a processor.
     *
     * @param array $options
     * @return mixed|string
     */
    public function runProcessor(array $options = array()) {
        $processor = isset($options['processors_path']) && !empty($options['processors_path']) ? $options['processors_path'] : MODX_PROCESSORS_PATH;
        if (isset($options['location']) && !empty($options['location'])) $processor .= $options['location'] . '/';
        $processor .= str_replace('../', '', $options['action']) . '.php';
        if (file_exists($processor)) {
            if (empty($this->modx->lexicon)) $this->modx->getService('lexicon', 'modLexicon');
            if (empty($this->modx->error)) $this->modx->getService('error','error.modError');

            $modx =& $this->modx;

            /* create scriptProperties array from HTTP GPC vars */
            if (!isset($_POST)) $_POST = array();
            if (!isset($_GET)) $_GET = array();
            $scriptProperties = array_merge($_GET,$_POST,$options);
            if (isset($_FILES) && !empty($_FILES)) {
                $scriptProperties = array_merge($scriptProperties,$_FILES);
            }
            $result = include $processor;
        } else {
            $result = 'Processor not found: '.$processor;
        }
        return $result;
    }

} // End of class HandyMan
?>
