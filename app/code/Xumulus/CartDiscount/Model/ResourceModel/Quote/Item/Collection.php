<?php

namespace Xumulus\CartDiscount\Model\ResourceModel\Quote\Item;

class Collection extends \Magento\Quote\Model\ResourceModel\Quote\Item\Collection
{
	protected $_productMetadata;

	public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory $itemOptionCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Quote\Model\Quote\Config $quoteConfig,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $itemOptionCollectionFactory,
            $productCollectionFactory,
            $quoteConfig,
            $connection,
            $resource

        );
        $this->productMetadata = $productMetadata;
    }

	public function getStoreId(): int
    {
    	$version = $this->getMagentoVersion();
    	$version = str_replace('.', '', $version);
    	$subVersion = substr($version, 0, 2);
    	if($subVersion < 22){
    		return (int)$this->_productCollectionFactory->create()->getStoreId();
    	}else{
    		return parent::getStoreId();
    	}
        
    }

    public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }
}