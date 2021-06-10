<?php


namespace RLTSquare\RelatedProductFix\Block;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class RelatedFix (RLTSqu)
 * @package RLTSquare\RelatedProductFix\Block
 */
class RelatedFix extends \Magento\TargetRule\Block\Catalog\Product\ProductList\Related
{
    /**
     * @var array
     */
    protected $productIds = [];
    /**
     * @var \Magento\TargetRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * RelatedFix constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\TargetRule\Model\ResourceModel\Index $index
     * @param \Magento\TargetRule\Helper\Data $targetRuleData
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $visibility
     * @param \Magento\TargetRule\Model\IndexFactory $indexFactory
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\TargetRule\Model\ResourceModel\Rule\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\TargetRule\Model\ResourceModel\Index $index,
        \Magento\TargetRule\Helper\Data $targetRuleData,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\TargetRule\Model\IndexFactory $indexFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\TargetRule\Model\ResourceModel\Rule\CollectionFactory $collectionFactory,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $index,
            $targetRuleData,
            $productCollectionFactory,
            $visibility,
            $indexFactory,
            $cart,
            $data
        );
        $this->ruleCollectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    protected function _getLinkProducts()
    {
        $items = [];
        if($this->validateRule()){
            if (count($this->productIds)) {
                foreach ($this->productIds as $productId) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
                    $items[$productId] = $product;
                }
            }
        }
        return $items;
    }

    /**
     * @return bool
     */
    protected function validateRule(){
        $ruleFactory= $this->ruleCollectionFactory->create();
        foreach ($ruleFactory as $rule){
            try{
                if($rule->getIsActive() && $rule->getApplyTo() == \Magento\TargetRule\Model\Rule::RELATED_PRODUCTS && $rule->validate($this->getProduct()))
                {
                    $productIds = $rule->getMatchingProductIds();
                    $this->productIds= array_slice($productIds,0,$rule->getPositionsLimit());
                    return true;
                }
            }catch (\Exception $ex){
                return false;
            }
        }
        return false;
    }
}
