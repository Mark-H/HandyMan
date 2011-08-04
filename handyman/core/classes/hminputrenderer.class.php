<?php
/**
 * @package handyman
 */
class hmInputRenderer {
    /** @var HandyMan $hm */
    public $hm;
    /** @var modX $modx */
    public $modx;

    function __construct(HandyMan &$hm) {
        $this->hm =& $hm;
        $this->modx =& $modx;
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
            default: break;
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
            $field['value'] = str_replace('"','&quot;',$field['value']);
        }
        return $this->hm->getTpl('fields/'.$type,$field);
    }

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
}