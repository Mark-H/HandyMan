<?php
class hmcResourceList extends hmController {
    protected $cache = false;
    protected $templateFile = 'resource/list';
    public $parent;
    public $context;
    public $contexts = array();
    public $start;
    public $limit;
    public $list;

    public function getPageTitle() {
        return 'Listing Resources';
    }

    public function setup() {
        if (empty($this->config['gpc']['ctx'])) {
            $this->context = null;
        } else {
            $this->context = $this->config['gpc']['ctx'];
        }
        $this->modx->lexicon->load('default','resource');
        return true;
    }

    public function process() {
        $parent = (isset($this->config['get']['parent'])) ? (int)$this->config['get']['parent'] : 0;
        $start = (isset($this->config['get']['start'])) ? $this->config['get']['start'] : null;
        $limit = (isset($this->config['get']['limit'])) ? $this->config['get']['limit'] : null;
        $list = (isset($this->config['get']['list'])) ? $this->config['get']['list'] : null;

        /* First make sure we got a context to load from. */
        if ($this->context === null) {
            $this->contexts = $this->listContexts();
            // If we have multiple contexts show the contexts listing and halt the processing here.
            if (count($this->contexts) > 1) {
                $headerRow = array(
                    array(hmController::LIST_DIVIDER => 'Choose a Context')
                );
                $contextMap = array_merge($headerRow,$this->contexts);
                $this->setPlaceholder('contexts',$this->processActions($contextMap));
                $this->templateFile = 'resource/contexts';
                return;
            // If we have exactly one context let's just go with that one and skip a click.
            } elseif (count($this->contexts) == 1) {
                $this->context = $this->contexts[0]['text'];
            }
        }


        if ($parent > 0) {
            /** @var modResource $current */
            $current = $this->modx->getObject('modResource',$parent);
            $this->setPlaceholders($current->toArray());

            /* Set up a breadcrumbs trail */
            $parents = $this->modx->getParentIds($parent, 10, array('context' => $this->context));
            $trail = array();
            foreach ($parents as $p) {
                if ($p > 0) {
                    $obj = $this->modx->getObject('modResource',$p);
                    if ($obj instanceof modResource) {
                        $phs = array_merge($this->placeholders,array('resid' => $p, 'ctx' => $this->context, 'title' => $obj->get('pagetitle')));
                        $trail[] = $this->hm->getTpl('widgets/crumbsli',$phs);
                    }
                }
            }
            $trail[] = $this->hm->getTpl('widgets/crumbsli',array_merge($this->placeholders,array('ctx' => $this->context, 'title' => $this->context)));
            $trail = implode("\n",array_reverse($trail));
            $trail = $this->hm->getTpl('widgets/crumbsouter',array('wrapper' => $trail));
            $this->setPlaceholder('crumbs',$trail);

            $pubstate = (boolean)$current->get('published');
            $deleted = (boolean)$current->get('deleted');
            $resEditMap = array(
                array(
                    hmController::LIST_DIVIDER => $this->modx->lexicon('options'),
                ),
                array (
                    'action' => 'resource/view',
                    'text' => $this->modx->lexicon->exists('resource_overview') ?  $this->modx->lexicon('resource_overview') :  $this->modx->lexicon('resource_view'),
                    'linkparams' => array(
                        'ctx' => $this->context,
                        'rid' => $parent,
                    ),
                    'icon' => 'grid'
                ),
                array(
                    'action' => 'resource/update',
                    'text' => $this->modx->lexicon('resource_edit'),
                    'linkparams' => array(
                        'ctx' => $this->context,
                        'rid' => $parent,
                    ),
                    'icon' => 'gear'
                ),
                array(
                    'action' => 'resource/preview',
                    'text' => $this->modx->lexicon('resource_view'),
                    'linkparams' => array(
                        'rid' => $parent,
                    ),
                    'icon' => 'arrow-r',
                    'target' => '_blank'
                ),
                array(
                    'action' => 'resource/create',
                    'text' => $this->modx->lexicon('document_create_here'),
                    'linkparams' => array(
                        'ctx' => $this->context,
                        'parent' => $parent,
                    ),
                    'icon' => 'plus'
                ),
                array(
                    hmController::LIST_DIVIDER => 'Quick Options',
                ),
                array(
                    'action' => 'resource/publish',
                    'text' => ($pubstate) ? $this->modx->lexicon('resource_unpublish') : $this->modx->lexicon('resource_publish'),
                    'linkparams' => array(
                        'ctx' => $this->context,
                        'rid' => $parent,
                    ),
                    'icon' => 'star',
                    'dialog' => true
                ),
                array(
                    'action' => 'resource/delete',
                    'text' => ($deleted) ? $this->modx->lexicon('resource_undelete') : $this->modx->lexicon('resource_delete'),
                    'linkparams' => array(
                        'ctx' => $this->context,
                        'rid' => $parent
                    ),
                    'icon' => 'delete',
                    'dialog' => true,
                ),
            );
            $this->setPlaceholder('actions',$this->processActions($resEditMap));
            $this->setPlaceholder('view',$this->hm->getTpl('resource/list.view.resource',$this->getPlaceholders()));
        } else {
            $parent = 0;
            $this->setPlaceholder('context_key',$this->context);
            $contextActionMap = array(
                array(
                    hmController::LIST_DIVIDER => $this->modx->lexicon('options'),
                ),
                array(
                    'action' => 'resource/create',
                    'text' => $this->modx->lexicon('document_create_here'),
                    'linkparams' => array(
                        'ctx' => $this->context,
                        'parent' => 0,
                    ),
                    'icon' => 'plus'
                )
            );
            $this->setPlaceholder('actions',$this->processActions($contextActionMap));
            $this->setPlaceholder('view',$this->hm->getTpl('resource/list.view.context',$this->getPlaceholders()));
        }

        $subResources = $this->listResources($parent);
        $resources = '';
        if (count($subResources) > 0) {
            $parentText = ($parent > 0) ? $this->getPlaceholder('pagetitle') : $this->context;
            $header = array(array(hmController::LIST_DIVIDER => 'Listing Resources in '.$parentText));
            $resources = $this->processActions(array_merge($header,$subResources));
        }
        $this->setPlaceholder('resources',$resources);
    }
    
    public function listResources($parent = 0) {
        $c = $this->modx->newQuery('modResource');
        $c->where(array(
            'context_key' => $this->context,
            'parent' => $parent,
        ));
        $c->sortby('menuindex','asc');

        $resources = array();
        $ress = $this->modx->getCollection('modResource',$c);
        foreach ($ress as $res) {
            $aside = array();
            $aside[] = ($res->get('published')) ? 'Published' : 'Unpublished';
            if ($res->get('deleted')) { $aside[] = 'Deleted'; }
            if ($res->get('hidemenu')) { $aside[] = 'Hidden from menu'; }
            $aside = implode(", ",$aside);

            $count = $res->hasChildren();

            $resources[] = array(
                'action' => 'resource/list',
                'text' => $res->get('pagetitle').' ('.$res->get('id').')',
                'aside' => $aside,
                'linkparams' => array('ctx' => $this->context, 'parent' => $res->get('id')),
                'object' => $this->context,
                'count' => $count
            );
        }
        return $resources;
    }
    
    public function listContexts() {
        $c = $this->modx->newQuery('modContext');
        $c->where(array(
            'key:!=' => 'mgr',
        ));
        $contexts = array();
        $contextobjects = $this->modx->getCollection('modContext',$c);
        foreach ($contextobjects as $ctx) {
            $count = $this->modx->getCount('modResource',array('context_key' => $ctx->get('key')));
            $contexts[] = array(
                'action' => 'resource/list',
                'text' => $ctx->get('key'),
                'linkparams' => array('ctx' => $ctx->get('key')),
                'object' => $ctx,
                'count' => $count
            );
        }
        return $contexts;
    }
}