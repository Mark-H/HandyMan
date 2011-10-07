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

    /** @var modResource $resource */
    public $resource;
    /** @var modTemplate $template */
    public $template;
    
    public function getPageTitle() {
        return 'Resource Details - HandyMan';
    }
    public function setup() {
        if (empty($_REQUEST['rid'])) {
            return 'No valid resource id passed.';
        }
        $this->resource = $this->modx->getObject('modResource',intval($_REQUEST['rid']));
        if (empty($this->resource)) {
            return 'Resource not found.';
        }
        $this->modx->lexicon->load('default','resource');
        return true;
    }

    /**
     * Process this page, load the resource, and present its values
     * @return void
     */
    public function process() {
        $this->setPlaceholders($this->resource->toArray());

        $this->template = $this->resource->getOne('Template');
        if ($this->template) {
            $this->setPlaceholder('template',$this->template->get('templatename'));
        }

        $this->getContent();
        $this->getResourceFields();
        $this->getResourceSettings();
        
        if ($this->template) {
            $this->getTemplateVariables();
        }
    }

    /**
     * Get, process and encode the content of this Resource
     * @return void
     */
    public function getContent() {
        $content = $this->resource->getContent();
        $this->setPlaceholder('content',$this->hm->getTpl('widgets/text',array(
            'text' => $this->safe($content,true),
        )));
    }

    /**
     * Get all the main Resource Fields for this Resource
     * @return void
     */
    public function getResourceFields() {
        $fields = array();
        $rfields = array('id', 'template', 'pagetitle', 'longtitle', 'description', 'alias', 'link_attributes', 'introtext', 'parent', 'menutitle', 'menuindex', 'hidemenu');
        foreach ($rfields as $fieldName) {
            $text = '';
            $fieldValue = $this->getPlaceholder($fieldName);
            if (!empty($fieldValue)) {
                $lexstring = ($this->modx->lexicon->exists($fieldName)) ? $this->modx->lexicon($fieldName) : $this->modx->lexicon('resource_'.$fieldName);
                $text = $lexstring.': '.$fieldValue;
            }
            if (!empty($text)) {
                $fields[] = $this->hm->getTpl('widgets/simpleli',array(
                    'text' => $this->safe($text),
                ));
            }
        }
        $this->setPlaceholder('resourceFields',implode("\n",$fields));
    }

    /**
     * Make MODX/HTML safe a string of content
     * @param string $string
     * @param bool $raw
     * @return mixed
     */
    public function safe($string, $raw = false) {
        $string = ($raw) ? $string : htmlentities($string);
        return str_replace(array('[',']'),array('&#91;','&#93;'),$string);
    }

    /**
     * Get all the Resource Settings for this Resource
     * @return void
     */
    public function getResourceSettings() {
        $rfields = array('container', 'richtext', 'publishedon', 'pub_date', 'unpub_date', 'searchable', 'cacheable', 'deleted', 'content_type', 'content_dispo', 'class_key');
        $settings = array();
        foreach ($rfields as $fieldName) {
            $text = '';
            $fieldValue = $this->getPlaceholder($fieldName);
            if ($fieldValue !== null) {
                $lexstring = ($this->modx->lexicon->exists($fieldName)) ? $this->modx->lexicon($fieldName) : $this->modx->lexicon('resource_'.$fieldName);
                $text = $lexstring.': '.$fieldValue;
            }
            if (!empty($text)) {
                $settings[] = $this->hm->getTpl('widgets/simpleli',array(
                    'text' => $this->safe($text),
                ));
            }
        }
        $this->setPlaceholder('pageSettings',implode("\n",$settings));
    }

    /**
     * Get all Template Variables for this Resource
     * @return void
     */
    public function getTemplateVariables() {
        $c = $this->modx->newQuery('modTemplateVar');
        $c->query['distinct'] = 'DISTINCT';
        $c->select($this->modx->getSelectColumns('modTemplateVar', 'modTemplateVar'));
        $c->select($this->modx->getSelectColumns('modCategory', 'Category', 'cat_', array('category')));
        $c->select($this->modx->getSelectColumns('modTemplateVarResource', 'TemplateVarResource', '', array('value')));
        $c->select($this->modx->getSelectColumns('modTemplateVarTemplate', 'TemplateVarTemplate', '', array('rank')));
        $c->leftJoin('modCategory','Category');
        $c->innerJoin('modTemplateVarTemplate','TemplateVarTemplate',array(
            'TemplateVarTemplate.tmplvarid = modTemplateVar.id',
            'TemplateVarTemplate.templateid' => $this->template->id,
        ));
        $c->leftJoin('modTemplateVarResource','TemplateVarResource',array(
            'TemplateVarResource.tmplvarid = modTemplateVar.id',
            'TemplateVarResource.contentid' => $this->resource->id,
        ));
        $c->sortby('cat_category,TemplateVarTemplate.rank,modTemplateVar.rank','ASC');
        $tvs = $this->modx->getCollection('modTemplateVar',$c);
        if (count($tvs) > 0) {
            $templateVariables = array();
            /** @var modTemplateVar $tv */
            foreach ($tvs as $tv) {
                 $templateVariables[] = $this->hm->getTpl('widgets/simpleli',array(
                    'text' => $tv->get('caption').': '.$tv->get('value'),
                ));
            }
            $this->setPlaceholder('tvs',implode("\n",$templateVariables));
        }
    }
}