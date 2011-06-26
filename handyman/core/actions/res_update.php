<?php
    /* HandyMan - a Mobile Manager for MODX 
     *
     * Copyright 2010-2011 by Mark Hamstra (contact via www.markhamstra.nl)
     *
     * This file is part of HandyMan, a Mobile Manager for MODX.
     *
     * HandyMan is free software; you can redistribute it and/or modify it under the
     * terms of the GNU General Public License as published by the Free Software
     * Foundation; either version 2 of the License, or (at your option) any later
     * version.
     *
     * HandyMan is distributed in the hope that it will be useful, but WITHOUT ANY
     * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
     * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License along with
     * HandyMan; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
     * Suite 330, Boston, MA 02111-1307 USA
     *
     * @package HandyMan
     ***/
     
    class res_update extends HandyMan {
        public $meta = array(
            'title' => 'Update Resource',
            'cache' => false
        );
        function __construct() {
            
        }
        public function run($options = array(),&$modx) {   
            $o = '';
            
            if (is_numeric($options['get']['rid'])) {
                $rid = $options['get']['rid'];
            } else { 
                return 'No valid resource id passed.';
            }
            
            $resource = $modx->getObject('modResource',$rid);
         
            if (empty($resource)) {
                return 'Resource not found.';
            }
            
            $r = $resource->toArray();
            $r['tplObj'] = $resource->getOne('Template');
            $o .= '<h2>Updating '.$r['pagetitle'].' ('.$r['id'].')</h2>';
            
            /* Will use three sections: resource fields, resource settings 
             * and template variables. These will be styled in an accordeon-ish
             * fashion using collapsible sets.
             ***/

            $o .= '<form action="'. $this->webroot . 'index.php?hma=res_update_save" method="post" data-transition="pop">
                <input type="hidden" name="id" value="' . $rid .'" />
                <input type="hidden" name="context_key" value="' . $r['context_key'] . '" />
                <div data-role="collapsible-set">
                <div data-role="collapsible"><h3>Resource Fields</h3>';

            $fields = array();

            /* Template options  */
            $tpls = $modx->getCollection('modTemplate');
            foreach ($tpls as $tpl) {
                $tplOptions[] = array(
                    'name' => $tpl->get('templatename'),
                    'value' => $tpl->get('id')
                );
            }

            unset ($tpl, $tpls, $tplPrep);

            $fld = array(
                'published' => array('name' => 'Published','type' => 'flipswitch'),
                'template' => array('name' => 'Template', 'type' => 'select', 'options' => $tplOptions, 'active' => $r['template']),
                'pagetitle' => array('name' => 'Title','type' => 'text'),
                'longtitle' => array('name' => 'Long Title','type' => 'text'),
                'description' => array('name' => 'Description','type' => 'text'),
                'alias' => array('name' => 'Resource Alias','type' => 'text'),
                'link_attributes' => array('name' => 'Link Attributes','type' => 'text'),
                'introtext' => array('name' => 'Summary (introtext)','type' => 'textarea'),
                'parent' => array('name' => 'Parent Resource','type' => 'text'),
                'menutitle' => array('name' => 'Menu Title','type' => 'text'),
                'menuindex' => array('name' => 'Menu Index','type' => 'text'),
                'hidemenu' => array('name' => 'Hide From Menus','type' => 'flipswitch'),
            );

            foreach ($fld as $cf => $cfarr) {
                $fields[$cf] = $this->createFieldMarkup($cf, $cfarr, $r);
            }

            $o .= implode("\n",$fields);
            unset ($fld,$fields);
            $o .= '</div>';

            $o .= '<div data-role="collapsible"><h3>Content</h3>';
            $o .= <<<EOD
<div data-role="fieldcontain">
    <textarea name="content" id="upd_content" rows="20" cols="40">$r[content]</textarea>
</div>
EOD;

            $o .= '</div>'; // Close collapsible

            $o .= '<div data-role="collapsible"><h3>Resource Settings</h3>';
            $fld = array(
                'isfolder' => array('name' => 'Container','type' => 'flipswitch'),
                'pub_date' => array('name' => 'Publish date','type' => 'text'),
                'unpub_date' => array('name' => 'Unpublish date','type' => 'text'),
                'searchable' => array('name' => 'Searchable','type' => 'flipswitch'),
                'cacheable' => array('name' => 'Cacheable','type' => 'flipswitch'),
                'clearCache' => array('name' => 'Empty Cache','type' => 'flipswitch'),
                'deleted' => array('name' => 'Deleted','type' => 'flipswitch'),
            );

            // This little workaround will make sure the cache gets cleared
            $fields = array();
            foreach ($fld as $cf => $cfarr) {
                $fields[$cf] = $this->createFieldMarkup($cf, $cfarr, $r);
            }
            $o .= implode("\n",$fields);
            unset ($fld,$fields);

            $o .= '</div>'; // Close collapsible

            $o .= '</div>'; // Close collapsible set

            $o .= '<button type="submit" name="submit" id="upd_submit" value="Save" data-rel="dialog"></button>';

            $o .= '</form>'; // Close form
            return $o;
            
        }

        public function createFieldMarkup(string $cf, array $cfarr, $r = array()) {
            $cfname = $cfarr['name'];
            switch ($cfarr['type']) {
                case 'flipswitch':
                    $yes = ($r[$cf] == 1) ? ' selected="selected" ' : '';
                    $no = ($r[$cf] == 1) ? '' : ' selected="selected" ';
                    return <<<EOD
                    <div data-role="fieldcontain">
                        <label for="upd_$cf">$cfname</label>
                        <select name="$cf" id="upd_$cf" data-role="slider">
                            <option value="0"$no>No</option>
                            <option value="1"$yes>Yes</option>
                        </select>
                    </div>
EOD;
                    break;
                case 'select':
                    $opts = '';
                    if (is_array($cfarr['options'])) {
                        foreach ($cfarr['options'] as $opt) {
                            $sel = ($opt['value'] == $cfarr['active']) ? ' selected="selected" ' : '';
                            $opts .= '<option value="' . $opt['value'] . '"' . $sel . '>' . $opt['name'] . '</option>';
                        }
                    }
                    return <<<EOD
                    <div data-role="fieldcontain">
                        <label for="upd_$cf">$cfname</label>
                        <select name="$cf" id="upd_$cf">
                            $opts
                        </select>
                    </div>
EOD;
                    break;
                case 'textarea':
                    return <<<EOD
                    <div data-role="fieldcontain">
                        <label for="upd_$cf">$cfname</label>
                        <textarea id="upd_$cf" name="$cf" rows="8" cols="40">$r[$cf]</textarea>
                    </div>
EOD;
                    break;
                case 'text':
                default:
                    return <<<EOD
                    <div data-role="fieldcontain">
                        <label for="upd_$cf">$cfname</label>
                        <input type="text" value="$r[$cf]" id="upd_$cf" name="$cf" />
                    </div>
EOD;
                    break;
            }
            return 'Unknown field type.';
        }
        
    }
?>