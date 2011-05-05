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
            $ctx = (isset($options['get']['ctx'])) ? $options['get']['ctx'] : null;
            $parent = (isset($options['get']['parent'])) ? (int)$options['get']['parent'] : 0;
            $start = (isset($options['get']['start'])) ? $options['get']['start'] : null;
            $limit = (isset($options['get']['limit'])) ? $options['get']['limit'] : null;
            $list = (isset($options['get']['list'])) ? $options['get']['list'] : null;
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
            } // End if (!isset($ctx))

            if (!empty($ctx)) {
                if ($parent > 0) {
                    $current = $modx->getObject('modResource',$parent);
                    $o = '<h2>'.$current->get('pagetitle').' ('.$current->get('id').')</h2>';
                    $pubstate = (boolean)$current->get('published');
                    $deleted = (boolean)$current->get('deleted');
                    $resEditMap = array(
                        array (
                            'action' => 'res_view',
                            'linktext' => 'Show details',
                            'linkparams' => array(
                                'ctx' => $ctx,
                                'rid' => $parent
                            ),
                            'icon' => 'grid',
                            'reset' => true
                        ),
                        array(
                            'action' => 'res_publish',
                            'linktext' => ($pubstate) ? 'Unpublish' : 'Publish',
                            'linkparams' => array(
                                'ctx' => $ctx,
                                'rid' => $parent
                            ),
                            'icon' => 'star',
                            'dialog' => true
                        ),
                        array(
                            'action' => 'res_modify',
                            'linktext' => 'Modify (coming soon!)',
                            'linkparams' => array(
                                'ctx' => $ctx,
                                'rid' => $parent
                            ),
                            'icon' => 'gear'
                        ),
                        array(
                            'action' => 'res_delete',
                            'linktext' => ($deleted) ? 'Restore' : 'Delete',
                            'linkparams' => array(
                                'ctx' => $ctx,
                                'rid' => $parent
                            ),
                            'icon' => 'delete',
                            'dialog' => true
                        ),
                        array(
                            'action' => 'res_create',
                            'linktext' => 'Create resource here (coming soon!)',
                            'linkparams' => array(
                                'ctx' => $ctx,
                                'parent' => $parent
                            ),
                            'icon' => 'plus'
                        )
                    );
                    $o .= '<ul data-inset="true" data-role="listview">';
                    $o .= $this->processActions($resEditMap);
                    $o .= '</ul>';
                    $o .= '<h2>Children Resources</h2>';
                } else {
                    $parent = 0;
                    $o = '<h2>Resources</h2>';
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
                $aside = array();
                $aside[] = ($res->get('published')) ? 'Published' : 'Unpublished';
                if ($res->get('deleted')) { $aside[] = 'Deleted'; }
                if ($res->get('hidemenu')) { $aside[] = 'Hidden from menu'; }
                $aside = implode(", ",$aside);
                $resources[] = array(
                    'action' => 'resourcelist',
                    'linktext' => $res->get('pagetitle').' ('.$res->get('id').')',
                    'aside' => $aside,
                    'linkparams' => array('ctx' => $ctx, 'parent' => $res->get('id')),
                    'object' => $ctx,
                    'count' => count($res->getMany('Children'))
                );
                //if ($res->getMany('Children')) { $resources['count'] = count($res->getMany('Children')); }
            }
            return $resources;
        }
    }

?>