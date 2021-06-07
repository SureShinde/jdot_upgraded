<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Infomodus\Dhllabel\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderPlaceAfter implements ObserverInterface
{
    private $coreRegistry;
    private $handy;
    private $items;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Infomodus\Dhllabel\Helper\Handy $handy,
        \Infomodus\Dhllabel\Model\ItemsFactory $items
    )
    {
        $this->coreRegistry = $registry;
        $this->handy = $handy;
        $this->items = $items;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if (!$order->getId()) {
            //order not saved in the database
            return $this;
        }

        if ($this->handy->_conf
                ->getStoreConfig('dhllabel/frontend_autocreate_label/frontend_order_autocreate_label_enable', $order->getStoreId()) == 1) {
            $storeId = $order->getStoreId();
            $shippingActiveMethods = trim($this->handy->_conf->getStoreConfig('dhllabel/frontend_autocreate_label/apply_to', $storeId), " ,");
            $shippingActiveMethods = strlen($shippingActiveMethods) > 0 ? explode(",", $shippingActiveMethods) : [];
            $orderStatuses = explode(",", trim($this->handy->_conf->getStoreConfig('dhllabel/frontend_autocreate_label/orderstatus', $storeId), " ,"));
            if (((isset($shippingActiveMethods)
                        && count($shippingActiveMethods) > 0
                        && in_array($order->getShippingMethod(), $shippingActiveMethods)
                    )
                    || strpos($order->getShippingMethod(), "dhl_") === 0
                )
                && (
                    isset($orderStatuses)
                    && count($orderStatuses) > 0
                    && in_array($order->getStatus(), $orderStatuses)
                )
            ) {
                $label = $this->items->create()
                    ->getCollection()
                    ->addFieldToFilter('type', 'shipment')
                    ->addFieldToFilter('lstatus', 0)
                    ->addFieldToFilter('order_id', $order->getId());
                if (count($label) == 0) {
                    $shipment = $order->getShipmentsCollection();
                    $shipment_id = null;
                    if (count($shipment) > 0) {
                        $shipment = $shipment->getFirstItem();
                        $shipment_id = $shipment->getId();
                    }
                    $this->handy->intermediate($order->getId(), 'shipment');
                    $this->handy->defConfParams['package'] = $this->handy->defPackageParams;
                    $this->handy->getLabel(null, 'shipment', $shipment_id, $this->handy->defConfParams);
                }
            }
        }

        $this->coreRegistry->unregister('infomodus_dhllabel_autocreate_label');
        return $this;
    }
}
