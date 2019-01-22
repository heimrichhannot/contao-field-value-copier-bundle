<?php

/**
 * Backend form fields
 */
$GLOBALS['BE_FFL']['fieldValueCopier'] = 'HeimrichHannot\FieldValueCopierBundle\Widget\FieldValueCopierWidget';

/**
 * Assets
 */
if (TL_MODE == 'BE')
{
    $GLOBALS['TL_JAVASCRIPT']['jquery.field_value_copier.js'] = 'bundles/contaofieldvaluecopier/js/field_value_copier.be.js';
//    $GLOBALS['TL_CSS']['field_value_copier'] = 'system/modules/field_value_copier/assets/css/field_value_copier.css';
}
