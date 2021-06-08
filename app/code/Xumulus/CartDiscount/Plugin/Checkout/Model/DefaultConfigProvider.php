<?php
namespace Xumulus\CartDiscount\Plugin\Checkout\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class DefaultConfigProvider
{
    protected $checkoutSession;
    protected $_totalAfterDiscount = 0;
    protected $_actualPrice = 0;
    protected $_cartPrice;
    protected $_salesRule;
    protected $_helper;
    protected $priceCurrency;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        CheckoutSession $checkoutSession,
        \Xumulus\CartDiscount\Model\Cartprice $cartprice,
        \Magento\SalesRule\Model\RuleFactory $saleRule,
        \Xumulus\CartDiscount\Helper\Data $helper
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->_cartPrice = $cartprice; 
        $this->checkoutSession = $checkoutSession;
        $this->_salesRule = $saleRule;
        $this->_helper = $helper; 
    }

    public function getFormatedPrice($price)
    {
        return $this->priceCurrency->format($price, false);
    }

    /**
     * {@inheritdoc}
     */
    public function aroundGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $subject,
        \Closure $proceed
    ) {
        $result = $proceed();
        $save = '';
        $actualPrice = 0;
        $totalAfterDiscount = 0;
        if(isset($result['quoteItemData'])){
	        $items = $result['totalsData']['items'];
	        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	        $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data');
            $totals = $this->checkoutSession->getQuote();
            //print_r($totals->getTotals()); exit;
	        foreach ($items as $index => $item) {
	            $quoteItem = $this->checkoutSession->getQuote()->getItemById($item['item_id']);
                $bundlePrice = array();
                $price = $this->_cartPrice->getProductPrice($quoteItem);
                $formattedCurrencyValue = 0;
                $save = 0;
                if(isset($price['save']) &&  $price['save'] > 0){
                    $formattedCurrencyValue = $this->getFormatedPrice(($price['price']*$item['qty']));
                    $save = $price['save'];
                }
                
                $actualPrice = $actualPrice + ($price['price']*$item['qty']);
                $totalAfterDiscount = $totalAfterDiscount + ($price['saved_price']*$item['qty']);
                
	            $result['quoteItemData'][$index]['sprice'] = $formattedCurrencyValue;
                if($save != '' && $this->_helper->getShowSavedPercent()){
                    $result['quoteItemData'][$index]['saved'] = number_format($save,2);
                }
                
	            $index++;
	        }
    	}
        $cartHtml = $this->getCartDiscount();
        
        if (isset($result['totalsData'])) {
            $totalsData = $result['totalsData'];
            $saving = $actualPrice - $totalAfterDiscount;
            if($saving > 0)
            { 
                $result['totalsData']['savings'] = $saving;
            }
            $result['totalsData']['actualprice'] = $actualPrice;
        }
        if(strlen($cartHtml)>1){
            $result['totalsData']['cartruleapplied'] = $cartHtml;
        }

        return $result;
    }

    public function optionProduct($quoteItem)
    {
        $totalDiscount = 0;
        $actualPrice = 0;
        $selectionCollection = $quoteItem->getProduct()->getTypeInstance(true)->getSelectionsCollection(
            $quoteItem->getProduct()->getTypeInstance(true)->getOptionsIds($quoteItem->getProduct()), $quoteItem->getProduct()
        );
     
        $bundled_items = array();
        foreach($selectionCollection as $option) 
        {
            $bundled_items[$option->getOptionId()][] = array('product_id'=>$option->getProductId(),'option_id'=>$option->getOptionId(),'price'=>$option->getPrice(),'special_price'=>$option->getSpecialPrice(),'name'=>$option->getName());
        }
        $options = $quoteItem->getProduct()->getTypeInstance(true)->getOrderOptions($quoteItem->getProduct());
        foreach ($options['bundle_options'] as $key=>$option){
            $product_detail = $bundled_items[$key];
            foreach ($option['value'] as $sub){
                $checkName = $this->checkProductName($product_detail,$sub['title']);

                if(empty($checkName) !== true){

                    if(isset($checkName['special_price']) && $checkName['special_price'] > 0){
                       $totalDiscount = $totalDiscount + ($sub['price']*$sub['qty']);
                       $actualPrice = $actualPrice + ($checkName['price']*$sub['qty']);
                    }
                }
            }
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

    public function getCartDiscount()
    {
        $appliedId = $this->checkoutSession->getQuote()->getAppliedRuleIds();
        $html = '';
        if($appliedId){
            $appliedIds = explode(',', $appliedId);
            $_rules = $this->_salesRule->create()->getCollection()->addFieldToFilter('is_active',1)
            ->addfieldtofilter('rule_id',array('in',$appliedIds));
            
            if($_rules->count() > 0){
                $html .= '<div class="lable-title">'.$this->_cartPrice->getShoppingCartRuleSummery().'</div>';
                foreach($_rules as $rule){
                    $html .= '<div class="lable-text">'.$this->getDiscountDetail($rule).'</div>';
                }
            }
        }
        return $html;
    }

    public function getDiscountDetail($rule) 
    {
        $html = '';
        if($rule->getSimpleAction() === 'by_percent'){
            $html .= '<span class="name">'.$rule->getName().'</span>';
            if($rule->getToDate()){
              $html .= '<span class="offer-expire">'.__('Hurry offer expires %1',date('t M y',strtotime($rule->getToDate()))).'</span>';  
            }
            
        }elseif($rule->getSimpleAction() === 'by_fixed'){

            $html .= '<span class="name">'.$rule->getName().'</span>';
            if($rule->getToDate()){
              $html .= '<span class="offer-expire">'.__('Hurry offer expires %1',date('t M y',strtotime($rule->getToDate()))).'</span>';  
            }

        }elseif($rule->getSimpleAction() === 'cart_fixed'){
            $html .= '<span class="name">'.$rule->getName().'</span>';
            if($rule->getToDate()){
              $html .= '<span class="offer-expire">'.__('Hurry offer expires %1',date('t M y',strtotime($rule->getToDate()))).'</span>';  
            }

        }elseif($rule->getSimpleAction() === 'buy_x_get_y'){
            $html .= '<span class="name">'.$rule->getName().'</span>';
            if($rule->getToDate()){
              $html .= '<span class="offer-expire">'.__('Hurry offer expires %1',date('t M y',strtotime($rule->getToDate()))).'</span>';  
            }
            
        }
        return $html;
    }
}