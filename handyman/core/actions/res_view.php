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
     
    class res_view extends HandyMan {
        public $meta = array(
            'title' => 'Resource Details'
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
            
            $r = $resource->toArray();
            $r['tplObj'] = $resource->getOne('Template');
            $r['template'] = $r['tplObj']->get('templatename');
            $o .= '<h2>'.$r['pagetitle'].' ('.$r['id'].')</h2>';
            
            /* Will use three sections: resource fields, resource settings 
             * and template variables. These will be styled in an accordeon-ish
             * fashion using collapsible sets.
             ***/
            

            $o .= '<div data-role="collapsible-set">';
            
            // Resource Fields
            $o .= '<div data-role="collapsible"><h3>Resource Fields</h3><ul data-role="listview" data-inset="true">';
            $rfields = array('id', 'template', 'pagetitle', 'longtitle', 'description', 'alias', 'link_attributes', 'introtext', 'parent', 'menutitle', 'menuindex', 'hidemenu', 'content');
            foreach ($rfields as $rf) {
                if (($rf == 'content') && (!empty($r[$rf]))) { $o .= '<li>'.$r[$rf].'</li>'; }
                elseif (!empty($r[$rf])) $o .= '<li>'.$rf.': '.$r[$rf].'</li>';
            }
            $o .= '</ul></div>';
            
            // Resource Settings
            $o .= '<div data-role="collapsible" data-collapsed="true"><h3>Page Settings</h3><ul data-role="listview" data-inset="true">';
            $rfields = array('container', 'richtext', 'publishedon', 'pub_date', 'unpub_date', 'searchable', 'cacheable', 'deleted', 'content_type', 'content_dispo', 'class_key');
            foreach ($rfields as $rf) {
                if (($rf == 'content') && (!empty($r[$rf]))) { $o .= '<li>'.$r[$rf].'</li>'; }
                elseif (!empty($r[$rf])) $o .= '<li>'.$rf.': '.$r[$rf].'</li>';
            }
            $o .= '</ul></div>';

            /*$tpl = $modx->getObject('modTemplate',$r['template']);*/
            $tvs = $r['tplObj']->getMany('modTemplateVar');
            if (count($tvs) > 0) {
                $o .= '<div data-role="collapsible" data-collapsed="true"><h3>Template Variables</h3><ul data-role="listview" data-inset="true">';
                foreach ($tvs as $tv) {
                    $o .= '<li>'.$tv->get('caption').': '.$tv->get('value').'</li>';
                }
                $o .= '</ul></div>';
            }
            
            
            $o .= '</div>';
            
            return $o;
            
        }
        
    }
?>