<?php

namespace HeimrichHannot\FieldValueCopierBundle\Widget;


use HeimrichHannot\Haste\Dca\General;
use HeimrichHannot\Haste\Util\Url;
use HeimrichHannot\Haste\Util\Widget;

class FieldValueCopierWidget extends \Widget
{

    protected $blnForAttribute   = true;
    protected $strTemplate       = 'be_widget_chk';
    protected $strEditorTemplate = 'field_value_copier';
    protected $arrDca;
    protected $arrWidgetErrors   = [];

    public function __construct($arrData)
    {
        \Controller::loadDataContainer($arrData['strTable']);
        $this->arrDca = $GLOBALS['TL_DCA'][$arrData['strTable']]['fields'][$arrData['strField']]['eval']['fieldValueCopier'];

        parent::__construct($arrData);
    }


    /**
     * Generate the widget and return it as string
     *
     * @return string
     */
    public function generate()
    {
        $objTemplate        = new \BackendTemplate($this->strEditorTemplate);
        $objTemplate->class = $this->arrDca['class'];

        \Controller::loadDataContainer('tl_field_value_copier');
        \System::loadLanguageFile('tl_field_value_copier');

        if ($strFieldValue = \Input::get('fieldValue')) {
            $objItem = General::getModelInstance($this->arrDca['table'], $strFieldValue);

            if ($objItem !== null) {
                $strFieldname = $this->arrDca['field'];

                // usage of model not possible since \DataContainer::save() is protected and not callable from here
                \Database::getInstance()->prepare("UPDATE $this->strTable SET $strFieldname = ? WHERE id=?")->execute(
                    $objItem->{$strFieldname},
                    $this->objDca->id
                );
            }

            \Controller::redirect(Url::removeQueryString(['fieldValue']));
        }

        $arrField = $GLOBALS['TL_DCA']['tl_field_value_copier']['fields']['fieldValueCopier'];

        $arrField['label'][0]         =
            sprintf($arrField['label'][0], General::getLocalizedFieldname($this->arrDca['field'], $this->arrDca['table']));
        $arrField['options_callback'] = $this->arrDca['options_callback'];

        $objTemplate->fieldValueCopier = Widget::getBackendFormField($this->strName, $arrField, null, $this->strName, $this->strTable, $this->objDca);

        return $objTemplate->parse();
    }

}