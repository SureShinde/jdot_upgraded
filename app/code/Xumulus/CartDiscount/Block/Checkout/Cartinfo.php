<?php
namespace Xumulus\CartDiscount\Block\Checkout;

use Magento\Framework\View\Element\Template\Context;

use Magento\Store\Model\ScopeInterface;

use Magento\Framework\View\Element\Template;

class Cartinfo extends \Magento\Checkout\Block\Cart
{
    
    
    public $_helper;
    
    protected $_currency;
    
    protected $_price;
    
    protected $_productPrice;

    protected $_salesRule;
    
    protected $_catalogRule;

    protected $_product;
    
    protected $_productItem;
    
    protected $_datetime;
    
    protected $_session;
    
    public function __construct(
            Context $context,
            \Magento\Customer\Model\Session $customerSession,
            \Magento\Checkout\Model\Session $checkoutSession,
            \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder,
            \Magento\Checkout\Helper\Cart $cartHelper,
            \Magento\Framework\App\Http\Context $httpContext,
            \Xumulus\CartDiscount\Helper\Data $helper,
            \Magento\Framework\Pricing\Helper\Data $currency,
            \Magento\SalesRule\Model\RuleFactory $saleRule,
            \Magento\Catalog\Model\Product $product,
            \Magento\CatalogRule\Model\RuleFactory $catalogRule,
            array $data = []
    ) {
        $this->_helper = $helper;
        $this->_currency = $currency;
        $this->_salesRule = $saleRule;
        $this->_product = $product;
        $this->_productItem = $product;
        $this->_catalogRule = $catalogRule;
        $this->_datetime = $context->getLocaleDate();
        $this->_session = $checkoutSession;
        parent::__construct($context, $customerSession, $checkoutSession, $catalogUrlBuilder, $cartHelper, $httpContext, $data);
    }
    
    public function getModuleEnable()
    {
        return $this->_helper->getEnableModule();        
    }

    public function getHeaderHtml()
    {
        return $this->_helper->getCartHeaderHtml();        
    }

    public function getFooterHtml()
    {
        return $this->_helper->getCartFooterHtml();        
    }

    public function getActiveCouponColor()
    {
        return $this->_helper->getCartActiveCouponColor();        
    }

    public function getTotalCouponShow()
    {
        return $this->_helper->getCartCouponShow();        
    }

    public function getCartItemBoxSize()
    {
        return $this->_helper->getCartItemBoxSize();        
    }

    public function getCartRules() 
    {

        $_rules = $this->_salesRule->create()->getCollection()->addFieldToFilter('is_active',1)
            ->addfieldtofilter('from_date', 
            array(
                array('to' => $this->_datetime->date()->format('Y-m-d')),
                 array('from_date', 'null'=>'')))
            ->addfieldtofilter('to_date',
            array(
                array('gteq' => $this->_datetime->date()->format('Y-m-d')),
                array('to_date', 'null'=>''))
                  )->addFieldToFilter('coupon_type',1);
        $_rules->getSelect()->limit($this->getTotalCouponShow());
         $html = "";
         $items = $this->getQuote()->getAllVisibleItems();
         $appliedId =  $this->getQuote()->getAppliedRuleIds();
         $appliedIds = explode(',', $appliedId);
         $class = "";
            if($this->getQuote()->getId() && $this->_helper->getShowPromotionOncart() && $this->getQuote()->getItemsCount()){
              foreach($_rules as $rule){
                        if (in_array($rule->getId(), $appliedIds)) {
                            $class = 'selected';
                        }else{
                             $class = '';
                        }
                        
                         $html .= '<div class="coupon-label '.$class.'"><div class="lable-text">'.$this->getDiscountDetail($rule).'</div></div>';
              }
            }
           return $html;
    }

    public function getDiscountDetail($rule) 
    {
        $html = '';
        if($rule->getSimpleAction() === 'by_percent'){
            $html .= '<span class="name">'.$rule->getName().'</span>';
            if($rule->getDiscountAmount() > 0){
                $html .= '<span class="offer-text">'.__('Receive %1 off',$this->getPercentage($rule->getDiscountAmount())).'</span>';
            }
            
        }elseif($rule->getSimpleAction() === 'by_fixed'){

            $html .= '<span class="name">'.$rule->getName().'</span>';
            if($rule->getDiscountAmount() > 0){
                $html .= '<span class="offer-text">'.__('Receive %1 off',number_format($rule->getDiscountAmount(),2).' Fixed').'</span>';
            }

        }elseif($rule->getSimpleAction() === 'cart_fixed'){
            $html .= '<span class="name">'.$rule->getName().'</span>';
            if($rule->getDiscountAmount() > 0){
                $html .= '<span class="offer-text">'.__('Receive %1 off',number_format($rule->getDiscountAmount(),2).' Fixed for whole cart').'</span>';
            }

        }elseif($rule->getSimpleAction() === 'buy_x_get_y'){
            $html .= '<span class="name">'.$rule->getName().'</span>';

            if($rule->getDiscountAmount() > 0){
                $html .= '<span class="offer-message">'.__('Add %1 applicable item to cart and receive...',(int)$rule->getDiscountStep()+(int)$rule->getDiscountAmount()).'</span>';
                $html .= '<span class="offer-text">'.__('Buy %1 and get %2 free',(int)$rule->getDiscountStep(),(int)$rule->getDiscountAmount()).'</span>';

            }
            if($rule->getToDate()){
              $html .= '<span class="offer-expire">'.__('Expires %1',date('t M y',strtotime($rule->getToDate()))).'</span>';  
            }
            
        }
        return $html;
    }
    public function getPercentage($number)
    {
        if ((int) $number == $number) {
            $str = (int)$number;
            return $str.'%';
        }else{
            return sprintf("%.2f%%", $number);
        }
    }
       
}