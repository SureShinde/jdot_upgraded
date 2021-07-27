<?php
namespace Magecomp\Sms\Observer\Customer;

use Magento\Framework\Event\ObserverInterface;

class ContactPostObserver implements ObserverInterface
{
    protected $helperapi;
    protected $helpercontact;
    protected $emailfilter;

    public function __construct(
        \Magecomp\Sms\Helper\Apicall $helperapi,
        \Magecomp\Sms\Helper\Contact $helpercontact,
        \Magento\Email\Model\Template\Filter $filter)
    {
        $this->helperapi = $helperapi;
        $this->helpercontact = $helpercontact;
        $this->emailfilter = $filter;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(!$this->helpercontact->isEnabled())
            return $this;

        $request = $observer->getRequest();
        $name = $request->getParam('name');
        $email = $request->getParam('email');
        $telephone = $request->getParam('telephone');
        $comment = $request->getParam('comment');

        $this->emailfilter->setVariables([
            'name' => $name,
            'email' => $email,
            'telephone' => $telephone,
            'comment' => $comment,
            'store_name' => $this->helpercontact->getStoreName()
        ]);

        if ($this->helpercontact->isContactNotificationForUser())
        {
            $message = $this->helpercontact->getContactNotificationUserTemplate();
            $finalmessage = $this->emailfilter->filter($message);
            $this->helperapi->callApiUrl($telephone,$finalmessage);
        }

        if($this->helpercontact->isContactNotificationForAdmin() && $this->helpercontact->getAdminNumber())
        {
            $message = $this->helpercontact->getContactNotificationForAdminTemplate();
            $finalmessage = $this->emailfilter->filter($message);
            $this->helperapi->callApiUrl($this->helpercontact->getAdminNumber(),$finalmessage);
        }

        return $this;
    }
}
