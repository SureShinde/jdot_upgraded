<?php

namespace Arpatech\CustomInvoiceNumber\Controller\Adminhtml\Order\Shipment;

class Save extends \Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save
{
    protected function _saveShipment($shipment)
    {
        $custom_invoice = $this->getRequest()->getParam('shipment_custom_invoice');
        $shipment->getOrder()->setCustomInvoiceNo($custom_invoice);
        $shipment->getOrder()->setIsInProcess(true);


        $transaction = $this->_objectManager->create('Magento\Framework\DB\Transaction');
        $transaction->addObject($shipment
        )->addObject(
            $shipment->getOrder()
        );
        $transaction->save();

        $invoice = $this->_prepareInvoice($shipment->getOrderID());
        $invoice->getOrder()->setIsInProcess(true);
        $transaction->addObject($invoice
        )->addObject(
            $invoice->getOrder()
        );
        $transaction->save();

        return $this;
    }

    protected function _prepareInvoice($orderid)
    {
        //$logger = \Magento\Framework\App\ObjectManager::getInstance()->get('\Psr\Log\LoggerInterface');
        //$logger->debug(__METHOD__ . ' -111- ' . __LINE__);
        /** @var $invoice \Magento\Sales\Model\Order\Invoice */
        $order = $this->_objectManager->create(\Magento\Sales\Model\Order::class)->load($orderid);
        $invoiceService = $this->_objectManager->create(\Magento\Sales\Api\InvoiceManagementInterface::class);
        $invoice = $invoiceService->prepareInvoice($order);
        return $invoice->register();

    }
}
?>