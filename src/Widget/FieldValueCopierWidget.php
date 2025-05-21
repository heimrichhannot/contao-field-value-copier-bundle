<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FieldValueCopierBundle\Widget;

use Contao\BackendTemplate;
use Contao\Controller;
use Contao\Database;
use Contao\Input;
use Contao\System;
use Contao\Widget;
use HeimrichHannot\FieldValueCopierBundle\Util\Polyfill;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FieldValueCopierWidget extends Widget
{
    protected $blnForAttribute = true;

    protected $strTemplate = 'be_widget_chk';

    protected string $strEditorTemplate = 'field_value_copier';

    protected $arrDca;

    protected array $arrWidgetErrors = [];

    protected ContainerInterface $container;

    public function __construct($arrData)
    {
        Controller::loadDataContainer($arrData['strTable']);
        $this->arrDca = $GLOBALS['TL_DCA'][$arrData['strTable']]['fields'][$arrData['strField']]['eval']['fieldValueCopier'];
        $this->container = System::getContainer();

        parent::__construct($arrData);
    }

    /**
     * Generate the widget and return it as string.
     */
    public function generate(): string
    {
        $objTemplate = new BackendTemplate($this->strEditorTemplate);
        $objTemplate->class = $this->arrDca['class'] ?? null;

        $GLOBALS['TL_JAVASCRIPT']['jquery.field_value_copier.js'] = 'bundles/contaofieldvaluecopier/js/field_value_copier.be.js';

        Controller::loadLanguageFile('tl_field_value_copier');

        if (($strFieldValue = Input::get('fieldValue')) && ($this->arrDca['field'] === ($strFieldName = Input::get('fieldName')))) {
            $objItem = $this->container->get(Utils::class)->model()->findModelInstanceByPk($this->arrDca['table'], $strFieldValue);

            if (null !== $objItem) {
                // usage of model not possible since \DataContainer::save() is protected and not callable from here
                Database::getInstance()
                    ->prepare("UPDATE $this->strTable SET $strFieldName = ? WHERE id=?")
                    ->execute($objItem->{$strFieldName}, $this->objDca->id);
            }

            $utils = System::getContainer()->get(Utils::class);

            Controller::redirect($utils->url()->removeQueryStringParameterFromUrl(['fieldName', 'fieldValue']));
        }

        $arrField = [
            'exclude' => true,
            'inputType' => 'select',
            'eval' => [
                'tl_class' => 'long',
                'chosen' => true,
                'includeBlankOption' => true,
            ],
        ];

        $arrField['label'][0] = sprintf(
            $GLOBALS['TL_LANG']['MSC']['tl_field_value_copier']['fieldValueCopierLabel'],
            Polyfill::getLocalizedFieldName($this->arrDca['field'], $this->arrDca['table'])
        );
        $arrField['options_callback'] = $this->arrDca['options_callback'];

        $objTemplate->fieldValueCopier = Polyfill::getBackendFormField($this->strName, $arrField, null, $this->strName, $this->strTable, $this->objDca);
        $objTemplate->baseField = $this->arrDca['field'];

        return $objTemplate->parse();
    }
}
