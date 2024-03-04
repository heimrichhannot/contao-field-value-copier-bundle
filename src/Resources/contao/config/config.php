<?php

use Contao\System;

/**
 * ## Backend form fields
 */
$GLOBALS['BE_FFL']['fieldValueCopier'] = 'HeimrichHannot\FieldValueCopierBundle\Widget\FieldValueCopierWidget';


/**
 * ## Assets
 */
(function($scopeMatcher, $requestStack)
{
    if ($scopeMatcher->isBackendRequest($requestStack->getCurrentRequest()))
    {
        $GLOBALS['TL_JAVASCRIPT']['jquery.field_value_copier.js'] = 'bundles/contaofieldvaluecopier/js/field_value_copier.be.js';
        // $GLOBALS['TL_CSS']['field_value_copier'] = 'system/modules/field_value_copier/assets/css/field_value_copier.css';
    }
})(
    System::getContainer()->get('contao.routing.scope_matcher'),
    System::getContainer()->get('request_stack')
);
