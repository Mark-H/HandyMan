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

/* Check against direct access just in case.
 ***/
if (!defined('HANDYMAN')) { die ('Do not access this file directly.'); }

/**
 * HandyMan main class
 */
class HandyMan {
    /* @var string $path The absolute path to HandyMan */
    public $path;
    /* @var string $url The web accessible URL to HandyMan */
    public $url;
    /* @var modX $modx */
    public $modx;
    /* @var array $action Contains a hma key with the requested controller, and an option key */
    public $action = array('hma' => 'home','options' => array('source' => 'default'));
    /* @var array $errors */
    public $errors = array();
    /* @var array $config An array with configuration options for HandyMan */
    public $config = array();
    /* @var hmRequest $request */
    public $request;
    /* @var array $templates */
    public $templates;

    /**
     * The construct method is called when the class is instantiated, so we
     * can use that to set some variables to the appropriate values and check
     * authorization.
     * @param modX $modx
     * @param array $config
     * @return \HandyMan
     */
    function __construct(modX $modx,array $config = array()) {
        $this->modx =& $modx;
        /**
         * Calculated & Set some paths to use throughout HandyMan
         */
        $corePath = $this->modx->getOption('handyman.core_path',null,dirname(dirname(__FILE__)));
        $path = $this->modx->getOption('handyman.path',null,$this->modx->getOption('base_path').'handyman/');
        $url = $this->modx->getOption('handyman.url','',$this->modx->getOption('base_url').'handyman/');
        $templates = $this->modx->getOption('handyman.templates',null,'default');
        $theme = $this->modx->getOption('handyman.theme',null,'default');

        $this->url = $url;
        $this->path = $path;

        $this->config = array_merge(array(
            'baseUrl' => $url,
            'basePath' => $path,
            'corePath' => $corePath.'/',
            'controllersPath' => $corePath.'/controllers/',
            'templatesPath' => $corePath.'/templates/',
            'classesPath' => $corePath.'/classes/',
            'assets' => $url.$theme.'/',
            'tplSuffix' => '.tpl',
            'templates' => $templates,
            'theme' => $theme
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
            if ($this->modx->loadClass('hmRequest',$this->config['classesPath'],true,true)) {
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
     * Gets a Template and caches it; also falls back to file-based templates.
     *
     * @access public
     * @param string $name The name of the Chunk
     * @param array $properties The properties for the Chunk
     * @return string The processed content of the Chunk
     */
    public function getTpl($name,array $properties = array()) {
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
        $f = $this->config['templatesPath'] . $this->config['templates'] . '/' . strtolower($name).$suffix;
        if (($this->config['templates'] != 'default') && !file_exists($f)) {
            $f = $this->config['templatesPath'] . 'default/' . strtolower($name).$suffix;
        }
        if (file_exists($f)) {
            $o = file_get_contents($f);
            /* @var modChunk $chunk */
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
