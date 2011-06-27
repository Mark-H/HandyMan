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
                <div data-role="collapsible" data-collapsed="true"><h3>Resource Fields</h3>';

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
                $fields[$cf] = $this->createFieldMarkup($cf,$cfarr['name'],$cfarr['type'],$r[$cf],$cfarr['options']);
            }

            $o .= implode("\n",$fields);
            unset ($fld,$fields);
            $o .= '</div>';

            $o .= '<div data-role="collapsible" data-collapsed="true"><h3>Content</h3>';
            $o .= <<<EOD
<div data-role="fieldcontain">
    <textarea name="content" id="upd_content" rows="20" cols="40">$r[content]</textarea>
</div>
EOD;

            $o .= '</div>'; // Close collapsible

            $o .= '<div data-role="collapsible" data-collapsed="true"><h3>Resource Settings</h3>';
            $fld = array(
                'isfolder' => array('name' => 'Container','type' => 'flipswitch'),
                'pub_date' => array('name' => 'Publish date','type' => 'text'),
                'unpub_date' => array('name' => 'Unpublish date','type' => 'text'),
                'searchable' => array('name' => 'Searchable','type' => 'flipswitch'),
                'cacheable' => array('name' => 'Cacheable','type' => 'flipswitch'),
                'deleted' => array('name' => 'Deleted','type' => 'flipswitch'),
                // This does not included: publishedon, empty cache (done seperately later on), content type,
                //      content disposition, class key and freeze_uri (2.1+). Don't think it's needed.
            );

            $fields = array();
            foreach ($fld as $cf => $cfarr) {
                $fields[$cf] = $this->createFieldMarkup($cf,$cfarr['name'],$cfarr['type'],$r[$cf],$cfarr['options']);
            }
            $o .= implode("\n",$fields);
            unset ($fld,$fields);

            $o .= '</div>'; // Close collapsible

            /* Template Variables */
            $tvObjs = $resource->getTemplateVarCollection($resource);
            $tvs = array();
            $categories = array();
            foreach ($tvObjs as $tv) {
                if ($tv instanceof modTemplateVar) {
                    $tvArray = $tv->toArray();
                    if (!empty($categories[$tvArray['category']]))
                        $tvs[$categories[$tvArray['category']]][] = $tvArray;
                    else {
                        if ($tvArray['category'] == 0) {
                            $tvs['Uncategorized'][] = $tvArray;
                        }
                        else {
                            $cat = $tv->getOne('Category');
                            if ($cat instanceof modCategory) {
                                $categories[$tvArray['category']] = $cat->get('category');
                                $tvs[$categories[$tvArray['category']]][] = $tvArray;
                            }
                        }
                    }
                }
            }

            if (count($tvs) > 0) {
                $o .= '<div data-role="collapsible" data-collapsed="true"><h3>Template Variables</h3>
                    <div data-role="collapsible-set">';
                foreach ($tvs as $catname => $category) {
                    // This makes sure the first section is opened if there are > 1 sections
                    $collapsed = (!isset($notfirst) && count($tvs != 1)) ? 'data-collapsed="false"' : 'data-collapsed="true"';
                    $o .= '<div data-role="collapsible" '.$collapsed.'><h5>' . $catname .  '</h5>';
                    foreach ($category as $tv) {
                        $o .= $this->createTemplateVarFieldMarkup($tv);
                    }
                    $o .= '</div>';
                    $notfirst = true;
                }
                unset ($notfirst);
                $o .= '</div>';
            }

            $o .= '</div>';

            // Add a flipswitch to decide whether or not the cache should be cleared.
            $o .= $this->createFieldMarkup('clearcache','Clear cache on save?','flipswitch',1);
            $o .= '<button type="submit" name="submit" id="upd_submit" value="Save" data-rel="dialog"></button>';

            $o .= '</form>'; // Close form
            return $o;
            
        }

        public function createTemplateVarFieldMarkup(array $tv) {
            switch($tv['display']) {
                default: 
                case 'default':
                    $value = $tv['value'];
                    break;
            }
            switch ($tv['type']) {
                default:
                case 'text':
                    $type = 'text';
            }

            $options = array();
            return $this->createFieldMarkup('tv'.$tv['id'],$tv['caption'],$type,$value,$options);
        }

        public function createFieldMarkup(string $fieldname, string $displayname, $type = 'text', string $value, $options = array()) {
            switch ($type) {
                case 'flipswitch':
                    $yes = ($value == 1) ? ' selected="selected" ' : '';
                    $no = ($value == 1) ? '' : ' selected="selected" ';
                    return <<<EOD
                    <div data-role="fieldcontain">
                        <label for="upd_$fieldname">$displayname</label>
                        <select name="$fieldname" id="upd_$fieldname" data-role="slider">
                            <option value="0"$no>No</option>
                            <option value="1"$yes>Yes</option>
                        </select>
                    </div>
EOD;
                    break;
                case 'select':
                    $opts = '';
                    if (is_array($options)) {
                        foreach ($options as $opt) {
                            $sel = ($opt['value'] == $value) ? ' selected="selected" ' : '';
                            $opts .= '<option value="' . $opt['value'] . '"' . $sel . '>' . $opt['name'] . '</option>';
                        }
                    }
                    return <<<EOD
                    <div data-role="fieldcontain">
                        <label for="upd_$fieldname">$displayname</label>
                        <select name="$fieldname" id="upd_$fieldname">
                            $opts
                        </select>
                    </div>
EOD;
                    break;
                case 'textarea':
                    return <<<EOD
                    <div data-role="fieldcontain">
                        <label for="upd_$fieldname">$displayname</label>
                        <textarea id="upd_$fieldname" name="$fieldname" rows="8" cols="40">$value</textarea>
                    </div>
EOD;
                    break;
                case 'text':
                default:
                    return <<<EOD
                    <div data-role="fieldcontain">
                        <label for="upd_$fieldname">$displayname</label>
                        <input type="text" value="$value" id="upd_$fieldname" name="$fieldname" />
                    </div>
EOD;
                    break;
            }
            return 'Unknown field type.';
        }
        
    }
?>