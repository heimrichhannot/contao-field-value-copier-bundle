<?php

$GLOBALS['TL_DCA']['tl_field_value_copier'] = [
    'fields' => [
        'fieldValueCopier' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_field_value_copier']['fieldValueCopier'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => ['tl_class' => 'long', 'chosen' => true, 'includeBlankOption' => true],
        ],
    ],
];