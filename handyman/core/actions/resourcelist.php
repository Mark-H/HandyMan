<?php

    class resourcelist extends HandyMan {
        public $parent;
        public $context;
        public $start;
        public $limit;
        public $list;
        
        function __construct() {
            
        }
        
        public function run($options = array(),&$modx) {
            $wantedOpts = array('ctx','parent','start','limit','list');
            foreach ($wantedOpts as $wantedOpt) {
                $$wantedOpt = ($options['get'][$wantedOpt]) ? $options['get'][$wantedOpt] : null;
            }
            if (!$ctx) {
                $o = 'Please choose a context.';
                $o .= '<ul data-inset="true" data-role="listview">';
                $o .= $this->processActions($this->listContexts($modx));
                $o .= '</ul>';
            } 
            
            else {
                if ($parent == 0) {

                }
                elseif ($parent > 0) {
                    $o = 'Viewing children of resource '.$parent.'.';
                }
                $o .= '<ul data-inset="true" data-role="listview">';
                $o .= $this->processActions($this->listResources($modx,$ctx,$parent));
                $o .= '</ul>';
            }
            
            return $o;
        }
        
        public function listContexts(&$modx) {
            $c = $modx->newQuery('modContext');
            $c->where(array(
                'key:!=' => 'mgr'
            ));
            $contexts = array();
            $contextobjects = $modx->getCollection('modContext',$c);
            foreach ($contextobjects as $ctx) {
                $contexts[] = array(
                    'action' => 'resourcelist',
                    'linktext' => $ctx->get('key'),
                    'linkparams' => array('ctx' => $ctx->get('key'))
                );
            }
            return $contexts;
        }
        
        public function listResources(&$modx,$ctx,$parent) {
            $c = $modx->newQuery('modResource');
            $c->where(array(
                'context_key' => $ctx,
                'parent' => $parent));
            
            $resources = array();
            $ress = $modx->getCollection('modResource',$c);
            foreach ($ress as $res) {
                $resources[] = array(
                    'action' => 'resourcelist',
                    'linktext' => $res->get('pagetitle'),
                    'linkparams' => array('ctx' => $ctx, 'parent' => $res->get('id'))
                );
            }
            return $resources;
        }
    }

?>