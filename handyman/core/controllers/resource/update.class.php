<?php
     
class hmcResourceUpdate extends hmController {
    protected $cache = false;
    protected $templateFile = 'resource/update';

    /** @var modResource $resource */
    public $resource;
    /** @var modTemplate $template */
    public $template;

    /** @var hmInputRenderer $renderer */
    public $renderer;

    public function getPageTitle() {
        if ($this->resource instanceof modResource)
            return 'Updating: '.$this->resource->get('pagetitle');
        return 'Resource not found';
    }
    public function setup() {
        if (empty($_REQUEST['rid'])) {
            return 'No valid resource ID passed.';
        }
        $this->resource = $this->modx->getObject('modResource',intval($_REQUEST['rid']));
        if (empty($this->resource)) {
            return 'Resource not found.';
        }
        $this->template = $this->resource->getOne('Template');
        $this->modx->lexicon->load('default','resource');
        return true;
    }

    /**
     * Process this page, load the resource, and present its values
     * @return void
     */
    public function process() {
        $this->setPlaceholders($this->resource->toArray());

        $this->modx->loadClass('hmInputRenderer',$this->hm->config['modelPath'],true,true);
        $this->renderer = new hmInputRenderer($this->hm,$this->resource->toArray());
        
        $clearCache = array('type' => 'boolean','name' => 'clearcache','title' => 'Clear cache on save?','value' => true);
        $clearCache = $this->renderer->render('boolean',$clearCache);
        $this->setPlaceholder('clearCache',$clearCache);

        $content = array('type' => 'richtext', 'name' => 'content', 'value' => $this->resource->get('content'));
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
            'hidemenu' => array('type' => 'boolean'),
        );

        $list = array();
        foreach ($fields as $name => $details) {
            $details['title'] = ($this->modx->lexicon->exists($name)) ? $this->modx->lexicon($name) : $this->modx->lexicon('resource_'.$name);
            $details['name'] = $name;
            $details['value'] = $this->resource->get($name);
            $list[$name] = $this->renderer->render($details['type'],$details);
        }
        $this->setPlaceholder('fields',implode("\n",$list));
    }

    public function getResourceSettings() {
        $fields = array(
            'richtext' => array('type' => 'flipswitch'),
            'isfolder' => array('type' => 'flipswitch'),
            'pub_date' => array('type' => 'text'),
            'unpub_date' => array('type' => 'text'),
            'searchable' => array('type' => 'boolean'),
            'cacheable' => array('type' => 'boolean'),
            'deleted' => array('type' => 'boolean'),
            // This does not included: publishedon, empty cache (done separately later on), content type,
            //      content disposition, class key and freeze_uri (2.1+). Don't think it's needed.
        );

        $list = array();
        foreach ($fields as $name => $details) {
            $details['title'] = ($this->modx->lexicon->exists($name)) ? $this->modx->lexicon($name) : $this->modx->lexicon('resource_'.$name);
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
                        $tvs['Uncategorized'][] = $tv;
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
            $this->modx->loadClass('hmTvInputRenderer',$this->hm->config['modelPath'],true,true);
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
     * Create a field for a TV type
     * @param modTemplateVar $tv
     * @return string
     */
    public function createTemplateVarField(modTemplateVar $tv) {
        $value = $tv->get('value');
        switch($tv->get('display')) {
            default:
            case 'default':
                break;
        }
        $type = 'text';
        switch ($tv->get('type')) {
            case 'checkbox':
                
            default:
            case 'text':
                break;
        }

        $options = array();
        $tvArray = $tv->toArray();
        return $this->createField($type,'tv'.$tvArray['id'],$tvArray['caption'],$value,$options);
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
