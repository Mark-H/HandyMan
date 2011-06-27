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
     
class hmcResourceView extends hmController {
    protected $cache = false;
    protected $templateFile = 'resource/view';
    
    public function getPageTitle() {
        return 'Resource Details';
    }
    public function setup() {}

    public function process() {
        $placeholders = array();
        $o = '';

        if (empty($_REQUEST['rid'])) {
            $this->hm->end('No valid resource id passed.');
        } else {
            $rid = $_REQUEST['rid'];
        }

        $resource = $this->modx->getObject('modResource',$rid);
        if (empty($resource)) {
            $this->hm->end('Resource not found.');
        }
        $r = $resource->toArray();
        $placeholders = array_merge($placeholders,$r);
        $r['tplObj'] = $resource->getOne('Template');
        if ($r['tplObj']) {
            $r['template'] = $r['tplObj']->get('templatename');
        }

        /* Will use three sections: resource fields, resource settings
         * and template variables. These will be styled in an accordeon-ish
         * fashion using collapsible sets.
         ***/

        // Content

        $content = $resource->getContent();
        $placeholders['content'] = $this->hm->getTpl('widgets/simpleli',array(
            'text' => htmlentities($content),
        ));

        // Resource Fields
        $fields = array();
        $rfields = array('id', 'template', 'pagetitle', 'longtitle', 'description', 'alias', 'link_attributes', 'introtext', 'parent', 'menutitle', 'menuindex', 'hidemenu');
        foreach ($rfields as $rf) {
            $text = '';
            if (!empty($r[$rf])) {
                $text = $r[$rf];
            } elseif (!empty($r[$rf])) {
                $text = $rf.': '.$r[$rf];
            }
            if (!empty($text)) {
                $fields[] = $this->hm->getTpl('widgets/simpleli',array(
                    'text' => htmlentities($text),
                ));
            }
        }
        $placeholders['resourceFields'] = implode("\n",$fields);

        // Resource Settings
        $rfields = array('container', 'richtext', 'publishedon', 'pub_date', 'unpub_date', 'searchable', 'cacheable', 'deleted', 'content_type', 'content_dispo', 'class_key');
        $settings = array();
        foreach ($rfields as $rf) {
            $text = '';
            if (!empty($r[$rf])) {
                $text = $r[$rf];
            } elseif (!empty($r[$rf])) {
                $text = $rf.': '.$r[$rf];
            }
            if (!empty($text)) {
                $settings[] = $this->hm->getTpl('widgets/simpleli',array(
                    'text' => $text,
                ));
            }
        }
        $placeholders['pageSettings'] = implode("\n",$settings);

        /* TVs! */
        $placeholders['tvs'] = '';
        if ($r['tplObj']) {
            $c = $this->modx->newQuery('modTemplateVar');
            $c->query['distinct'] = 'DISTINCT';
            $c->select($this->modx->getSelectColumns('modTemplateVar', 'modTemplateVar'));
            $c->select($this->modx->getSelectColumns('modCategory', 'Category', 'cat_', array('category')));
            $c->select($this->modx->getSelectColumns('modTemplateVarResource', 'TemplateVarResource', '', array('value')));
            $c->select($this->modx->getSelectColumns('modTemplateVarTemplate', 'TemplateVarTemplate', '', array('rank')));
            $c->leftJoin('modCategory','Category');
            $c->innerJoin('modTemplateVarTemplate','TemplateVarTemplate',array(
                'TemplateVarTemplate.tmplvarid = modTemplateVar.id',
                'TemplateVarTemplate.templateid' => $r['tplObj']->id,
            ));
            $c->leftJoin('modTemplateVarResource','TemplateVarResource',array(
                'TemplateVarResource.tmplvarid = modTemplateVar.id',
                'TemplateVarResource.contentid' => $resource->id,
            ));
            $c->sortby('cat_category,TemplateVarTemplate.rank,modTemplateVar.rank','ASC');
            $tvs = $this->modx->getCollection('modTemplateVar',$c);

            if (count($tvs) > 0) {
                $templateVariables = array();
                foreach ($tvs as $tv) {
                    $templateVariables[] = $this->hm->getTpl('widgets/simpleli',array(
                        'text' => $tv->get('caption').': '.$tv->get('value'),
                    ));
                }
                $placeholders['tvs'] = implode("\n",$templateVariables);
            }
        }

        return $placeholders;

    }

}