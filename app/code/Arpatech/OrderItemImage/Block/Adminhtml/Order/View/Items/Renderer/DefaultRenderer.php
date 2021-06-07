<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Arpatech\OrderItemImage\Block\Adminhtml\Order\View\Items\Renderer;

use Magento\Sales\Model\Order\Item;
use Magento\Framework\ObjectManagerInterface;
/**
 * Adminhtml sales order item renderer
 */
class DefaultRenderer extends \Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer
{
//    public function __construct(
//        \Magento\Backend\Block\Template\Context $context,
//        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
//        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
//        \Magento\Framework\Registry $registry,
//        \Magento\GiftMessage\Helper\Message $messageHelper,
//        \Magento\Checkout\Helper\Data $checkoutHelper,
//        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
//        ObjectManagerInterface $objectManager,
//        array $data = []
//    ) {
//        $this->_checkoutHelper = $checkoutHelper;
//        $this->_messageHelper = $messageHelper;
//        $this->_productRepositoryFactory = $productRepositoryFactory;
//        $this->objectManager = $objectManager;
//        //parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $messageHelper,$checkoutHelper,$data);
//    }
    /**
     * @param \Magento\Framework\DataObject|Item $item
     * @param string $column
     * @param null $field
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getColumnHtml(\Magento\Framework\DataObject $item, $column, $field = null)
    {
        $html = '';
        switch ($column) {
            case 'product':
                if ($this->canDisplayContainer()) {
                    $html .= '<div id="' . $this->getHtmlId() . '">';
                }
                $html .= $this->getColumnHtml($item, 'name');
                if ($this->canDisplayContainer()) {
                    $html .= '</div>';
                }
                break;
            case 'status':
                $html = $item->getStatus();
                break;
            case 'price-original':
                $html = $this->displayPriceAttribute('original_price');
                break;
            case 'tax-amount':
                $html = $this->displayPriceAttribute('tax_amount');
                break;
            case 'tax-percent':
                $html = $this->displayTaxPercent($item);
                break;
            case 'discont':
                $html = $this->displayPriceAttribute('discount_amount');
                break;
            case 'product-image':

                $originalProduct = false;
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $product = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($item->getData('product_id'));
                if(isset($product[0])){
                    //this is parent product id..
                    $originalProduct = $objectManager->get('Magento\Catalog\Model\ProductRepository')->getById($product[0]);
                }else{
                    $originalProduct = $objectManager->get('Magento\Catalog\Model\ProductRepository')->getById($item->getData('product_id'));
                }

                $store = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
                $productThumbnailPath = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $originalProduct->getData('small_image');
//                if(!file_exists($productThumbnailPath)){
//                    $productThumbnailPath = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product/placeholder/' . $store->getConfig('catalog/placeholder/thumbnail_placeholder');
//                }
                $html = '<img src="'.$productThumbnailPath.'"/>';

                break;
            default:
                $html = parent::getColumnHtml($item, $column, $field);
        }
        return $html;
    }

}
