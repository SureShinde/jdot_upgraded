<?php
namespace Remmote\Facebookpixelremarketing\Observer;

class CompleteRegistration implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;
        
    public function __construct(
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //Logging event
        $this->customerSession->setPixelCompleteRegistration(true);
    }
}
