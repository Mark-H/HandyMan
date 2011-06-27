<?php
class hmcResourceContexts extends hmController {
    protected $cache = false;
    protected $templateFile = 'resource/contexts';
    public $contexts = array();

    public function getPageTitle() {
        return 'Listing Contexts';
    }

    public function setup() {}

    public function process() {
        $placeholders = array();

        $this->contexts = $this->listContexts();
        if (count($this->contexts) > 1) {
            $placeholders['contexts'] = $this->processActions($this->contexts);
        } elseif (count($this->contexts) == 1) {
            $this->redirect('resource/list',array(
                'ctx' => $this->contexts[0]['object']->get('key'),
            ));
        } else {
            $placeholders['contexts'] = 'Your contexts are messed up.';
        }
        return $placeholders;
    }

    public function listContexts() {
        $c = $this->modx->newQuery('modContext');
        $c->where(array(
            'key:!=' => 'mgr',
        ));
        $contexts = array();
        $contextobjects = $this->modx->getCollection('modContext',$c);
        foreach ($contextobjects as $ctx) {
            $contexts[] = array(
                'action' => 'resource/list',
                'text' => $ctx->get('key'),
                'linkparams' => array('ctx' => $ctx->get('key')),
                'object' => $ctx
            );
        }
        return $contexts;
    }
}