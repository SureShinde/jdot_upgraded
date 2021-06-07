<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Invoice tracking control form
 */
namespace Arpatech\CustomInvoiceNumber\Block\Adminhtml\Order;

class CustomInvoice  extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }


    public function getHello(){
        return "Foo Bar Baz";
    }
    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getShipment()
    {
        return $this->_coreRegistry->registry('current_shipment');
    }
    public function getCustomInvoiceNumber()
    {
        return $this->getShipment()->getOrder()->getCustomInvoiceNo();
    }
    public function isReadonly(){
        $custominvoiceno = $this->getCustomInvoiceNumber();
        if(!empty($custominvoiceno))
            return true;
        return false;
    }

}
