<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FieldValueCopierBundle\Util;

use Contao\BackendUser;
use Contao\Calendar;
use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\Database;
use Contao\DataContainer;
use Contao\Date;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use HeimrichHannot\EventsBundle\Model\CalendarEventsModel;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FieldValueCopierUtil implements FrameworkAwareInterface, ContainerAwareInterface
{
    use FrameworkAwareTrait;
    use ContainerAwareTrait;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function getOptions(DataContainer $dc)
    {
        if (!($table = $dc->table) || !($field = $dc->field)) {
            return [];
        }

        $dca = $GLOBALS['TL_DCA'][$table]['fields'][$dc->field];

        if (!isset($dca['eval']['fieldValueCopier']['table'])) {
            throw new \Exception("No 'table' set in $dc->table.$dc->field's eval array.");
        }

        if (null === ($items = $this->container->get('huh.utils.model')->findModelInstancesBy(
                $dca['eval']['fieldValueCopier']['table'],
                $dca['eval']['fieldValueCopier']['column'] ?: [],
                $dca['eval']['fieldValueCopier']['value'] ?: [],
                $dca['eval']['fieldValueCopier']['options'] ?: []
            )))
        {
            return [];
        }

        $label = $GLOBALS['TL_LANG']['MSC']['tl_field_value_copier']['record'];

        $options = array_combine($items->fetchEach('id'), array_map(function ($val) use ($label) {
            return $label . $val;
        }, $items->fetchEach('id')));

        // remove the item itself
        unset($options[$dc->id]);

        return $options;
    }
}