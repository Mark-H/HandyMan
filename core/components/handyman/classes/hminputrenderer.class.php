<?php
/**
 * @package handyman
 */
class hmInputRenderer {
    /** @var HandyMan $hm */
    public $hm;
    /** @var modX $modx */
    public $modx;
    public $data;
    public $useRichtext = false;
    public $allowRichtext = false;

    /**
     * @param \HandyMan $hm
     * @param array $data
     * @return \hmInputRenderer
     */
    function __construct(HandyMan &$hm, $data = array()) {
        $this->hm =& $hm;
        $this->modx =& $hm->modx;
        $this->data = $data;
        if (count($this->data) == 0) {
            $this->data['richtext'] = $this->modx->getOption('richtext_default',null,true);
        }

        $useRichtext = $this->modx->getOption('handyman.useRichtext',null,true);
        if ($useRichtext && $this->data['richtext']) {
            $this->allowRichtext = true;
            if (intval($_REQUEST['nort']))
                $this->useRichtext = false;
            else
                $this->useRichtext = true;
        }
    }

    /**
     * @param string $type
     * @param xPDOObject|array $field
     * @return string
     */
    public function render($type,$field) {
        switch ($type) {
            case 'flipswitch':
            case 'boolean':
                $type = 'boolean';
            break;
            case 'options':
            case 'dropdown':
            case 'select':
                $type = 'select';
            break;
            case 'richtext':
                if ($this->allowRichtext && $this->useRichtext)
                    $type = 'richtext';
                else
                    $type = 'textarea';
            break;
            default: 
                break;
        }
        $method = $this->_exists($type);
        if ($method) {
            $field = $this->$method($field);
        }
        $field = is_object($field) ? $field->toArray() : $field;
        return $this->output($type,$field);
    }

    /**
     * @param string $type
     * @param array $field
     * @return string
     */
    protected function output($type,array $field) {
        /* ensure that quotes are escaped for field values */
        if (is_string($field['value'])) {
            $field['value'] = str_replace(array('"','[',']'),array('&quot;','&#91;','&#93;'),$field['value']);
        }
        return $this->hm->getTpl('fields/'.$type,$field);
    }

    /**
     * Checks if a method exists in this class.
     * 
     * @param $type
     * @return bool|string
     */
    protected function _exists($type) {
        $method = 'prepare'.ucfirst($type);
        return method_exists($this,$method) ? $method : false;
    }

    /**
     * @param xPDOObject|array $field
     * @return array
     */
    public function prepareText($field) {
        return $field;
    }

    /**
     * @param xPDOObject|array $field
     * @return array
     */
    public function prepareBoolean($field) {
        $field = is_object($field) ? $field->toArray() : $field;
        $field['yes'] = ($field['value'] == 1) ? ' selected="selected" ' : '';
        $field['no'] = ($field['value'] == 1) ? '' : ' selected="selected" ';
        return $field;
    }

    /**
     * @param xPDOObject|array $field
     * @return array
     */
    public function prepareSelect($field) {
        $field = is_object($field) ? $field->toArray() : $field;
        $optionList = array();
        if (is_array($field['options'])) {
            foreach ($field['options'] as $opt) {
                $sel = ($opt['value'] == $field['value']) ? ' selected="selected" ' : '';
                $optionList[] = $this->hm->getTpl('fields/select.option',array(
                    'value' => $opt['value'],
                    'name' => $opt['name'],
                    'selected' => $sel,
                ));
            }
        }
        $field['options'] = implode("\n",$optionList);
        return $field;
    }

    /**
     * Prepares richtext fields.
     *
     * @param $field
     * @return array
     */
    public function prepareRichtext($field) {
        $field = is_object($field) ? $field->toArray() : $field;
        $this->hm->modx->getService('h2t','html2textile',$this->hm->config['corePath'].'classes/textile/');
        $field['value'] = $this->hm->modx->h2t->detextile($field['value']);
        return $field;
    }
}