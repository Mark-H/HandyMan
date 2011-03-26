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
                $contexts = $this->listContexts($modx);
                if (count($contexts) > 1) {
                    $o = 'Please choose a context.';
                    $o .= '<ul data-inset="true" data-role="listview">';
                    $o .= $this->processActions($contexts);
                    $o .= '</ul>';
                }
                elseif (count($contexts) == 1) {
                    $ctx = $contexts[0]['object']->get('key');
                }
                else {
                    return 'Your contexts are messed up.';
                }
            } 
            
            if ($ctx) {
                if ($parent > 0) {
                    $current = $modx->getObject('modResource',$parent);
                    $o = '<h2>'.$current->get('pagetitle').'</h2>';
                    $resEditMap = array(
                        array (
                            'action' => 'res_details',
                            'linktext' => 'Show details',
                            'linkparams' => array(
                                'ctx' => $ctx,
                                'rid' => $parent
                            )
                        ),
                        array(
                            'action' => 'res_publish',
                            'linktext' => 'Publish',
                            'linkparams' => array(
                                'ctx' => $ctx,
                                'rid' => $parent
                            )
                        ),
                        array(
                            'action' => 'res_modify',
                            'linktext' => 'Modify',
                            'linkparams' => array(
                                'ctx' => $ctx,
                                'rid' => $parent
                            )
                        ),
                        array(
                            'action' => 'res_delete',
                            'linktext' => 'Delete',
                            'linkparams' => array(
                                'ctx' => $ctx,
                                'rid' => $parent
                            )
                        )
                    );
                    $o .= '<ul data-inset="true" data-role="listview">';
                    $o .= $this->processActions($resEditMap);
                    $o .= '</ul>';
                    $o .= '<h2>Children Resources</h2>';
                } else {
                    $parent = 0;
                    $o .= '<h2>Resources</h2>';
                }
                $subResources = $this->listResources($modx,$ctx,$parent);
                if (count($subResources) > 0) {
                    $o .= '<ul data-inset="true" data-role="listview">';
                    $o .= $this->processActions($this->listResources($modx,$ctx,$parent));
                    $o .= '</ul>';
                } else {
                    $o .= '<p>This resource does not have any children.</p>';
                }
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
                    'linkparams' => array('ctx' => $ctx->get('key')),
                    'object' => $ctx
                );
            }
            return $contexts;
        }
        
        public function listResources(&$modx,$ctx,$parent = 0) {
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
                    'linkparams' => array('ctx' => $ctx, 'parent' => $res->get('id')),
                    'object' => $ctx
                );
            }
            return $resources;
        }
    }

?>