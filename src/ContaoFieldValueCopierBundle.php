<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FieldValueCopierBundle;

use HeimrichHannot\FieldValueCopierBundle\DependencyInjection\FieldValueCopierExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotContaoFieldValueCopierBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new FieldValueCopierExtension();
    }
}
