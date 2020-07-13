<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FieldValueCopierBundle\Util;

use Contao\DataContainer;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FieldValueCopierUtil
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    public function getOptions(DataContainer $dc)
    {
        if (!($table = $dc->table) || !($field = $dc->field)) {
            return [];
        }

        $dca = $GLOBALS['TL_DCA'][$table]['fields'][$dc->field];

        if (!isset($dca['eval']['fieldValueCopier']['table'])) {
            throw new \Exception("No 'table' set in $dc->table.$dc->field's eval array.");
        }

        $config = [
            'dataContainer' => $dca['eval']['fieldValueCopier']['table'],
        ];

        $config += $dca['eval']['fieldValueCopier']['config'] ?: [];

        $options = $this->container->get('huh.utils.choice.model_instance')->getChoices($config);

        // remove the item itself
        unset($options[$dc->id]);

        return $options;
    }
}
