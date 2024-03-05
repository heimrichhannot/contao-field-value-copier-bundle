<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FieldValueCopierBundle\Util;

use Contao\DataContainer;
use Exception;

class FieldValueCopierUtil
{
    protected ModelInstanceChoicePolyfill $modelInstanceChoicePolyfill;

    public function __construct(
        ModelInstanceChoicePolyfill $modelInstanceChoicePolyfill
    )
    {
        $this->modelInstanceChoicePolyfill = $modelInstanceChoicePolyfill;
    }

    /**
     * @throws Exception
     *
     * @return array
     */
    public function getOptions(DataContainer $dc): array
    {
        if (!($table = $dc->table) || !$dc->field) {
            return [];
        }

        $dca = $GLOBALS['TL_DCA'][$table]['fields'][$dc->field];

        if (!isset($dca['eval']['fieldValueCopier']['table'])) {
            throw new Exception("No 'table' set in $dc->table.$dc->field's eval array.");
        }

        $config = [
            'dataContainer' => $dca['eval']['fieldValueCopier']['table'],
        ];

        $config += $dca['eval']['fieldValueCopier']['config'] ?: [];

        $options = $this->modelInstanceChoicePolyfill->getChoices($config);

        // remove the item itself
        unset($options[$dc->id]);

        return $options;
    }
}
