<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2016-10-20T12:16:27+00:00
 * File:          app/code/Xtento/OrderExport/Model/Export/Condition/Address/Billing.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Condition\Address;

use Xtento\OrderExport\Model\Export\Condition\ObjectCondition;

class Billing extends ObjectCondition
{
    public function loadAttributeOptions()
    {
        $attributes = [
            'postcode' => __('Billing Postcode'),
            'region' => __('Billing Region'),
            'region_id' => __('Billing State/Province'),
            'country_id' => __('Billing Country'),
        ];

        $this->setAttributeOption($attributes);
        return $this;
    }

    /**
     * Validate Address Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        $address = $object;
        if (!$address instanceof \Magento\Sales\Model\Order\Address) {
            $address = $object->getBillingAddress();
        }

        return $this->validateAttribute($address->getData($this->getAttribute()));
    }
}
