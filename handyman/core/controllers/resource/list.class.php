<?php
class hmcResourceList extends hmController {
    protected $cache = false;
    protected $templateFile = 'resource/list';
    public $parent;
    public $context;
    public $start;
    public $limit;
    public $list;

    public function getPageTitle() {
        return 'Listing Resources';
    }

    public function setup() {
        if (empty($_REQUEST['ctx'])) {
            $this->redirect('resource/contexts');
        } else {
            $this->context = $_REQUEST['ctx'];
        }
        return true;
    }

    public function process() {
        $parent = (isset($this->config['get']['parent'])) ? (int)$this->config['get']['parent'] : 0;
        $start = (isset($this->config['get']['start'])) ? $this->config['get']['start'] : null;
        $limit = (isset($this->config['get']['limit'])) ? $this->config['get']['limit'] : null;
        $list = (isset($this->config['get']['list'])) ? $this->config['get']['list'] : null;

        if ($parent > 0) {
            /** @var modResource $current */
            $current = $this->modx->getObject('modResource',$parent);
            $this->setPlaceholders($current->toArray());
            
            $pubstate = (boolean)$current->get('published');
            $deleted = (boolean)$current->get('deleted');
            $resEditMap = array(
                array (
                    'action' => 'resource/view',
                    'text' => 'Show details',
                    'linkparams' => array(
                        'ctx' => $this->context,
                        'rid' => $parent
                    ),
                    'icon' => 'grid'
                ),
                array(
                    'action' => 'resource/publish',
                    'text' => ($pubstate) ? 'Unpublish' : 'Publish',
                    'linkparams' => array(
                        'ctx' => $this->context,
                        'rid' => $parent
                    ),
                    'icon' => 'star',
                    'dialog' => true
                ),
                array(
                    'action' => 'resource/update',
                    'text' => 'Update',
                    'linkparams' => array(
                        'ctx' => $this->context,
                        'rid' => $parent
                    ),
                    'icon' => 'gear'
                ),
                array(
                    'action' => 'resource/delete',
                    'text' => ($deleted) ? 'Restore' : 'Delete',
                    'linkparams' => array(
                        'ctx' => $this->context,
                        'rid' => $parent
                    ),
                    'icon' => 'delete',
                    'dialog' => true
                ),
                array(
                    'action' => 'resource/create',
                    'text' => 'Create resource here (coming soon!)',
                    'linkparams' => array(
                        'ctx' => $this->context,
                        'parent' => $parent
                    ),
                    'icon' => 'plus'
                )
            );
            $this->setPlaceholder('actions',$this->processActions($resEditMap));
            $this->setPlaceholder('view',$this->hm->getTpl('resource/list.view',$this->getPlaceholders()));
        } else {
            $parent = 0;
            $this->setPlaceholder('view','');
        }

        $subResources = $this->listResources($parent);
        $resources = '';
        if (count($subResources) > 0) {
            $resources = $this->processActions($subResources);
        }
        $this->setPlaceholder('resources',$resources);
    }
    
    public function listResources($parent = 0) {
        $c = $this->modx->newQuery('modResource');
        $c->where(array(
            'context_key' => $this->context,
            'parent' => $parent,
        ));

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
}