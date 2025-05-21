<?php

namespace HeimrichHannot\FieldValueCopierBundle\Util;

use Contao\Controller;
use Contao\System;
use Contao\Widget;

class Polyfill
{
    /**
     * @internal https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Dca/DcaUtil.php#L1015
     */
    public static function getLocalizedFieldName($strField, $strTable)
    {
        Controller::loadDataContainer($strTable);
        System::loadLanguageFile($strTable);

        return $GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['label'][0] ?: $strField;
    }

    /**
     * Get an instance of Widget by passing field name and dca data.
     *
     * @param string $fieldName     The field name
     * @param array  $dca           The DCA
     * @param string $dbField       The database field name
     * @param string $table         The table
     * @param null   $dataContainer object The data container
     *
     * @internal https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Form/FormUtil.php#L353
     */
    public static function getBackendFormField(
        string $fieldName,
        array $dca,
        ?array $value = null,
        string $dbField = '',
        string $table = '',
        $dataContainer = null,
    ): ?Widget {
        $strClass = $GLOBALS['BE_FFL'][$dca['inputType']];
        if (!$strClass) {
            return null;
        }

        return new $strClass(Widget::getAttributesFromDca($dca, $fieldName, $value, $dbField, $table, $dataContainer));
    }
}
