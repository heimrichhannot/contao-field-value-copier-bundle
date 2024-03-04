<?php

namespace HeimrichHannot\FieldValueCopierBundle\Util;

use Contao\Controller;
use Contao\Environment;
use Contao\PageModel;
use Contao\StringUtil;
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
     * @param string     $fieldName     The field name
     * @param array      $dca           The DCA
     * @param array|null $value
     * @param string $dbField       The database field name
     * @param string $table         The table
     * @param null       $dataContainer object The data container
     *
     * @return Widget|null
     *
     * @internal https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Form/FormUtil.php#L353
     */
    public static function getBackendFormField(
        string $fieldName,
        array $dca,
        array $value = null,
        string $dbField = '',
        string $table = '',
        $dataContainer = null
    ): ?Widget {
        $strClass = $GLOBALS['BE_FFL'][$dca['inputType']];
        if (!$strClass) {
            return null;
        }
        return new $strClass(Widget::getAttributesFromDca($dca, $fieldName, $value, $dbField, $table, $dataContainer));
    }

    /**
     * Prepare URL from ID and keep query string from current string.
     *
     * Options:
     * - absoluteUrl: (boolean) Return absolute url instead of relative url. Only applicable if id or null is given as url. Default: false
     *
     * @param string|int|null Url or page id
     * @param array $options pass additional options
     *
     * @return string
     *
     * @deprecated
     * @codeCoverageIgnore
     */
    public static function prepareUrl($url = null, array $options = []): string
    {
        if (null === $url) {
            if (isset($options['absoluteUrl']) && true === $options['absoluteUrl']) {
                $url = Environment::get('uri');
            } else {
                $url = Environment::get('requestUri');
            }
        } elseif (is_numeric($url)) {
            $framework = System::getContainer()->get('contao.framework');
            /** @var PageModel $jumpTo */
            $jumpTo = $framework->getAdapter(PageModel::class)->findByPk($url);
            if (null === $jumpTo) {
                throw new \InvalidArgumentException('Given page id does not exist.');
            }

            if (isset($options['absoluteUrl']) && true === $options['absoluteUrl']) {
                $url = $jumpTo->getAbsoluteUrl();
            } else {
                $url = $jumpTo->getFrontendUrl();
            }

            [, $queryString] = explode('?', Environment::get('request'), 2);

            if ('' != $queryString) {
                $url .= '?'.$queryString;
            }
        }

        return StringUtil::ampersand($url, false);
    }

    /**
     * Remove query parameters from the current URL.
     *
     * Options:
     * - absoluteUrl: (boolean) Return absolute url instead of relative url. Only applicable if id or null is given as url. Default: false
     *
     * @param array           $params List of parameters to remove from url
     * @param string|int|null $url    Full Uri, Page id or null (for current environment uri)
     *
     * @deprecated Use utils service instead
     * @codeCoverageIgnore
     */
    public static function removeQueryString(array $params, $url = null, array $options = []): string
    {
        $strUrl = static::prepareUrl($url, $options);

        if (empty($params)) {
            return $strUrl;
        }

        $explodedUrl = explode('?', $strUrl, 2);

        if (2 === count($explodedUrl)) {
            [$script, $queryString] = $explodedUrl;
        } else {
            [$script] = $explodedUrl;

            return $script;
        }

        parse_str($queryString, $queries);

        $queries = array_filter($queries);
        $queries = array_diff_key($queries, array_flip($params));

        $href = '';

        if (!empty($queries)) {
            $href .= '?'.http_build_query($queries);
        }

        return $script.$href;
    }
}