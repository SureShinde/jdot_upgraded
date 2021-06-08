<?php
namespace Xumulus\CartDiscount\Block\Checkout;

use Magento\Framework\View\Element\Template\Context;

use Magento\Store\Model\ScopeInterface;

use Magento\Framework\View\Element\Template;

class Cart extends \Magento\Checkout\Block\Cart\Additional\info
{
	   
    public $_helper;
    
    protected $_currency;
    
    protected $_price;
    
    protected $_productPrice;

    protected $_salesRule;

    protected $_catalogRule;

    protected $_product;
    
    protected $_productItem;
    
    protected $_objectManager;
    
    protected $_directory;
    
    public function __construct(
            Context $context,
            \Xumulus\CartDiscount\Helper\Data $helper,
            \Magento\Framework\Pricing\Helper\Data $currency,
            \Magento\Bundle\Model\Product\Price $price,
            \Magento\SalesRule\Model\RuleFactory $saleRule,
            \Magento\Catalog\Model\Product $product,
            \Magento\CatalogRule\Model\RuleFactory $catalogRule,
            \Magento\Framework\ObjectManagerInterface $objectmanager,
            \Magento\Directory\Model\Currency $directory,
            array $data = []
    ) {
        $this->_helper = $helper;
        $this->_currency = $currency;
        $this->_price = $price;
        $this->_salesRule = $saleRule;
        $this->_product = $product;
        $this->_productItem = $product;
        $this->_catalogRule = $catalogRule;
        $this->_objectManager = $objectmanager;
        $this->_directory = $directory;
        //$this->_productHelper = $productHelper;
    	parent::__construct($context, $data);
    }
    
    public function getModuleEnable()
    {
        return $this->_helper->getEnableModule();        
    }

    public function getLineItemTitle()
    {
        return $this->_helper->getLineItemTitle();        
    }
    
    public function sortByOrder ($a, $b)
    {
        return $a['price_qty'] - $b['price_qty'];
    }

    protected function getTierPriceHtml($tierPrice, $qty, $moreBuy, $productType)
    {
        $html = '';
        $price = $tierPrice['price']->getValue();
        $appliedValue = $this->_currency->currency($price, true, false);
        if($productType === 'bundle' ){
            $price = $tierPrice['percentage_value'];
            $appliedValue = '-'.$this->_directory->format($price, ['display'=>\Zend_Currency::NO_SYMBOL], false).'%';
        }
        if($moreBuy > 0){
            $html .= "<li>";
            $html .= __(
                    'Add %1 more to get at %2 each',
                    intval($moreBuy),
                    $appliedValue
                    );
            $html .= '<div class="tooltip"><span class="design">?</span>
              <span class="tooltiptext">'.__(
                    'Buy %1 or more for %2 each',
                    intval($qty),
                    $appliedValue
                    ).'</span>
            </div>';
            $html .= "</li>";
        }else{
            $html .= "<li class='applied'>";
            $html .= __(
                    '%1 - rerceive %2 each',
                    $this->getItem()->getProduct()->getName(),
                    $appliedValue
                    );
            $html .= '<div class="tooltip"><span class="design">?</span>
              <span class="tooltiptext">'.__(
                    'Buy %1 or more for %2 each',
                    intval($qty),
                    $appliedValue
                    ).'</span>
            </div>';
            $html .= "</li>";
        }
        return $html;

    }

    public function getDefaultGroup($product_obj) 
    {
        $tier_price = $product_obj->getTierPrice();
        $tierPricesList = $product_obj->getPriceInfo()->getPrice('tier_price')->getTierPriceList();
        $tierPrices = array();
        uasort($tierPricesList, array($this, 'sortByOrder'));
        $html = '';
        if(count($tierPricesList) > 0){
            $html .='<ul>';
            foreach ($tierPricesList as $tierPrice) {
                $moreBuy = $tierPrice['price_qty'] - $this->getItem()->getQty();
                 $html .= $this->getTierPriceHtml($tierPrice, $tierPrice['price_qty'],$moreBuy,$product_obj->getTypeId());
                
            }
            $html .= "</ul>";
        }
        return $html;
    
    
     uasort($tier_price, array($this, 'sortByOrder'));
     $html = '';
        if(count($tier_price) > 0){
          $html .='<ul>';
            foreach($tier_price as $index=>$price){
               $moreBuy = $price['price_qty'] - $this->getItem()->getQty();
                if($moreBuy > 0){
                    $html .= "<li>";
                    $html .= __(
                            '%1 - Buy %2 more for %3 each',
                            $this->getItem()->getProduct()->getName(),
                            intval($moreBuy),
                            $this->_currency->currency($price['price'], true, false)
                            );
                    $html .= '<div class="tooltip"><span class="design">?</span>
                      <span class="tooltiptext">'.__(
                            'Buy %1 or more for %2 each',
                            intval($price['price_qty']),
                            $this->_currency->currency($price['price'], true, false)
                            ).'</span>
                    </div>';
                    $html .= "</li>";
                }else{
                    $html .= "<li class='applied'>";
                    $html .= __(
                            '%1 - rerceive %2 each',
                            $this->getItem()->getProduct()->getName(),
                            $this->_currency->currency($price['price'], true, false)
                            );
                    $html .= '<div class="tooltip"><span class="design">?</span>
                      <span class="tooltiptext">'.__(
                            'Buy %1 or more for %2 each',
                            intval($price['price_qty']),
                            $this->_currency->currency($price['price'], true, false)
                            ).'</span>
                    </div>';
                    $html .= "</li>";
                }
            }
            $html .= "</ul>";
        } 
        return $html;
    }


    public function getCartRules() 
    {
        $html = "";
        $ids = $this->getItem()->getAppliedRuleIds();
        if(strlen($ids) > 1){
            $ids = explode(',', $ids);
            $_rules = $this->_salesRule->create()->getCollection()->addFieldToFilter('is_active',1)
                ->addfieldtofilter('rule_id',array('in',$ids));
            if($_rules->count() > 0){
                foreach($_rules as $rule){
                    $html .= '<div class="cart-rule-text">'.$this->getDiscountDetail($rule).'</div>';
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
            
            }
            
        }elseif($rule->getSimpleAction() === 'by_fixed'){

            $html .= '<span class="name">'.$rule->getName().'</span>';
            if($rule->getToDate()){
             
            }

        }elseif($rule->getSimpleAction() === 'cart_fixed'){
            $html .= '<span class="name">'.$rule->getName().'</span>';
            if($rule->getToDate()){
              
            }

        }elseif($rule->getSimpleAction() === 'buy_x_get_y'){
            $html .= '<span class="name">'.$rule->getName().'</span>';
            if($rule->getToDate()){
              
            }
            
        }
        return $html;
    }

    public function getCatalogRules() 
    {
        $_rules = $this->_catalogRule->create()->getCollection();
        $productIds = array();
        $html = "";
        foreach($_rules as $rule){
            if (!$rule->getIsActive() || empty($rule->getWebsiteIds())) {
                return false;
            }
            $array = $rule->getMatchingProductIds();
            $pro_Ids = array_filter(array_map('array_filter', $array));
            $product_id = $this->getItem()->getProductId();
            if(isset($pro_Ids[$product_id])){
                $html .= '<span class="catalog-label"><span>'.$rule->getName().'</span></span>';
            }

        }
       return $html;
        
    }
       
}