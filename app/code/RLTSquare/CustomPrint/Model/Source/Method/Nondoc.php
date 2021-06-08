<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace RLTSquare\CustomPrint\Model\Source\Method;

/**
 * Source model for DHL shipping methods for documentation
 */
class Nondoc extends \RLTSquare\CustomPrint\Model\Source\Method\AbstractMethod
{
    /**
     * Carrier Product Type Indicator
     *
     * @var string $_contentType
     */
    protected $_contentType = \Magento\Dhl\Model\Carrier::DHL_CONTENT_TYPE_NON_DOC;
}
