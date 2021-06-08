<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace RLTSquare\CustomPrint\Model\Source\Method;

/**
 * Source model for DHL shipping methods for documentation
 */
class Unitofmeasure extends \RLTSquare\CustomPrint\Model\Source\Method\Generic
{
    /**
     * Carrier code
     *
     * @var string
     */
    protected $_code = 'unit_of_measure';
}
