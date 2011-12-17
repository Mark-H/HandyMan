<?php

$s = array(
    'Paths' => array(
        'core_path' => '',
        'path' => '',
        'url' => '',
    ),'Customization' => array(
        'templates' => 'default',
        'theme' => 'default',
        'useRichtext' => false,
    ),
);

$settings = array();
foreach ($s as $area => $sets) {
    foreach ($sets as $key => $value) {
        if (is_string($value) || is_int($value)) { $type = 'textfield'; }
        elseif (is_bool($value)) { $type = 'combo-boolean'; }
        else { $type = 'textfield'; }

        $settings['handyman.'.$key] = $modx->newObject('modSystemSetting');
        $settings['handyman.'.$key]->set('key', 'handyman.'.$key);
        $settings['handyman.'.$key]->fromArray(array(
            'value' => $value,
            'xtype' => $type,
            'namespace' => 'handyman',
            'area' => $area
        ));
    }
}

return $settings;


