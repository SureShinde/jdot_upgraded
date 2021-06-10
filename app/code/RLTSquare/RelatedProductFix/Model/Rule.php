<?php

namespace RLTSquare\RelatedProductFix\Model;


class Rule extends \Magento\Rule\Model\AbstractModel
{
    protected $_productIds;
    /**
     * Get array of product ids which are matched by rule
     *
     * @return array
     */
    public function getListProductIdsInRule()
    {
        $productCollection = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Catalog\Model\ResourceModel\Product\Collection'
        );
        $productFactory = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Catalog\Model\ProductFactory'
        );
        $this->_productIds = [];
        $this->setCollectedAttributes([]);
        $this->getConditions()->collectValidatedAttributes($productCollection);
        \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Framework\Model\ResourceModel\Iterator'
        )->walk(
            $this->_productCollection->getSelect(),
            [[$this, 'callbackValidateProduct']],
            [
                'attributes' => $this->getCollectedAttributes(),
                'product' => $productFactory->create()
            ]
        );
        return $this->_productIds;
    }
    /**
     * Callback function for product matching
     *
     * @param array $args
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);
        $websites = $this->_getWebsitesMap();
        foreach ($websites as $websiteId => $defaultStoreId) {
            $product->setStoreId($defaultStoreId);
            if ($this->getConditions()->validate($product)) {
                $this->_productIds[] = $product->getId();
            }
        }
    }
    /**
     * Prepare website map
     *
     * @return array
     */
    protected function _getWebsitesMap()
    {
        $map = [];
        $websites = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Store\Model\StoreManagerInterface'
        )->getWebsites();
        foreach ($websites as $website) {
            // Continue if website has no store to be able to create catalog rule for website without store
            if ($website->getDefaultStore() === null) {
                continue;
            }
            $map[$website->getId()] = $website->getDefaultStore()->getId();
        }
        return $map;
    }

    /**
     * Getter for rule combine conditions instance
     *
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditionsInstance()
    {
        // TODO: Implement getConditionsInstance() method.
    }

    /**
     * Getter for rule actions collection instance
     *
     * @return \Magento\Rule\Model\Action\Collection
     */
    public function getActionsInstance()
    {
        // TODO: Implement getActionsInstance() method.
    }
}