<?php

namespace RLTSquare\RelatedProductFix\Plugin;

use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;

/**
 * Class GetMatchingProductIdsPlugin
 * @package RLTSquare\RelatedProductFix\Plugin
 */
class GetMatchingProductIdsPlugin
{
    /**
     * Product factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * Store matched product Ids
     *
     * @var array
     */
    protected $productIds;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility ProductVisibility
     */
    protected $productVisibility;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    protected $stockFilter;
    /**
     * GetMatchingProductIdsPlugin constructor.
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\CatalogInventory\Helper\Stock $stockFilter
    )
    {
        $this->productFactory = $productFactory;
        $this->productVisibility = $productVisibility;
        $this->stockFilter = $stockFilter;
    }

    /**
     * @param \Magento\TargetRule\Model\Rule $subject
     * @param callable $proceed
     * @return array
     */
    public function aroundGetMatchingProductIds(\Magento\TargetRule\Model\Rule $subject, callable $proceed)
    {
        if(null === $this->productIds){
            /**
             * @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
             */
            $productCollection = $this->productFactory->create()->getCollection();
            $subject->setCollectedAttributes([]);
            $subject->getConditions()->collectValidatedAttributes($productCollection);

            $where = $subject->getConditions()->getConditionForCollection($productCollection);
            if ($where) {
                $productCollection->getSelect()->where($where);
            }
            $productCollection->addAttributeToFilter('status', ['in' => ProductStatus::STATUS_ENABLED]);
            $productCollection->setVisibility($this->productVisibility->getVisibleInSiteIds());
            $this->stockFilter->addInStockFilterToCollection($productCollection);
            $productCollection->getSelect()->orderRand();
            $productCollection->setPageSize($subject->getPositionsLimit());
            $this->productIds = [];
            foreach ($productCollection as $product) {
                if ($subject->getConditions()->validateByEntityId($product->getId())) {
                    $this->productIds[] = $product->getId();
                }
            }

        }
        return $this->productIds;
    }
}
