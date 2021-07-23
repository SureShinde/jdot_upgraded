<?php
namespace Magecomp\Sms\Observer\Customer;

use Magento\Framework\Event\ObserverInterface;

class CreditmemoSaveObserver implements ObserverInterface
{
    protected $helperapi;
    protected $helpercreditmemo;
    protected $emailfilter;
    protected $customerFactory;

    public function __construct(
        \Magecomp\Sms\Helper\Apicall $helperapi,
        \Magecomp\Sms\Helper\Creditmemo $helpercreditmemo,
        \Magento\Email\Model\Template\Filter $filter,
        \Magento\Customer\Model\CustomerFactory $customerFactory)
    {
        $this->helperapi = $helperapi;
        $this->helpercreditmemo = $helpercreditmemo;
        $this->emailfilter = $filter;
        $this->customerFactory = $customerFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(!$this->helpercreditmemo->isEnabled())
            return $this;

        $creditmemo = $observer->getCreditmemo();
        $order      = $creditmemo->getOrder();

        if($creditmemo)
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
                    'creditmemo' => $creditmemo,
                    'customer' => $customer,
                    'mobilenumber' => $mobilenumber
                ]);
            }
            else
            {
                $this->emailfilter->setVariables([
                    'order' => $order,
                    'creditmemo' => $creditmemo,
                    'mobilenumber' => $mobilenumber
                ]);
            }

            if ($this->helpercreditmemo->isCreditmemoNotificationForUser())
            {
                $message = $this->helpercreditmemo->getCreditmemoNotificationUserTemplate();
                $finalmessage = $this->emailfilter->filter($message);
                $this->helperapi->callApiUrl($mobilenumber,$finalmessage);
            }

            if($this->helpercreditmemo->isCreditmemoNotificationForAdmin() && $this->helpercreditmemo->getAdminNumber())
            {
                $message = $this->helpercreditmemo->getCreditmemoNotificationForAdminTemplate();
                $finalmessage = $this->emailfilter->filter($message);
                $this->helperapi->callApiUrl($this->helpercreditmemo->getAdminNumber(),$finalmessage);
            }
        }
        return $this;
    }
}
