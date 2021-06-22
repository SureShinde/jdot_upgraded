<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2016-03-01T16:15:41+00:00
 * File:          app/code/Xtento/OrderExport/Model/System/Config/Source/Log/Result.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\System\Config\Source\Log;

use Magento\Framework\Option\ArrayInterface;

/**
 * @codeCoverageIgnore
 */
class Result implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $values = [
            \Xtento\OrderExport\Model\Log::RESULT_NORESULT => __('No Result'),
            \Xtento\OrderExport\Model\Log::RESULT_SUCCESSFUL => __('Successful'),
            \Xtento\OrderExport\Model\Log::RESULT_WARNING => __('Warning'),
            \Xtento\OrderExport\Model\Log::RESULT_FAILED => __('Failed')
        ];
        return $values;
    }
}
