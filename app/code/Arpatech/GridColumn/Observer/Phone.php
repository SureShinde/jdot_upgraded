<?php

namespace Arpatech\GridColumn\Observer;
use \Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
/**
 * Class OrderStatus
 * @package RLTSquare\OrderState\Observer
 */
class Phone implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Product
     */
    protected $product;
    /**
     * @var Option
     */
    protected $options;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_customerCartSession;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;
    public function __construct(Product $product,Option $option,\Magento\Checkout\Model\Cart $cart,\Magento\Framework\Message\ManagerInterface $messageManager,        \Magento\Sales\Model\OrderFactory $orderFactory

    )
    {
        $this->product = $product;
        $this->options = $option;
        $this->_customerCartSession = $cart;
        $this->messageManager = $messageManager;
        $this->orderFactory = $orderFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $telephone = $order->getShippingAddress()->getTelephone();
        $order->setTelephone($telephone);
        $order->save();

    }
}