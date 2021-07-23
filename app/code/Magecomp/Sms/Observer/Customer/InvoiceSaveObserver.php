<?php
namespace Magecomp\Sms\Observer\Customer;

use Magento\Framework\Event\ObserverInterface;

class InvoiceSaveObserver implements ObserverInterface
{
    protected $helperapi;
    protected $helperinvoice;
    protected $emailfilter;
    protected $customerFactory;

    public function __construct(
        \Magecomp\Sms\Helper\Apicall $helperapi,
        \Magecomp\Sms\Helper\Invoice $helperinvoice,
        \Magento\Email\Model\Template\Filter $filter,
        \Magento\Customer\Model\CustomerFactory $customerFactory)
    {
        $this->helperapi = $helperapi;
        $this->helperinvoice = $helperinvoice;
        $this->emailfilter = $filter;
        $this->customerFactory = $customerFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(!$this->helperinvoice->isEnabled())
            return $this;

        $invoice    = $observer->getInvoice();
        $order      = $invoice->getOrder();

        if($invoice)
        {
            $billingAddress = $order->getBillingAddress();
            $mobilenumber = $billingAddress->getTelephone();

            if($order->getCustomerId() > 0)
            {
                $customer = $this->customerFactory->create()->load($order->getCustomerId());
                $mobile = $customer->getMobilenumber();
                if($mobile != '' && $mobile != null)
                {
                    $mobilenumber = $mobile;
                }

                $this->emailfilter->setVariables([
                    'order' => $order,
                    'invoice' => $invoice,
                    'customer' => $customer,
                    'invoice_total' => $order->formatPriceTxt($invoice->getGrandTotal()),
                    'mobilenumber' => $mobilenumber
                ]);
            }
            else
            {
                $this->emailfilter->setVariables([
                    'order' => $order,
                    'invoice' => $invoice,
                    'invoice_total' => $order->formatPriceTxt($invoice->getGrandTotal()),
                    'mobilenumber' => $mobilenumber
                ]);
            }

            if ($this->helperinvoice->isInvoiceNotificationForUser())
            {
                $message = $this->helperinvoice->getInvoiceNotificationUserTemplate();
                $finalmessage = $this->emailfilter->filter($message);
                $this->helperapi->callApiUrl($mobilenumber,$finalmessage);
            }

            if($this->helperinvoice->isInvoiceNotificationForAdmin() && $this->helperinvoice->getAdminNumber())
            {
                $message = $this->helperinvoice->getInvoiceNotificationForAdminTemplate();
                $finalmessage = $this->emailfilter->filter($message);
                $this->helperapi->callApiUrl($this->helperinvoice->getAdminNumber(),$finalmessage);
            }
        }
        return $this;
    }
}
