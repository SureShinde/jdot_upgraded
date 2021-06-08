<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace RLTSquare\CustomPrint\Model\Source\Method;

class Generic
{
    /**
     * @var \RLTSquare\CustomPrint\Model\Carrier
     */
    protected $_shippingDhl;

    /**
     * @var string
     */
    protected $_code = '';

    /**
     * @param \RLTSquare\CustomPrint\Model\Carrier $shippingDhl
     */
    public function __construct(\RLTSquare\CustomPrint\Model\Carrier $shippingDhl)
    {
        $this->_shippingDhl = $shippingDhl;
    }

    /**
     * Returns array to be used in multiselect on back-end
     *
     * @return array
     */
    public function toOptionArray()
    {
        $configData = $this->_shippingDhl->getCode($this->_code);
        $arr = [];
        foreach ($configData as $code => $title) {
            $arr[] = ['value' => $code, 'label' => $title];
        }
        return $arr;
    }
}
