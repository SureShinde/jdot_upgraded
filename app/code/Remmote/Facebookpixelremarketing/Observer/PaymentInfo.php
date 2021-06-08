<?php
namespace Remmote\Facebookpixelremarketing\Observer;

class PaymentInfo implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    public $checkoutSession;
        
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //Logging event
        $this->checkoutSession->setPixelPaymentInfo(true);
    }
}