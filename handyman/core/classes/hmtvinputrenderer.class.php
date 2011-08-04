<?php
require_once dirname(__FILE__).'/hminputrenderer.class.php';

class hmTvInputRenderer extends hmInputRenderer {

    function __construct(HandyMan &$hm) {
        $this->hm =& $hm;
        $this->modx =& $modx;
    }

    /**
     * @param string $type
     * @param modTemplateVar $tv
     * @return string
     */
    public function render($type,$tv) {

        $type = $tv->get('type');

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
            case 'option':
                $type = 'radio'; break;
            case 'default':
                $type = 'text';
                break;
        }

        $method = $this->_exists($type);
        if ($method) {
            $tv = $this->$method($tv);
        } else {
            $type = 'text';
        }
        $tv = is_object($tv) ? $tv->toArray() : $tv;
        $tv['name'] = 'tv'.$tv['id'];
        $tv['title'] = $tv['caption'];
        return $this->output($type,$tv);
    }

    /**
     * @param modTemplateVar $tv
     * @return modTemplateVar
     */
    public function prepareCheckbox($tv) {
        $value = explode("||",$tv->get('value'));

        $default = explode("||",$tv->get('default_text'));

        $options = $tv->parseInputOptions($tv->processBindings($tv->get('elements'),$tv->get('name')));

        $items = array();
        $defaults = array();
        $i = 0;
        foreach ($options as $option) {
            $opt = explode("==",$option);
            if (!isset($opt[1])) $opt[1] = $opt[0];

            /* set checked status */
            $checked = in_array($opt[1],$value) ? ' checked="checked"' : '';
            
            /* add checkbox id to defaults if is a default value */
            if (in_array($opt[1],$default)) {
                $defaults[] = 'tv'.$tv->get('id').'-'.$i;
            }

            $items[] = array(
                'text' => htmlspecialchars($opt[0],ENT_COMPAT,'UTF-8'),
                'name' => 'tv'.$tv->get('id'),
                'idx' => $i,
                'value' => $opt[1],
                'checked' => $checked,
            );
            $i++;
        }
        $list = array();
        foreach ($items as $item) {
            $list[] = $this->hm->getTpl('fields/checkbox.option',$item);
        }

        $tv->set('options',implode("\n",$list));
        return $tv;
    }
    /**
     * @param modTemplateVar $tv
     * @return modTemplateVar
     */
    public function prepareRadio($tv) {

        $value = $tv->get('value');
        $default = $tv->get('default_text');

        // handles radio buttons
        $options = $tv->parseInputOptions($tv->processBindings($tv->get('elements'),$tv->get('name')));
        $items = array();
        $defaultIndex = '';
        $i = 0;
        foreach ($options as $option) {
            $opt = explode("==",$option);
            if (!isset($opt[1])) $opt[1] = $opt[0];
            
            /* set checked status */
            $checked = strcmp($opt[1],$value) == 0 ? ' checked="checked"' : '';
            
            /* set default value */
            if (strcmp($opt[1],$default) == 0) {
                $defaultIndex = 'tv'.$tv->get('id').'-'.$i;
                $tv->set('default_text',$defaultIndex);
            }

            $items[] = array(
                'text' => htmlspecialchars($opt[0],ENT_COMPAT,'UTF-8'),
                'name' => 'tv'.$tv->get('id'),
                'value' => $opt[1],
                'checked' => $checked,
                'idx' => $i,
            );

            $i++;
        }

        $list = array();
        foreach ($items as $item) {
            $list[] = $this->hm->getTpl('fields/radio.option',$item);
        }
        $tv->set('options',implode("\n",$list));
        return $tv;
    }
}