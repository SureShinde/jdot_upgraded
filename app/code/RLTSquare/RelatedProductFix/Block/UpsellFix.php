<?php


namespace RLTSquare\RelatedProductFix\Block;


class UpsellFix extends \Magento\TargetRule\Block\Catalog\Product\ProductList\Upsell
{
    protected $product;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\TargetRule\Model\ResourceModel\Index $index, \Magento\TargetRule\Helper\Data $targetRuleData,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\TargetRule\Model\IndexFactory $indexFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\Product $product,
        array $data = []
    )
    {
        parent::__construct($context, $index, $targetRuleData, $productCollectionFactory, $visibility, $indexFactory, $cart, $data);
        $this->product = $product;
    }

    protected function _getLinkProducts()
    {
        $items = [];
        if($this->validateRule()){
            $linkCollection = $this->getLinkCollection();
            if ($linkCollection) {
                foreach ($linkCollection->getData() as $item) {
                    $product = $this->product->load($item['entity_id']);
                    $items[$product->getId()] = $product;
                }
            }
        }
        return $items;
    }

    protected function validateRule(){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $ruleFactory = $objectManager->create(\Magento\TargetRule\Model\ResourceModel\Rule\CollectionFactory::class);
        $ruleFactory= $ruleFactory->create();
        foreach ($ruleFactory as $rule){
            try{
                if($rule->getIsActive() && $rule->getApplyTo() == \Magento\TargetRule\Model\Rule::UP_SELLS && $rule->validate($this->getProduct()))
                    return true;
            }catch (\Exception $ex){
                return null;
            }
        }
        return false;
    }

}