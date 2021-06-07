<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Arpatech\GridColumn\Ui\Component\Listing\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use  Magento\Ui\Component\Listing\Columns\Column;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;


class ProductGrid extends Column
{
    private $_product;

    private $_helper = null;

    /**
     * Constructor
     *
     * @param ContextInterface               $context
     * @param UiComponentFactory             $uiComponentFactory
     * @param \Magento\Sales\Model\Order     $order
     * @param \WeltPixel\Maxmind\Helper\Data $helper
     * @param array                          $components
     * @param array                          $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,

        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }
        return $dataSource;
    }
    protected function prepareItem(array $item){

        $category = '';

        $entityId = array_key_exists('entity_id', $item) ? $item['entity_id'] : null;
        $product = $this->productRepository->getById($entityId);
        $categoryIds = $product->getCustomAttribute('category_ids');
        
	//$categoryId = '';
        if($categoryIds) {
	    $categoryId = array_slice($categoryIds->getValue(), -1);
            foreach ($categoryId as $cid) {
                $category = $this->categoryRepository->get($cid)->getName();
            }
        }
        return $category;
    }
}
