<?php
/**
 * Catalog super product configurable part block
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Arpatech\CustomConfigurableProduct\Block\Product\View\Type;


/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Configurable
{
    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    )
    {
        $this->_stockRegistry   = $stockRegistry;
    }

     /**
     * Get Allowed Products
     *
     * @return \Magento\Catalog\Model\Product[]
     */
    public function beforeGetAllowProducts($class)
    {
        $logger = \Magento\Framework\App\ObjectManager::getInstance()->get('\Psr\Log\LoggerInterface');
        $logger->debug('Block Override '.__METHOD__ .__LINE__);
        if (!$class->hasAllowProducts()) {
            $skipSaleableCheck = true;
            $products = $skipSaleableCheck ?
            $class->getProduct()->getTypeInstance()->getUsedProducts($class->getProduct(), null) :
            $class->getProduct()->getTypeInstance()->getSalableUsedProducts($class->getProduct(), null);
            $class->setAllowProducts($products);
        }
        return $class->getData('allow_products');
    }



}
