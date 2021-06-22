<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2017-11-27T20:04:32+00:00
 * File:          app/code/Xtento/OrderExport/Model/Export/Settings/ConfigDataConverter.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Settings;

class ConfigDataConverter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert($source)
    {
        $settings = [];
        foreach ($source->getElementsByTagName('setting') as $setting) {
            $name = $setting->getAttribute('name');
            $settings[$name] = $setting->nodeValue;
        }
        return $settings;
    }
}
