<?php
namespace Remmote\Facebookpixelremarketing\Observer;

class AddProduct implements \Magento\Framework\Event\ObserverInterface
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
        $this->checkoutSession->setPixelAddToCart(true);

        //Logging product ID
        $product = $observer->getEvent()->getProduct();
        $this->checkoutSession->setPixelAddToCartProductId($product->getId());
    }
}
