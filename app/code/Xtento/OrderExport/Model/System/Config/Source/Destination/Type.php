<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2015-08-09T14:37:03+00:00
 * File:          app/code/Xtento/OrderExport/Model/System/Config/Source/Destination/Type.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\System\Config\Source\Destination;

use Magento\Framework\Option\ArrayInterface;

/**
 * @codeCoverageIgnore
 */
class Type implements ArrayInterface
{
    /**
     * @var \Xtento\OrderExport\Model\Destination
     */
    protected $destinationModel;

    /**
     * @param \Xtento\OrderExport\Model\Destination $destinationModel
     */
    public function __construct(\Xtento\OrderExport\Model\Destination $destinationModel)
    {
        $this->destinationModel = $destinationModel;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->destinationModel->getTypes();
    }

    public function getName($type) {
        foreach ($this->toOptionArray() as $optionType => $name) {
            if ($optionType == $type) {
                return $name;
            }
        }
        return '';
    }
}
