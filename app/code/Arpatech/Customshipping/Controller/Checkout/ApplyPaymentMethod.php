<?php

namespace Arpatech\Customshipping\Controller\Checkout;

class ApplyPaymentMethod extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;
    protected $_scopeConfig;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->layoutFactory = $layoutFactory;
        $this->cart = $cart;
        $this->_scopeConfig = $scopeConfig;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $pMethod = $this->getRequest()->getParam('payment_method');

        $quote = $this->cart->getQuote();

        $quote->getPayment()->setMethod($pMethod['method']);
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $this->setShippingPrice($quote,$pMethod['method']);
        $quote->save();
    }

    function setShippingPrice($quote,$pMethod){

        $price = $this->_scopeConfig->getValue('carriers/mpcustomshipping/price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $onlinePayment = $this->_scopeConfig->getValue('carriers/mpcustomshipping/psp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $bankTransferPrice = $this->_scopeConfig->getValue('carriers/mpcustomshipping/banktransfer', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $method = $quote->getShippingAddress()->getShippingMethod();
        $rate = $quote->getShippingAddress()->getShippingRateByCode($method);

        $pMethodArr = array('ubl' => $onlinePayment,'banktransfer' => $bankTransferPrice);
        $weight = $rate->getPrice() / $price;

        $shippingPrice = $pMethodArr[$pMethod] * $weight;
        $rate->setPrice($shippingPrice);
        $rate->setCost($shippingPrice);
    }
}