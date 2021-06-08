<?php

namespace RLTSquare\TcsShipping\Observer\Sales\Tcs;


use Magento\Framework\Event\Observer;

/**
 * Class Invoice
 * @package RLTSquare\TcsShipping\Observer\Sales\Tcs
 */
class Invoice implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $invoiceSender;
    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Invoice constructor.
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->invoiceSender = $invoiceSender;
        $this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param Observer $observer
     * @return void|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getData('order');
        if (isset($order)) {
            if (!$order->canInvoice()) {
                return null;
            }
            if (!$order->getState() == 'new') {
                return null;
            }
            try {
                /** @var \Magento\Sales\Model\Order\Invoice $invoice */
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                $invoice->register();
                /** @var \Magento\Framework\DB\Transaction $transaction */
                $transaction = $this->transactionFactory->create()
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
                $transaction->save();

                $invoiceEmail = $this->scopeConfig->getValue('sales_email/invoice/enabled',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if ($invoiceEmail) {
                    $this->invoiceSender->send($invoice);
                }
                return;
            } catch (\Exception $exception) {
                $order->addStatusHistoryComment('Exception message: ' . $exception->getMessage(), false);
                $order->save();
                return null;
            }
        }
        throw new \Magento\Framework\Exception\LocalizedException(__('The order no longer exists.'));
    }
}