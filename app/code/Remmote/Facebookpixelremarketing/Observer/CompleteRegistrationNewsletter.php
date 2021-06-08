<?php
namespace Remmote\Facebookpixelremarketing\Observer;

class CompleteRegistrationNewsletter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    /**
     * @var \Magento\Newsletter\Model\Session
     */
    public $newsletterSession;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Newsletter\Model\Session $newsletterSession
    ) {
        $this->customerSession = $customerSession;
        $this->newsletterSession = $newsletterSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $subscriber = $observer->getEvent()->getSubscriber();

        if ($subscriber->getStatus() == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED
            && $subscriber->isStatusChanged()) {
            $this->customerSession->setPixelCompleteRegistration(true);

            //Set flag for Lead Event
            $this->newsletterSession->setPixelLead(true);
        }
    }
}
