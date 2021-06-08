<?php

namespace RLTSquare\TcsShipping\Model;


/**
 * Class OrderManager
 * @package RLTSquare\TcsShipping\Model
 */
class OrderManager
{

    /**
     * @var \Magento\Framework\Event\Manager
     */
    private $eventManager;

    /**
     * @var \RLTSquare\TcsShipping\Helper\Data
     */
    protected $tcsHelper;

    /**
     * OrderManager constructor.
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param \RLTSquare\TcsShipping\Helper\Data $tcsHelper
     */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        \RLTSquare\TcsShipping\Helper\Data $tcsHelper

    ) {

        $this->tcsHelper = $tcsHelper;
        $this->eventManager = $eventManager;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function invoiceAndShip(\Magento\Sales\Model\Order $order)
    {
        $this->tcsHelper->log('invoiceAndShipAll Start');
        $message = [];
        if (!empty($order)) {
            $orderIncrementId = $order->getIncrementId();

            try {

                /******** Create Order Shipment *********/
                if ($order->canShip()) {
                    $this->tcsHelper->log('invoiceAndShipAll Ship Start');
                    $this->eventManager->dispatch('rltsquare_tcs_ship', ['order' => $order]);
                }

                /****** Create Order Invoice ******/
                if ($order->canInvoice()) {
                    $this->tcsHelper->log('invoiceAndShipAll Invoice Start');
                    $this->eventManager->dispatch('rltsquare_tcs_invoice', ['order' => $order]);
                }

                $message['success'] = $orderIncrementId . ": Invoiced and Shipped";

            } catch (\Exception $e) {
                $this->tcsHelper->log('Exception: ', $e->getMessage());
                $message['error'] = $orderIncrementId . ": " . $e->getMessage();
            }
        }
        return $message;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function shipOrder(\Magento\Sales\Model\Order $order)
    {
        $message = '';
        $this->tcsHelper->log('order Ship Start');
        if (!empty($order)) {

            $orderIncrementId = $order->getIncrementId();
            try {
                /******** Create Order Shipment *********/
                if ($order->canShip()) {
                    $this->tcsHelper->log('Shipping Start');
                    $this->eventManager->dispatch('rltsquare_tcs_ship', ['order' => $order]);
                }
                $message = $orderIncrementId . ": Shipped";

            } catch (\Exception $e) {
                $this->tcsHelper->log('Exception: ', $e->getMessage());
                $message = $orderIncrementId . ": " . $e->getMessage();
            }
        }
        return $message;
    }
}
