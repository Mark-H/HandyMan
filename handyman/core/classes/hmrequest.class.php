<?php
class hmRequest {
    public $authorized;
    public $hm;
    public $modx;
    public $action = 'home';
    public $controller;

    function __construct(HandyMan &$hm,array $config = array()) {
        $this->hm =& $hm;
        $this->modx =& $hm->modx;
        $this->config = array_merge(array(),$config);
    }

    public function checkAuthentication() {
        $this->authorized = $this->modx->user && $this->modx->user->hasSessionContext('mgr');
        
        /* If we are authorized, the $this->authorized variable will have a value
         ***/
        if ($this->authorized) {
            // Check if it needs to log out
            if ($_GET['hma'] == 'logout') {
                $return = $this->processor(array(
                    'action' => 'logout',
                    'location' => 'security'),
                $this->modx);
                if ($return['success'] == 1) {
                    $this->action = array('hma' => 'login','options' => array('message' => 'Successfully logged out.'));
                    $this->authorized = false;
                } else {
                    $this->action = array('hma' => 'home','options' => array('message' => $return['message']));
                }
            }
            // Set the action
            else {
                $this->action = ($_GET['hma']) ?
                    array('hma' => $_GET['hma'],'options' => array('source' => 'get')) :
                    array('hma' => 'home','options' => array('source' => 'default'));
            }
        }

        // If not yet logged in...
        else if (!$this->authorized) {
            // Check if there is a login attempt, and if so validate it
            if ($_POST['hm_action'] == 'login') {
                $return = $this->processor(array(
                    'action' =>'login',
                    'location' => 'security'),
                $this->modx);
                if ($return['success'] == 1) {
                    $this->action = array('hma' => 'home','options' => array('source' => 'login'));
                } else {
                    $msg = $return['message'];
                    $this->action = array('hma' => 'login','options' => array('message' => $msg));
                }
            // Show the "login" action -> a login form.
            } else {
                $this->action = array('hma' => 'login','options' => array('source' => 'default'));
            }
        }
    }

    public function handle() {
        if ((!is_array($this->action)) OR (count($this->action) < 1)) {
            return 'Oops. Failure.';
        }

        $actionName = $this->action['hma'];
        $actionPath = $actionName;
        if (strlen($actionPath) < 1) { return 'Oops, hma failure.'; }
        $actionName = 'hmc'.str_replace('/','',$actionName);

        if (count($this->action['options']) > 0) {
            $actionOptions = $this->action['options'];
        }
        // @TODO: $_GET[] validation, sanitation
        $actionOptions['get'] = array_merge($_GET,$_POST);

        $output = '';
        $this->modx->loadClass('hmController',$this->hm->config['controllersPath'],true,true);
        if (!$this->modx->loadClass($actionPath,$this->hm->config['controllersPath'],true,true)) {
            $this->modx->loadClass('empty',$this->hm->config['controllersPath'],true,true);
            $actionName = 'hmcEmpty';
        }
        $this->action['actionName'] = $actionName;
        $this->action['actionPath'] = $actionPath;
        $this->controller = new $actionName($this->hm,$this->action);
        $this->controller->initialize();

        if ($this->controller->meta) {
            $this->action['meta'] = $this->controller->meta;
        } else {
            $this->action['meta'] = array(
                'title' => 'HandyMan'
            );
        }
        $output = $this->controller->render($actionOptions);

        $this->modx->parser->processElementTags('', $output, true, true, '[[', ']]', array(), 10);
        return $output;
    }


    public function processor(array $options = array(),&$modx) {
        $processor = isset($options['processors_path']) && !empty($options['processors_path']) ? $options['processors_path'] : MODX_PROCESSORS_PATH;
        if (isset($options['location']) && !empty($options['location'])) $processor .= $options['location'] . '/';
        $processor .= str_replace('../', '', $options['action']) . '.php';
        if (file_exists($processor)) {
            if (!isset($modx->lexicon)) $modx->getService('lexicon', 'modLexicon');
            if (!isset($modx->error)) $modx->getService('error','error.modError');

            /* create scriptProperties array from HTTP GPC vars */
            if (!isset($_POST)) $_POST = array();
            if (!isset($_GET)) $_GET = array();
            $scriptProperties = array_merge($_GET,$_POST,$options);
            if (isset($_FILES) && !empty($_FILES)) {
                $scriptProperties = array_merge($scriptProperties,$_FILES);
            }
            $result = include $processor;
        } else {
            //$this->modx->error->failure(modX::LOG_LEVEL_ERROR, "Processor {$processor} does not exist; " . print_r($options, true));
            $result = 'Processor not found: '.$processor;
        }
        return $result;
    }

}
