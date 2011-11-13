<?php
     
class hmcResourceUpdate extends hmController {
    protected $cache = false;
    protected $templateFile = 'resource/update';
    public $useRichtext = false;
    public $allowRichtext = false;

    /** @var modResource $resource */
    public $resource;
    /** @var modTemplate $template */
    public $template;

    /** @var hmInputRenderer $renderer */
    public $renderer;

    public function getPageTitle() {
        if ($this->resource instanceof modResource)
            return $this->modx->lexicon('update') . ' ' . $this->resource->get('pagetitle');
        return $this->modx->lexicon('resource_err_nf');
    }
    public function setup() {
        $this->modx->lexicon->load('default','resource');
        if (empty($this->config['gpc']['rid'])) {
            return $this->modx->lexicon('resource_err_nf');
        }
        $this->resource = $this->modx->getObject('modResource',intval($this->config['gpc']['rid']));
        if (empty($this->resource)) {
            return $this->modx->lexicon('resource_err_nfs',array('id' => intval($this->config['gpc']['rid'])));
        }
        $this->template = $this->resource->getOne('Template');
        return true;
    }

    /**
     * Process this page, load the resource, and present its values
     * @return void
     */
    public function process() {
        $useRichtext = $this->modx->getOption('handyman.useRichtext',null,true);
        if ($useRichtext && $this->resource->get('richtext')) {
            $this->allowRichtext = true;
            if (intval($this->config['gpc']['nort']))
                $this->setPlaceholder('richtextStatus',2);
            else
                $this->setPlaceholder('richtextStatus',1);
        } else {
            $this->setPlaceholder('richtextStatus',0);
        }

        $this->setPlaceholders($this->resource->toArray());

        $this->modx->loadClass('hmInputRenderer',$this->hm->config['classesPath'],true,true);
        $this->renderer = new hmInputRenderer($this->hm,$this->resource->toArray());
        
        $clearCache = array('type' => 'boolean','name' => 'clearCache','title' => $this->modx->lexicon('clear_cache_on_save'),'value' => true);
        $clearCache = $this->renderer->render('boolean',$clearCache);
        $this->setPlaceholder('clearCache',$clearCache);

        $content = array('type' => 'richtext', 'name' => 'content', 'title' => $this->modx->lexicon('resource_content'), 'value' => $this->resource->get('content'));
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
            'published' => array('type' => 'boolean'),
            'template' => array('type' => 'select', 'options' => $tplOptions),
            'pagetitle' => array('type' => 'text'),
            'longtitle' => array('type' => 'text'),
            'description' => array('type' => 'text'),
            'alias' => array('type' => 'text'),
            'link_attributes' => array('type' => 'text'),
            'introtext' => array('type' => 'textarea'),
            'parent' => array('type' => 'text'),
            'menutitle' => array('type' => 'text'),
            'menuindex' => array('type' => 'text'),
            'hidemenu' => array('type' => 'boolean', 'title' => $this->modx->lexicon('resource_hide_from_menus')),
        );

        $list = array();
        foreach ($fields as $name => $details) {
            $details['title'] = (($details['title']) ? $details['title'] : (($this->modx->lexicon->exists($name)) ? $this->modx->lexicon($name) : $this->modx->lexicon('resource_'.$name)));
            $details['name'] = $name;
            $details['value'] = $this->resource->get($name);
            $list[$name] = $this->renderer->render($details['type'],$details);
        }
        $this->setPlaceholder('fields',implode("\n",$list));
    }

    public function getResourceSettings() {
        $fields = array(
            'richtext' => array('type' => 'flipswitch'),
            'isfolder' => array('type' => 'flipswitch', 'title' => $this->modx->lexicon('resource_folder')),
            'pub_date' => array('type' => 'text', 'title' => $this->modx->lexicon('resource_publishdate')),
            'unpub_date' => array('type' => 'text', 'title' => $this->modx->lexicon('resource_unpublishdate')),
            'searchable' => array('type' => 'boolean'),
            'cacheable' => array('type' => 'boolean'),
            'deleted' => array('type' => 'boolean'),
            // This does not included: publishedon, empty cache (done separately later on), content type,
            //      content disposition, class key and freeze_uri (2.1+). Don't think it's needed.
        );

        $list = array();
        foreach ($fields as $name => $details) {
            $details['title'] = (($details['title']) ? $details['title'] : (($this->modx->lexicon->exists($name)) ? $this->modx->lexicon($name) : $this->modx->lexicon('resource_'.$name)));
            $details['name'] = $name;
            $details['value'] = $this->resource->get($name);
            $list[$name] = $this->renderer->render($details['type'],$details);
        }
        $this->setPlaceholder('settings',implode("\n",$list));
    }

    /**
     * Get all the Template Variables for this Resource
     * @return void
     */
    public function getTemplateVariables() {
        $tvObjs = modResource::getTemplateVarCollection($this->resource);
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
