<?php
class hmcResourceUpdateSave extends hmController {

    protected $cache = false;
    protected $templateFile = 'dialog';
    protected $viewType = hmController::VIEW_DIALOG;

    /** @var modResource $resource */
    public $resource;
    /** @var modTemplate $template */
    public $template;

    public function getPageTitle() {
        return 'Saving: '.$this->resource->get('pagetitle');
    }
    public function setup() {
        if (empty($_REQUEST['id'])) {
            return 'No valid resource id passed.';
        }
        $this->resource = $this->modx->getObject('modResource',intval($_REQUEST['id']));
        if (empty($this->resource)) {
            return 'Resource not found.';
        }
        $this->template = $this->resource->getOne('Template');
        return true;
    }

    /**
     * Process this page, load the resource, and present its values
     * @return void
     */
    public function process() {
        $data = $this->processInput($_REQUEST);
        $this->resource->fromArray($data);

        // Find & parse any submitted TVs
        foreach ($data as $key => $value) {
            if (substr($key,0,2) == 'tv') {
                if (is_array($value)) {
                    $value = implode('||',$value);
                }
                if (!$this->resource->setTVValue((int)substr($key,2),$value)) {
                    //return 'Error saving Template Variable '.substr($key,2);
                }
            }
        }
        $saved = $this->resource->save();


        if ($_REQUEST['clearcache'] == 1) {
            $this->modx->cacheManager->refresh(array(
                'db' => array(),
                'auto_publish' => array('contexts' => array($this->resource->get('context_key'))),
                'context_settings' => array('contexts' => array($this->resource->get('context_key'))),
                'resource' => array('contexts' => array($this->resource->get('context_key'))),
            ));
        }

        if ($saved) {
            $this->setPlaceholder('message','Resource saved.');
        } else {
            $this->setPlaceholder('message','An error occurred while saving the Resource.');
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
        return $data;
    }
}