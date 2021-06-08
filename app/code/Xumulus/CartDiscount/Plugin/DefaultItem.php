<?php

namespace Xumulus\CartDiscount\Plugin;

use Magento\Quote\Model\Quote\Item;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class DefaultItem
{

    protected $priceCurrency;
    protected $_cartPrice;
    protected $_helper;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        \Xumulus\CartDiscount\Model\Cartprice $cartprice,
        \Xumulus\CartDiscount\Helper\Data $helper
       
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->_cartPrice = $cartprice; 
        $this->_helper = $helper; 

    }

    public function getFormatedPrice($price)
    {
        return $this->priceCurrency->format($price);
    }

    public function aroundGetItemData($subject, \Closure $proceed, Item $item)
    {
        $data = $proceed($item);
        $price = $this->_cartPrice->getProductPrice($item);
        $result = $data;
        
        if(isset($price['save']) &&  $price['save'] > 0){
            $result['regular_price'] = $this->getFormatedPrice($price['price']);
            $result['regular_price_value'] = $price['price'];
            $result['saved'] = (string)__('(Saved %1)',$price['save'].'%');        
            $result['show_price'] = 1;
            $result['show_percent'] = ($this->_helper->getShowSavedPercent())?true:false;
        }
        return $result;
    }
}