<?php
/**
 * @extension   Remmote_Facebookpixelremarketing
 * @author      Remmote
 * @copyright   2017 - Remmote.com
 * @descripion  Code block
 */
namespace Remmote\Facebookpixelremarketing\Block;

class Code extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Remmote\Facebookpixelremarketing\Helper\Data
     */
    public $helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    public $request;

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * @var \Magento\Catalog\Model\Session
     */
    public $catalogSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    /**
     * @var \Magento\Newsletter\Model\Session
     */
    public $newsletterSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    public $checkoutSession;

    /**
     * @var \Magento\Sales\Model\Order
     */
    public $orderModel;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $productFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    public $categoryFactory;

    public function __construct(
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Newsletter\Model\Session $newsletterSession,
        \Magento\Framework\View\Element\Template\Context $context,
        \Remmote\Facebookpixelremarketing\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Order $orderModel,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        array $data = []
    ) {
        $this->catalogSession = $catalogSession;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->newsletterSession = $newsletterSession;

        $this->helper = $helper;
        $this->request = $request;
        $this->registry = $registry;
        $this->orderModel = $orderModel;
        $this->productFactory = $productFactory;
        $this->categoryFactory = $categoryFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get store current section
     * @return [type]
     * @author Remmote.com
     * @date   2017-07-27
     */
    private function _getSection()
    {
        return $this->request->getFullActionName();
    }

    /**
     * Get current store currency
     * @return [type]
     * @author Remmote.com
     * @date   2017-07-27
     */
    private function _getStoreCurrency()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * Get website id
     * @return [type]
     * @author Remmote.com
     * @date   2017-07-27
     */
    private function _getWebsiteId()
    {
        return $this->_storeManager->getStore()->getWebsiteId();
    }

    /**
     * Replace special chars
     * @param  [type] $string
     * @return [type]
     * @author Remmote.com
     * @date   2017-07-27
     */
    private function _replaceSpecialChars($string)
    {
        return str_replace("'", "\'", $string);
    }

    /**
     * Return Facebook Pixel Id
     * @return [type]
     * @author Remmote.com
     * @date   2017-07-27
     */
    public function getPixelId()
    {
        return $this->helper->getPixelId($this->_getWebsiteId());
    }

    /**
     * Return View Content event track
     * @return [type]
     * @author Remmote.com
     * @date   2017-07-27
     */
    public function getViewContentEvent()
    {
        $pageSection = $this->_getSection();

        //Check if event is enabled
        if ($this->helper->viewContentEnabled($this->_getWebsiteId()) && $pageSection == 'catalog_product_view') {
            $product = $this->registry->registry('current_product');
            if (!empty($product)) {
                //Checking if product is configurable product
                if($product->getTypeId() == 'configurable'){
                    // $_children = $product->getTypeInstance()->getUsedProducts($product);
                    // foreach ($_children as $child){
                    //     $product = $child;
                    //     break;
                    // }
                }

                //Getting category name
                $categoryIds   = $product->getCategoryIds();
                $categoryName  = '';
                if (count($categoryIds) >= 1) {
                    $categoryId   = end($categoryIds); //(lowest category level)
                    $category     = $this->categoryFactory->create()->load($categoryId);
                    $categoryName = $this->_replaceSpecialChars($category->getName());
                }

                $contentIds = $this->helper->getUseProductId($this->_getWebsiteId()) ?
                    $product->getId() : $product->getSku();
                return "fbq('track', 'ViewContent', {
                    content_name: '". $this->_replaceSpecialChars($product->getName()) ."',
                    content_ids: ['". $contentIds ."'],
                    content_type: 'product',
                    content_category: '". $categoryName ."',
                    value: '". $product->getFinalPrice() ."',
                    currency: '". $this->_getStoreCurrency() ."'
                });";
            }
        }
    }

    /**
     * Return Search event track
     * @return [type]
     * @author Remmote.com
     * @date   2017-07-27
     */
    public function getSearchEvent()
    {
        $pageSection = $this->_getSection();

        //Check if event is enabled
        if ($this->helper->searchEnabled($this->_getWebsiteId()) &&
            ($pageSection == 'catalogsearch_result_index' || $pageSection == 'catalogsearch_advanced_result')) {
            // Getting search string
            return "fbq('track', 'Search', {
                'search_string' : '". $this->_replaceSpecialChars($this->request->getParam('q')) ."'
            });";
        }
    }

    /**
     * Return AddToCart event track
     * @return [type]
     * @author Remmote.com
     * @date   2017-07-27
     */
    public function getAddToCartEvent()
    {
        //Check if event is enabled
        if ($this->helper->addToCartEnabled($this->_getWebsiteId())) {

            $pixelEvent = $this->checkoutSession->getPixelAddToCart();
            $productId  = $this->checkoutSession->getPixelAddToCartProductId();

            if ($pixelEvent && $productId) {
                //Unset events
                $this->checkoutSession->unsPixelAddToCart();
                $this->checkoutSession->unsPixelAddToCartProductId();

                //Loading Product
                $product = $this->productFactory->create()->load($productId);

                //Checking type of product
                if ($product->getTypeId() == 'grouped') {
                    $product = $this->checkoutSession->getQuote()->getItemsCollection()
                        ->getLastItem()
                        ->getProduct();
                }

                $contentIds = $this->helper->getUseProductId($this->_getWebsiteId()) ?
                    $product->getId() : $product->getSku();
                return "fbq('track', 'AddToCart', {
                    content_name: '". $this->_replaceSpecialChars($product->getName()) ."',
                    content_ids: ['". $contentIds ."'],
                    content_type: 'product',
                    value: '". $product->getFinalPrice() ."',
                    currency: '". $this->_getStoreCurrency() ."'
                });";
            }
        }
    }

    /**
     * Return AddToWishlist event track
     * @return [type]
     * @author Remmote.com
     * @date   2017-07-27
     */
    public function getAddToWishlistEvent()
    {
        //Check if event is enabled
        if ($this->helper->addToWishlistEnabled($this->_getWebsiteId())) {

            $pixelEvent = $this->catalogSession->getPixelAddToWishlist();
            $productId  = $this->catalogSession->getPixelAddToWishlistProductId();

            if ($pixelEvent && $productId) {
                //Unset event
                $this->catalogSession->unsPixelAddToWishlist();
                $this->catalogSession->unsPixelAddToWishlistProductId();

                //Loading Product
                $product = $this->productFactory->create()->load($productId);

                //Checking type of product
                if ($product->getTypeId() == 'grouped') {
                    $product = $this->checkoutSession->getQuote()->getItemsCollection()
                        ->getLastItem()
                        ->getProduct();
                }

                //Getting category name
                $categoryIds   = $product->getCategoryIds();
                $categoryName  = '';
                if (count($categoryIds) >= 1) {
                    $categoryId   = end($categoryIds); //(lowest category level)
                    $category     = $this->categoryFactory->create()->load($categoryId);
                    $categoryName = $this->_replaceSpecialChars($category->getName());
                }

                $contentIds = $this->helper->getUseProductId($this->_getWebsiteId()) ?
                    $product->getId() : $product->getSku();
                return "fbq('track', 'AddToWishlist',{
                    content_name:   '". $this->_replaceSpecialChars($product->getName()) ."',
                    content_ids:    ['". $contentIds ."'],
                    content_type:   'product',
                    content_category: '". $categoryName ."',
                    value:          '". $product->getFinalPrice() ."',
                    currency:       '". $this->_getStoreCurrency() ."'
                });";
            }
        }
    }

    /**
     * Return InitiateCheckout event track
     * @return [type]
     * @author Remmote.com
     * @date   2017-07-27
     */
    public function getInitiateCheckoutEvent()
    {
        $pageSection = $this->_getSection();

        //Check if event is enabled
        if ($this->helper->initiateCheckoutEnabled($this->_getWebsiteId()) &&
            ($pageSection == 'checkout_index_index' || $pageSection == 'checkout_onepage_index' ||
            $pageSection == 'onestepcheckout_index_index' || $pageSection == 'opc_index_index')) {

            //Get cart details
            $quote          = $this->checkoutSession->getQuote();
            $skusArray      = [];
            $useProductId   = $this->helper->getUseProductId($this->_getWebsiteId());

            foreach ($quote->getAllVisibleItems() as $item) {
                $product        = $this->productFactory->create()->load($item->getProductId());
                $skusArray[]    = $useProductId ? $product->getId() : $product->getSku();
                // $skusArray[]    = $useProductId ? $item->getProductId() : $item->getSku();
            }

            return "fbq('track', 'InitiateCheckout',{
                content_ids:    ['". implode(",", $skusArray). "'],
                value:          '". $quote->getGrandTotal() ."',
                num_items:      '". $this->checkoutSession->getSummaryCount() ."',
                content_type:   'product',
                currency:       '". $this->_getStoreCurrency() ."'
            });";
        }
    }

    /**
     * Return AddPaymentInfo event track
     * @return [type]
     * @author Remmote.com
     * @date   2017-07-27
     */
    public function getAddPaymentInfoEvent()
    {
        //Check if event is enabled
        if ($this->helper->addPaymentInfoEnabled($this->_getWebsiteId())
            && $this->checkoutSession->getPixelPaymentInfo()) {
            //Unset event
            $this->checkoutSession->unsPixelPaymentInfo();
            return "fbq('track', 'AddPaymentInfo');";
        }
    }

    /**
     * Return Purchase event track
     * @return [type]
     * @author Remmote.com
     * @date   2017-07-27
     */
    public function getPurchaseEvent()
    {
        //Check if event is enabled
        if ($this->helper->purchaseEnabled($this->_getWebsiteId()) && $this->checkoutSession->getPixelPurchase()) {

            $pageSection = $this->_getSection();

            //Unset event
            $this->checkoutSession->unsPixelPurchase();

            $orderId         = $this->checkoutSession->getLastRealOrderId();
            $order           = $this->orderModel->loadByIncrementId($orderId);
            $orderGrandTotal = $order->getGrandTotal();

            $skusArray = [];
            $useProductId = $this->helper->getUseProductId($this->_getWebsiteId());
            foreach ($order->getAllVisibleItems() as $item) {
                $product        = $this->productFactory->create()->load($item->getProductId());
                $skusArray[]    = $useProductId ? $product->getId() : $product->getSku();
                // $skusArray[] = $useProductId ? $item->getProductId() : $item->getSku();
            }

            return "fbq('track', 'Purchase', {
                content_ids: ['". implode("','", $skusArray) ."'],
                content_type: 'product',
                value: '". $orderGrandTotal ."',
                currency: '". $this->_getStoreCurrency() ."',
                num_items: '". number_format($order->getTotalQtyOrdered(), 0) ."'
            });";
        }
    }

    /**
     * Return Lead event track
     * @return [type]
     * @author Remmote.com
     * @date   2017-07-27
     */
    public function getLeadEvent()
    {
        //Check if event is enabled
        if ($this->helper->leadEnabled($this->_getWebsiteId()) && $this->newsletterSession->getPixelLead()) {
            //Unset event
            $this->newsletterSession->unsPixelLead();
            return "fbq('track', 'Lead');";
        }
    }

    /**
     * Return CompleteRegistration event track
     * @return [type]
     * @author Remmote.com
     * @date   2017-07-27
     */
    public function getCompleteRegistrationEvent()
    {
        //Check if event is enabled
        if ($this->helper->completeRegistrationEnabled($this->_getWebsiteId())
            && $this->customerSession->getPixelCompleteRegistration()) {
            //Unset event
            $this->customerSession->unsPixelCompleteRegistration();
            return "fbq('track', 'CompleteRegistration');";
        }
    }

}