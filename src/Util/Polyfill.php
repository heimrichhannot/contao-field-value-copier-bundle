<?php

namespace HeimrichHannot\FieldValueCopierBundle\Util;

use Contao\Controller;
use Contao\Environment;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use Error;
use Exception;

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
     * Retrieves an array from a dca config (in most cases eval) in the following priorities:.
     *
     * 1. The value associated to $array[$property]
     * 2. The value retrieved by $array[$property . '_callback'] which is a callback array like ['Class', 'method'] or ['service.id', 'method']
     * 3. The value retrieved by $array[$property . '_callback'] which is a function closure array like ['Class', 'method']
     *
     * @return mixed|null The value retrieved in the way mentioned above or null
     *
     * @internal https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Dca/DcaUtil.php#L375
     */
    public static function getConfigByArrayOrCallbackOrFunction(array $array, $property, array $arguments = [])
    {
        if (isset($array[$property])) {
            return $array[$property];
        }

        if (!isset($array[$property.'_callback'])) {
            return null;
        }

        if (is_array($array[$property.'_callback'])) {
            $callback = $array[$property.'_callback'];

            if (!isset($callback[0]) || !isset($callback[1])) {
                return null;
            }

            try {
                $instance = Controller::importStatic($callback[0]);
            } catch (Exception $e) {
                return null;
            }

            if (!method_exists($instance, $callback[1])) {
                return null;
            }

            try {
                return call_user_func_array([$instance, $callback[1]], $arguments);
            } catch (Error $e) {
                return null;
            }
        } elseif (is_callable($array[$property.'_callback'])) {
            try {
                return call_user_func_array($array[$property.'_callback'], $arguments);
            } catch (Error $e) {
                return null;
            }
        }

        return null;
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