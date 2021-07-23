<?php
namespace Magecomp\Sms\Observer\Customer;

use Magento\Framework\Event\ObserverInterface;

class RegisterSuccessObserver implements ObserverInterface
{
    protected $helperapi;
    protected $helpercustomer;
    protected $smsmodel;
    protected $emailfilter;

    public function __construct(
        \Magecomp\Sms\Helper\Apicall $helperapi,
        \Magecomp\Sms\Helper\Customer $helpercustomer,
        \Magecomp\Sms\Model\SmsFactory $smsmodel,
        \Magento\Email\Model\Template\Filter $filter)
    {
        $this->helperapi = $helperapi;
        $this->helpercustomer = $helpercustomer;
        $this->smsmodel = $smsmodel;
        $this->emailfilter = $filter;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(!$this->helpercustomer->isEnabled())
            return $this;

        $customer = $observer->getEvent()->getCustomer();

        $controller = $observer->getAccountController();
        $mobilenumber = $controller->getRequest()->getParam('mobilenumber');

        $this->emailfilter->setVariables([
            'customer' => $customer,
            'mobilenumber' => $mobilenumber
        ]);

        if($this->helpercustomer->isSignUpNotificationForAdmin() && $this->helpercustomer->getAdminNumber())
        {
            $message = $this->helpercustomer->getSignUpNotificationForAdminTemplate();
            $finalmessage = $this->emailfilter->filter($message);
            $this->helperapi->callApiUrl($this->helpercustomer->getAdminNumber(),$finalmessage);
        }

        if($mobilenumber == '' || $mobilenumber == null)
            return $this;

        $smsModel = $this->smsmodel->create();
        $smscollection = $smsModel->getCollection()
                       ->addFieldToFilter('mobile_number', $mobilenumber);
        foreach ($smscollection as $sms)
        {
            $sms->delete();
        }

        if ($this->helpercustomer->isSignUpNotificationForUser())
        {
            $message = $this->helpercustomer->getSignUpNotificationForUserTemplate();
            $finalmessage = $this->emailfilter->filter($message);
            $this->helperapi->callApiUrl($mobilenumber,$finalmessage);
        }
        return $this;
    }
}
