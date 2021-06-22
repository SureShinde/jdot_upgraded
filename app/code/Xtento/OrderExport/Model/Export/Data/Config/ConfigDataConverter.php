<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2017-11-27T20:02:21+00:00
 * File:          app/code/Xtento/OrderExport/Model/Export/Data/Config/ConfigDataConverter.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Config;

class ConfigDataConverter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert($source)
    {
        $classes = [];
        foreach ($source->getElementsByTagName('export') as $exportClass) {
            $id = $exportClass->getAttribute('id');
            $classes[$id] = [
                'class' => $exportClass->getAttribute('class'),
                'profile_ids' => !empty($exportClass->getAttribute('profile_ids')) ? $exportClass->getAttribute('profile_ids') : false
            ];
        }
        return [
            'classes' => $classes,
        ];
    }
}
