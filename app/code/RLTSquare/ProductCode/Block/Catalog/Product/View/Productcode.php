<?php

namespace RLTSquare\ProductCode\Block\Catalog\Product\View;

use \Magento\Catalog\Model\Product;

class Productcode extends \Magento\Framework\View\Element\Template
{
    protected $_registry;
    protected $_productRepository;
    protected $product;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context,
                                \Magento\Framework\Registry $registry,
                                \Magento\Catalog\Model\ProductRepository $productRepository,
                                Product $product,
                                array $data = []
    )
    {
        $this->_productRepository = $productRepository;
        $this->_registry = $registry;
        $this->product = $product;
        parent::__construct($context, $data);
    }
    public function getProductCode($productId)
    {
        $product = $this->_productRepository->getById($productId);
        return $product->getProductCode();
    }
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getCurrentCategory()
    {
        return $this->_registry->registry('current_category');
    }

    public function getCurrentProduct()
    {
        return $this->_registry->registry('current_product');
    }
    public function getQty($productId)
    {
        $productLoad = $this->product->load($productId);
        $stockStatus = $productLoad->getData('quantity_and_stock_status');
        $qty = $stockStatus['qty'];
        return $qty;
    }
}
