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
     *
     * @return string
     */
    public function generate()
    {
        $objTemplate = new BackendTemplate($this->strEditorTemplate);
        $objTemplate->class = $this->arrDca['class'] ?? null;

        Controller::loadDataContainer('tl_field_value_copier');
        Controller::loadLanguageFile('tl_field_value_copier');

        if (($strFieldValue = Input::get('fieldValue')) && ($this->arrDca['field'] === ($strFieldname = Input::get('fieldName')))) {
            $objItem = $this->container->get('huh.utils.model')->findModelInstanceByPk($this->arrDca['table'], $strFieldValue);

            if (null !== $objItem) {
                // usage of model not possible since \DataContainer::save() is protected and not callable from here
                Database::getInstance()->prepare("UPDATE $this->strTable SET $strFieldname = ? WHERE id=?")->execute(
                    $objItem->{$strFieldname},
                    $this->objDca->id
                );
            }

            Controller::redirect($this->container->get('huh.utils.url')->removeQueryString(['fieldValue', 'fieldName']));
        }

        $arrField = $GLOBALS['TL_DCA']['tl_field_value_copier']['fields']['fieldValueCopier'];

        $arrField['label'][0] =
            sprintf($GLOBALS['TL_LANG']['MSC']['tl_field_value_copier']['fieldValueCopierLabel'], $this->container->get('huh.utils.dca')->getLocalizedFieldName($this->arrDca['field'], $this->arrDca['table']));
        $arrField['options_callback'] = $this->arrDca['options_callback'];

        $objTemplate->fieldValueCopier = $this->container->get('huh.utils.form')->getBackendFormField($this->strName, $arrField, null, $this->strName, $this->strTable, $this->objDca);
        $objTemplate->baseField = $this->arrDca['field'];

        return $objTemplate->parse();
    }
}
