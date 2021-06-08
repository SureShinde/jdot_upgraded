<?php
namespace Xumulus\CartDiscount\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $_moduleList;

	protected $taxCalculation;
	
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\Module\ModuleListInterface $moduleList,
		\Magento\Tax\Api\TaxCalculationInterface $taxCalculation
	) {
		$this->_moduleList = $moduleList;
		$this->taxCalculation = $taxCalculation;

		parent::__construct($context);
	}

	public function getExtensionVersion()
	{
		$moduleCode = 'Xumulus_CartDiscount';
		$moduleInfo = $this->_moduleList->getOne($moduleCode);
		return $moduleInfo['setup_version'];
	}

	public function getTax()
	{
		return $this->taxCalculation->getCalculatedRate(2);
	}

	public function getConfig($config_path)
	{
	    return $this->scopeConfig->getValue(
	            $config_path,
	            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
	            );
	}

	public function getListprice()
	{
		$listprice = $this->getConfig('discount/general/listprice');
		return ($listprice !='')?$listprice:'Regular Price';
	}

	

	public function getSavings()
	{
		$savings = $this->getConfig('discount/general/savings');
		return ($savings !='')?$savings:'Saved';
	}

	public function getShowSavedPercent()
	{
		$show_saved_percent = $this->scopeConfig->getValue('discount/general/show_saved_percent',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		return $show_saved_percent;
	}

	public function getShowPromotionOncart()
	{
		$discount_general_show_promotion_oncart = $this->scopeConfig->getValue('discount/cart/show_promotion_oncart',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		return ($discount_general_show_promotion_oncart !='')?$discount_general_show_promotion_oncart:'1';
	}

	public function getCartHeaderHtml()
	{
		return $this->scopeConfig->getValue('discount/cart/header_html',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}

	public function getCartFooterHtml()
	{
		return $this->scopeConfig->getValue('discount/cart/footer_html',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}

	public function getCartActiveCouponColor()
	{
		$active_coupon_color = $this->scopeConfig->getValue('discount/cart/active_coupon_color',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		return ($active_coupon_color !='')?'#'.$active_coupon_color:'';
	}

	public function getCartCouponShow()
	{
		$coupon_show = $this->scopeConfig->getValue('discount/cart/coupon_show',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		return ($coupon_show !='')?$coupon_show:'4';
	}

	public function getLineItemTitle()
	{
		$cart_line_item_title = $this->scopeConfig->getValue('discount/general/cart_line_item_title',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		return ($cart_line_item_title !='')?$cart_line_item_title:'Applicable Offer';
	}

	public function getShoppingCartRuleSummery()
	{
		$shopping_cart_sidebar_title = $this->scopeConfig->getValue('discount/general/shopping_cart_sidebar_title',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		return ($shopping_cart_sidebar_title !='')?$shopping_cart_sidebar_title:'Applied Rule';
	}

	public function getCartItemBoxSize()
	{
		$size = $this->scopeConfig->getValue('discount/cart/cart_item_box',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$width = 200;
		$height = 200;
		if($size != ''){
			$sizes = explode('x', strtolower($size));
			if(count($sizes) == 1){
				$width = $sizes[0];
				$height = $sizes[0];
			}else{
				$width = $sizes[0];
				$height = $sizes[1];
			}

		}
		return array('width'=>$width,'height'=>$height);
	}

	public function getCartRuleBoxSize()
	{
		$cat_rule_cat_rule = $this->scopeConfig->getValue('discount/cart/cat_rule_box_size',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$width = 150;
		$height = 150;
		if($cat_rule_cat_rule != ''){
			$cat_rule = explode('x', strtolower($cat_rule_cat_rule));
			if(count($cat_rule) == 1){
				$width = $cat_rule[1];
				$height = $cat_rule[1];
			}else{
				$width = $cat_rule[1];
				$height = $cat_rule[2];
			}
		}
		return array('width'=>$width,'height'=>$height);
	}
	
	public function getCartRuleSummery()
	{
		return 'Applied Rule';
	}

}