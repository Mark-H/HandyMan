<?php

class hmcResourceCreate extends hmController {
    protected $cache = false;
    protected $templateFile = 'resource/create';

    /** @var hmInputRenderer $renderer */
    public $renderer;
    /** @var modTemplate $template */
    public $template;

    public function getPageTitle() {
        return $this->modx->lexicon('resource_new');
    }
    public function setup() {
        $this->template = $this->hm->modx->getObject('modTemplate',$this->hm->modx->getOption('default_template'));
        $this->modx->lexicon->load('default','resource');
        return true;
    }

    /**
     * Process this page, load the resource, and present its values
     * @return void
     */
    public function process() {
        $this->modx->loadClass('hmInputRenderer',$this->hm->config['classesPath'],true,true);
        $this->renderer = new hmInputRenderer($this->hm,array());
        
        $clearCache = array('type' => 'boolean','name' => 'clearCache','title' => $this->modx->lexicon('clear_cache_on_save'),'value' => true);
        $clearCache = $this->renderer->render('boolean',$clearCache);
        $this->setPlaceholder('clearCache',$clearCache);

        $content = array('type' => 'richtext', 'name' => 'content', 'title' => $this->modx->lexicon('resource_content'), 'value' => '');
        $content = $this->renderer->render('richtext',$content);
        $this->setPlaceholder('content',$content);

        $this->getResourceFields();
        $this->getResourceSettings();
        $this->getTemplateVariables();
    }

    /**
     * Get all resource fields
     * @return void
     */
    public function getResourceFields() {
        $tplOptions = $this->getTemplateList();

        $fields = array(
            'published' => array('type' => 'boolean', 'value' => $this->modx->getOption('publish_default')),
            'template' => array('type' => 'select', 'options' => $tplOptions, 'value' => $this->template->get('id')),
            'pagetitle' => array('type' => 'text'),
            'longtitle' => array('type' => 'text'),
            'description' => array('type' => 'text'),
            'alias' => array('type' => 'text'),
            'link_attributes' => array('type' => 'text'),
            'introtext' => array('type' => 'textarea'),
            'parent' => array('type' => 'text', 'value' => $_REQUEST['parent']),
            'context_key' => array('type' => 'hidden', 'value' => ($_REQUEST['ctx']) ? $_REQUEST['ctx'] : 'web'),
            'menutitle' => array('type' => 'text'),
            'menuindex' => array('type' => 'text'),
            'hidemenu' => array('type' => 'boolean', 'value' => $this->modx->getOption('hidemenu_default'), 'title' => $this->modx->lexicon('resource_hide_from_menus')),
        );

        $list = array();
        foreach ($fields as $name => $details) {
            $details['title'] = (($details['title']) ? $details['title'] : (($this->modx->lexicon->exists($name)) ? $this->modx->lexicon($name) : $this->modx->lexicon('resource_'.$name)));
            $details['name'] = $name;
            $list[$name] = $this->renderer->render($details['type'],$details);
        }
        $this->setPlaceholder('fields',implode("\n",$list));
    }

    public function getResourceSettings() {
        $fields = array(
            'richtext' => array('type' => 'flipswitch', 'value' => $this->hm->modx->getOption('richtext_default')),
            'isfolder' => array('type' => 'flipswitch', 'value' => false, 'title' => $this->modx->lexicon('resource_folder')),
            'pub_date' => array('type' => 'text', 'title' => $this->modx->lexicon('resource_publishdate')),
            'unpub_date' => array('type' => 'text', 'title' => $this->modx->lexicon('resource_unpublishdate')),
            'searchable' => array('type' => 'boolean', 'value' => $this->hm->modx->getOption('search_default')),
            'cacheable' => array('type' => 'boolean', 'value' => $this->hm->modx->getOption('cache_default')),
            'deleted' => array('type' => 'boolean', 'value' => false),
            // This does not included: publishedon, empty cache (done separately later on), content type,
            //      content disposition, class key and freeze_uri (2.1+). Don't think it's needed.
        );

        $list = array();
        foreach ($fields as $name => $details) {
            $details['title'] = (($details['title']) ? $details['title'] : (($this->modx->lexicon->exists($name)) ? $this->modx->lexicon($name) : $this->modx->lexicon('resource_'.$name)));
            $details['name'] = $name;
            $list[$name] = $this->renderer->render($details['type'],$details);
        }
        $this->setPlaceholder('settings',implode("\n",$list));
    }

    /**
     * Get all the Template Variables for this Resource
     * @return void
     */
    public function getTemplateVariables() {
        $tvObjs = $this->template->getTemplateVars();
        $tvs = array();
        $categories = array();
        /** @var modTemplateVar $tv */
        foreach ($tvObjs as $tv) {
            if ($tv instanceof modTemplateVar) {
                $tvArray = $tv->toArray();
                if (!empty($categories[$tvArray['category']]))
                    $tvs[$categories[$tvArray['category']]][] = $tv;
                else {
                    if ($tvArray['category'] == 0) {
                        $tvs[$this->modx->lexicon('uncategorized')][] = $tv;
                    }
                    else {
                        $cat = $tv->getOne('Category');
                        if ($cat instanceof modCategory) {
                            $categories[$tvArray['category']] = $cat->get('category');
                            $tvs[$categories[$tvArray['category']]][] = $tv;
                        }
                    }
                }
            }
        }

        $list = array();
        if (count($tvs) > 0) {
            $this->modx->loadClass('hmTvInputRenderer',$this->hm->config['classesPath'],true,true);
            $renderer = new hmTvInputRenderer($this->hm);

            foreach ($tvs as $categoryName => $categoryTemplateVariables) {
                $tvList = array();
                /** @var modTemplateVar $tv */
                foreach ($categoryTemplateVariables as $tv) {
                    $tvList[] = $renderer->render($tv->get('display'),$tv);
                }
                $list[] = $this->hm->getTpl('fields/tvs/category',array(
                    'name' => $categoryName,
                    'collapsed' => (!isset($notFirst) && count($tvs != 1)) ? 'data-collapsed="false"' : 'data-collapsed="true"',
                    'tvs' => implode("\n",$tvList),
                ));

                // This makes sure the first section is opened if there are > 1 sections
                $notFirst = true;
            }
            unset ($notFirst);
        }
        $this->setPlaceholder('tvs',implode("\n",$list));
    }

    /**
     * Get a list of options for a Template dropdown
     * @return array
     */
    public function getTemplateList() {
        $c = $this->modx->newQuery('modTemplate');
        $c->sortby('templatename','ASC');
        $templates = $this->modx->getCollection('modTemplate',$c);
        $tplOptions = array();
        /** @var modTemplate $template */
        foreach ($templates as $template) {
            $tplOptions[] = array(
                'name' => $template->get('templatename'),
                'value' => $template->get('id'),
            );
        }
        return $tplOptions;
    }
}
