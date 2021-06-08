<?php
namespace Remmote\Facebookpixelremarketing\Observer;

class AddWishlist implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Catalog\Model\Session
     */
    public $catalogSession;
        
    public function __construct(
        \Magento\Catalog\Model\Session $catalogSession
    ) {
        $this->catalogSession = $catalogSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //Logging event
        $this->catalogSession->setPixelAddToWishlist(true);

        //Logging product ID
        $product = $observer->getEvent()->getProduct();
        $this->catalogSession->setPixelAddToWishlistProductId($product->getId());
    }
}
