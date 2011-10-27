<?php
class hmcResourceCreateSave extends hmController {

    protected $cache = false;
    protected $templateFile = 'resource/create.save';
    protected $viewType = hmController::VIEW_DIALOG;

    /** @var modResource $resource */
    public $resource;
    /** @var modTemplate $template */
    public $template;

    public function getPageTitle() {
        return $this->modx->lexicon('resource_new');
    }
    public function setup() {
        return true;
    }

    /**
     * Process this page, load the resource, and present its values
     * @return void
     */
    public function process() {
        $data = $this->processInput($_REQUEST);

        /* @var modProcessorResponse $response */
        $response = $this->modx->runProcessor('resource/create',$data);
        
        if (!$response->isError()) {
            $tempRes = $response->getObject();
            $this->resource = $this->modx->getObject('modResource',$tempRes['id']);

            /* Make sure the createdby column is set */
            $cb = $this->resource->get('createdby');
            if (empty($cb)) {
                $this->resource->set('createdby',$this->modx->user->get('id'));
                $this->resource->save();
            }
            $this->setPlaceholders(
                array(
                    'message' => 'Resource created.',
                    'resid' => $this->resource->get('id'),
                    'ctx' => $this->resource->get('context_key'),
                )
            );
        } else {
            $error = $response->getAllErrors();
            $this->setPlaceholder('message','Something went wrong creating the Resource: '.implode(', ',$error));
        }
    }

    /**
     * Process POSTed data for richtext.
     * @param array $data
     * @return array
     */
    public function processInput($data) {
        foreach ($data as $key => $value) {
            /* If richtext, parse using textile */
            if (substr($key,-9) == '-richtext') {
                $this->hm->modx->getService('t2h','textile',$this->hm->config['corePath'].'classes/textile/');
                $data[substr($key,0,-9)] = $this->hm->modx->t2h->TextileThis($value);
                unset ($data[$key]);
            }
        }

        /* If no context_key passed, default to web. */
        if (empty($data['context_key'])) { $data['context_key'] = 'web'; $this->modx->log(modX::LOG_LEVEL_ERROR,'Defaulting to web'); }

        return $data;
    }
}