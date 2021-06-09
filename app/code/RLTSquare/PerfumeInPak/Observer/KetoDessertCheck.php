<?php

namespace RLTSquare\PerfumeInPak\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\StateException;

class KetoDessertCheck implements ObserverInterface
{
    protected $_objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $state = $this->_objectManager->get('Magento\Framework\App\State');
        if ($state->getAreaCode() == 'adminhtml') {
            $order = $observer->getEvent()->getOrder();
            if (is_object($order)) {
                $shippingaddres = $order->getShippingAddress();
                if (is_object($shippingaddres)) {
                    $countryCode = $shippingaddres->getCountryId();
                    $isKetoInUS = true;
                    foreach ($order->getAllItems() as $item) {
                        $productID = $item->getProduct()->getId();
                        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productID);
                        $myAttributeValue = $product->getData('perfume_is_saleable');
                        if ($myAttributeValue == 1 && $countryCode != 'PK') {
                            $isKetoInUS = false;
                        }
                    }
                    if ($isKetoInUS == false) {
                        throw new StateException(__('Perfume can\'t shipped outside the PK .'));
                    }
                }
            }
        }
    }
}