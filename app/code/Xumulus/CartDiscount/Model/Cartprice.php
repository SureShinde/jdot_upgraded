<?php
namespace Xumulus\CartDiscount\Model;

class Cartprice extends \Magento\Framework\Model\AbstractModel
{
    protected $_helper;

    protected $_pricingHelper;

    protected $_productHelper;
    protected $_quoteItemOption;

    protected $taxConfig;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Xumulus\CartDiscount\Helper\Data $hepler,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Data $productHelper,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemOption,
        \Magento\Tax\Model\Config $taxConfig,
        array $data = []
    ) {
        parent::__construct($context , $registry);
        $this->_helper = $hepler;
        $this->_pricingHelper = $pricingHelper;
        $this->_productHelper = $productHelper;
        $this->_quoteItemOption = $quoteItemOption;
        $this->taxConfig = $taxConfig;
    }

    public function optionProduct($quoteItem , $singleQty = false)
    {
        $totalDiscount = 0;
        $actualPrice = 0;
        $bundled_items = array();
        $itemCollection = $this->_quoteItemOption->create()->getCollection()
            ->addFieldToFilter('quote_id',$quoteItem->getQuoteId())
            ->addFieldToFilter('parent_item_id',$quoteItem->getId());
        foreach($itemCollection as $item){
            $itemPrice = $item->getPrice();
            $price = $this->_productHelper->getTaxPrice($item->getProduct(),$item->getProduct()->getPrice());
            if ($item->getTaxPercent() > 0) {
                $itemPrice = $itemPrice + (($itemPrice*$item->getTaxPercent())/100);
                $price = $price + (($price*$item->getTaxPercent())/100);
            }
            if($singleQty){
                $totalDiscount = $totalDiscount + $itemPrice;
                $actualPrice = $actualPrice + $price;
            }else{
                $totalDiscount = $totalDiscount + ($itemPrice*$item->getQty());
                $actualPrice = $actualPrice + ($price*$item->getQty());
            }

        }
        if($singleQty === false){
            $totalDiscount = $totalDiscount*$quoteItem->getQty();
            $actualPrice = $actualPrice*$quoteItem->getQty();
        }
        return array('totalDiscount'=>$totalDiscount,'actualPrice'=>$actualPrice);
    }

    public function checkProductName($productDetail , $bundleoptionName)
    {

        if(empty($productDetail) !== true){
            foreach($productDetail as $detail){
                if(strtolower($detail['name']) === strtolower($bundleoptionName)){
                    return $detail;
                }
            }
        }
        return false;
    }

    public function getProductPrice($item)
    {
        $product = $item->getProduct();
        $save = 0;
        $productPrice = 0;
        $discount_price = 0;
        $itemPrice = 0;
        if($product->getTypeId() === 'bundle' && $product->getPriceType() == 0){
            $bundlePrice = $this->optionProduct($item ,true);
            if(isset($bundlePrice['totalDiscount']) && $bundlePrice['totalDiscount'] > 0){
                if($bundlePrice['actualPrice'] > $bundlePrice['totalDiscount']){
                    $productPrice = $bundlePrice['actualPrice'];
                    $priceCal = ($bundlePrice['totalDiscount'] * 100) / $bundlePrice['actualPrice'];
                    $save = 100 - $priceCal;
                    $discount_price = $bundlePrice['actualPrice']-$bundlePrice['totalDiscount'];
                }else{
                    $productPrice = $bundlePrice['actualPrice'];
                    $save = 0;
                }

            }
        }else{
            $itemPrice = $item->getPrice();
            $productPrice = $this->_productHelper->getTaxPrice($product,$product->getPrice());
            if ($item->getTaxPercent() > 0) {
                $itemPrice = $itemPrice + (($itemPrice*$item->getTaxPercent())/100);
                $productPrice = $productPrice + (($productPrice*$item->getTaxPercent())/100);
            }

            $priceCal = ($itemPrice * 100) / $productPrice;
            $discount_price = $productPrice - $itemPrice;
            $save = 100 - $priceCal;
        }



        return array('price'=>$this->_pricingHelper->currency($productPrice,false),'itemPrice'=>$this->_pricingHelper->currency($itemPrice, false), 'saved_price'=>$this->_pricingHelper->currency($discount_price,false),'save'=>number_format($save,2));

    }

    public function getShoppingCartRuleSummery()
    {
        return $this->_helper->getShoppingCartRuleSummery();
    }

}