<?php

use Contao\System;
use HeimrichHannot\UtilsBundle\Util\Utils;

if (!System::getContainer()->get(Utils::class)->container()->isInstall())
{
    $GLOBALS['TL_DCA']['tl_field_value_copier'] = [
        'fields' => [
            'fieldValueCopier' => [
                'exclude'   => true,
                'inputType' => 'select',
                'eval'      => ['tl_class' => 'long', 'chosen' => true, 'includeBlankOption' => true],
            ],
        ],
    ];
}
