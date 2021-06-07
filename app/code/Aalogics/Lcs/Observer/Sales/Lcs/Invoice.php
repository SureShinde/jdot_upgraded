<?php
/**
 * Pmclain_Twilio extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GPL v3 License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl.txt
 *
 * @category       Pmclain
 * @package        Twilio
 * @copyright      Copyright (c) 2017
 * @license        https://www.gnu.org/licenses/gpl.txt GPL v3 License
 */

namespace Aalogics\Lcs\Observer\Sales\Lcs;

use Magento\Framework\Event\ObserverInterface;

class Invoice implements ObserverInterface
{
    protected $_lcshelper;
    protected $_invoiceSer;
    protected $_invoiceSender;
    protected $_transactions;
    protected $_registry;

    public function __construct(
        \Aalogics\Lcs\Helper\Data $helper,
        \Magento\Sales\Model\Service\InvoiceService $invoiceSer,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Framework\DB\TransactionFactory $transactions,
        \Magento\Framework\Registry $registry
    ) {
        $this->_lcshelper     = $helper;
        $this->_invoiceSer    = $invoiceSer;
        $this->_invoiceSender = $invoiceSender;
        $this->_transactions  = $transactions;
        $this->_registry = $registry;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Framework\Event\Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_lcshelper->debug('execute observer invoice');

        $order            = $observer->getOrder();
        $sendInvoiceEmail = $this->_lcshelper->getAdminField('lcs_inv_shipp_action/invoce_email');

        if ($this->_lcshelper->isEnabled()) {
            $this->_generateInvoice($order, false, $sendInvoiceEmail);
        }

        return $observer;
    }

    public function _generateInvoice(\Magento\Sales\Model\Order $order, $newStatus = false, $email = false)
    {

        $invoice = $this->_invoiceSer->prepareInvoice($order);

        if (!$invoice->getTotalQty()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Cannot create an invoice without products.'));
        }

        //$this->_registry->register('current_invoice', $invoice);
        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);

        $invoice->register();

        $this->_saveAsTransaction(array($order, $invoice));


        if (!$newStatus) {
            $order->addStatusToHistory(false, __('Created Invoice'), $email);
        }

        if ($newStatus) {
            $this->changeOrderStatus(
                $order, $newStatus, __('Created Invoice'), $email
            );
        }

        return $invoice;
    }

    /**
     * Save all objects together in one transaction
     *
     * @param $objects
     *
     * @throws \Exception
     * @throws bool
     */
    protected function _saveAsTransaction($objects)
    {
        $transaction = $this->_transactions->create();
        foreach ($objects as $object) {
            $transaction->addObject($object);
        }
        $transaction->save();
    }

    /**
     * Update order to new status, optional comment
     *
     * @param \Magento\Sales\Model\Order $order
     * @param                        $status
     * @param string                 $comment
     * @param bool                   $hasEmailBeenSent
     *
     * @throws \Exception
     */
    public function changeOrderStatus(\Magento\Sales\Model\Order $order, $status, $comment = '', $hasEmailBeenSent = false)
    {
        $order->addStatusToHistory($status, $comment, $hasEmailBeenSent);
        $order->save();
    }

}
