<?php
/**
 * @extension   Remmote_Facebookpixelremarketing
 * @author      Remmote
 * @copyright   2017 - Remmote.com
 * @descripion  Product catalog model
 */
namespace Remmote\Facebookpixelremarketing\Model;

class Productcatalog extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Remmote\Facebookpixelremarketing\Helper\Data
     */
    public $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    public $resourceConfig;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    public $directoryList;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    public $stockFilter;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    public $imageHelper;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    public $catalogHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    public $categoryFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public $productFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    public $stockState;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    public $logger;

    public function __construct(
        \Remmote\Facebookpixelremarketing\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\CatalogInventory\Helper\Stock $stockFilter,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Helper\Data $catalogHelper,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productFactory,
        \Magento\CatalogInventory\Api\StockStateInterface $stokeState,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->helper          = $helper;
        $this->storeManager    = $storeManager;
        $this->resourceConfig  = $resourceConfig;
        $this->directoryList   = $directoryList;
        $this->_scopeConfig     = $scopeConfig;
        $this->stockFilter     = $stockFilter;
        $this->imageHelper     = $imageHelper;
        $this->catalogHelper   = $catalogHelper;
        $this->categoryFactory = $categoryFactory;
        $this->productFactory  = $productFactory;
        $this->stockState      = $stokeState;
        $this->logger          = $logger;
    }

    /**
     * Exports the product catalog to media/facebook_productcatalog folder
     * Fields exported are the required fields to create standard dynamic ads in Facebook
     * @return [type]
     * @author Remmote
     * @date   2017-07-18
     */
    public function exportCatalog($websiteId = null)
    {
        //Load website and catalog path
        $website     = $this->storeManager->getWebsite($websiteId);
        $catalogPath = $this->directoryList->getPath('media') . DIRECTORY_SEPARATOR
            . 'facebook_productcatalog';

        //Check if facebook_productcatalog dir exist, if not create it
        if (!file_exists($catalogPath)) {
            mkdir($catalogPath, 0777, true);
            chmod($catalogPath, 0777);
        }

        //Generate CSV files by store
        foreach ($website->getStores() as $store) {
            if (!$store->isActive()) {
                continue;
            }
            $storeCurrency  = $store->getCurrentCurrency()->getCode();
            $storeMediaUrl  = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';
            $this->_generateStoreCsvFile($catalogPath, $websiteId, $website->getCode(), $store->getId(), $store->getCode(), $storeCurrency, $storeMediaUrl);
        }

        // Save time last export on data base
        $this->resourceConfig->saveConfig(
            \Remmote\Facebookpixelremarketing\Helper\Data::TIME_LASTEXPORT,
            date("M j, Y") . '  at  ' . date("g:iA"),
            $websiteId ? \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES : 'default',
            $websiteId ? $websiteId : ''
        );
    }

    /**
     * Genarate store product catalog file
     * @param  [type] $catalogPath
     * @param  [type] $websiteId
     * @param  [type] $websiteCode
     * @param  [type] $storeId
     * @param  [type] $storeCode
     * @param  [type] $storeCurrency
     * @param  [type] $storeMediaUrl
     * @return [type]
     * @author Remmote
     * @date   2017-07-18
     */
    public function _generateStoreCsvFile(
        $catalogPath,
        $websiteId,
        $websiteCode,
        $storeId,
        $storeCode,
        $storeCurrency,
        $storeMediaUrl
    ) {
        $currentDate = date('Y-m-d');
        //Get category collection
        $categoriesCollection = $this->categoryFactory->create()
            ->addAttributeToSelect(array('entity_id', 'remmote_google_taxonomy'))
            ->addIsActiveFilter();
        //Set category array with entity id as key and Remmote Google Taxonomy as value
        $categoriesArray = [];
        foreach ($categoriesCollection as $category) {
            $categoriesArray[$category->getEntityId()] = $category->getRemmoteGoogleTaxonomy();
        }
        //Get product collection
        $productsCollection = $this->productFactory->create();
        $productsCollection->setStoreId($storeId);
        if ($websiteId) {
            $productsCollection->addWebsiteFilter(array($websiteId));
        }
        //Getting configurable products information
        $configurableProductsCollection = $this->productFactory->create();
        $configurableProductsCollection->setStoreId($storeId);
        if ($websiteId) {
            $configurableProductsCollection->addWebsiteFilter(array($websiteId));
        }
        $configurableProductsCollection->addAttributeToFilter('status', [
            'eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        ]);
        $configurableProductsCollection->addAttributeToFilter('type_id', ['eq' => 'configurable']);
        $childProducts = array();
        $configurableProductPrices = array();
        foreach ($configurableProductsCollection as $configurableProduct) {
            $configurableProductPrices[$configurableProduct->getId()] = array(
                'price' => 0,
                'specialPrice' => 0,
                // added stock status based on child product
                'stockStatus' => 0
            );
            $_children = $configurableProduct->getTypeInstance()->getUsedProducts($configurableProduct);
            foreach ($_children as $child) {
                $childProducts[$child->getId()] = array(
                    'parentId' => $configurableProduct->getId(),
                    'parentUrl' => $configurableProduct->getProductUrl(),
                );
                $childProductStockStauts = $this->stockState->getStockQty($child->getId(),
                    $child->getStore()->getWebsiteId());
                //stock status set if any of the child product status is zero
                if ($childProductStockStauts) {
                    $configurableProductPrices[$configurableProduct->getId()]['stockStatus'] = $childProductStockStauts;
                }
                if ($configurableProductPrices[$configurableProduct->getId()]['price'] == 0 ||
                    $configurableProductPrices[$configurableProduct->getId()]['price'] > $child->getPrice()) {
                    $configurableProductPrices[$configurableProduct->getId()]['price'] = $child->getPrice();
                    if ($child->getData('special_price')) {
                        $configurableProductPrices[$configurableProduct->getId()]['specialPrice'] = $child->getData('special_price');
                    }
                }
            }
        }
        //Prepare header for csv file
        $csvHeader = [
            'id',
            'title',
            'google_product_category',
            'description',
            'link',
            'image_link',
            'condition',
            'availability',
            'price',
            'sale_price',
            'brand',
            'color'
        ];
        //Prepare attributes to select products collection
        $attributesToSelect = [
            'id',
            'sku',
            'type_id',
            'name',
            'description',
            'short_description',
            'facebook_product_description',
            'url_path',
            'status',
            'price',
            'price_type',
            'special_price',
            'special_from_date',
            'special_to_date',
            'final_price',
            'tax_class_id',
            'brand',
            'manufacturer',
            'color',
            'image'
        ];
        //Getting extra attributes
        $extraAttributes = $this->helper->getExtraAttributes($websiteId);
        if ($extraAttributes) {
            //Including extra attributes to attributesToSelect and csvHeader
            $attributesToSelect = array_merge($attributesToSelect, $extraAttributes);
            $csvHeader = array_merge($csvHeader, $extraAttributes);
        }
        $productsCollection->addAttributeToSelect($attributesToSelect);
        $productsCollection->joinTable('cataloginventory_stock_item', 'product_id=entity_id',
            array('qty', 'is_in_stock'));
        //Filter by status enabled
        $productsCollection->addAttributeToFilter('status', [
            'eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        ]);
        //Filter by visibility
        if ($this->helper->exportProductsNotVisibleIndEnabled($websiteId)) {
            //Not visible individually and Catalog, Search
            $productsCollection->addAttributeToFilter('visibility', [
                'in' => [
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE,
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
                ]
            ]);
        } else {
            //Visible in Catalog, Search
            $productsCollection->addAttributeToFilter(
                'visibility',
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
            );
        }
        //Check if apply 'Use for Facebook Product Catalog' filter.
        if (!$this->helper->exportAllEnabled($websiteId)) {
            $productsCollection->addAttributeToFilter('facebook_product_catalog', ['eq' => 1]);
        }
        $productsCollection->load();
        //Define product catalog filename
        $filename = 'products_' . $websiteCode . '_' . $storeCode . '.csv';
        // Prepare csv file with field names
        $fopen = fopen($catalogPath . DIRECTORY_SEPARATOR . $filename, 'w');
        fputcsv($fopen, $csvHeader, ",");
        // Prepare vars from helper to improve performance
        $includeTax = $this->helper->includeTax($websiteId);
        $useProductId = $this->helper->getUseProductId($websiteId);
        foreach ($productsCollection as $product) {
            //Skip products with no price, as for example grouped products
            if (!$product->getData('price') && !array_key_exists($product->getId(), $configurableProductPrices)) {
                continue;
            }
            //Preparing price and special price
            if ($includeTax) {
                if (array_key_exists($product->getId(), $configurableProductPrices)) {
                    $price = $this->catalogHelper->getTaxPrice($product,
                        $configurableProductPrices[$product->getId()]['price'], true);
                    $specialPrice = $configurableProductPrices[$product->getId()]['specialPrice'];
                } else {
                    $price = $this->catalogHelper->getTaxPrice($product, $product->getData('price'), true);
                    $specialPrice = $product->getData('price');
                    if ($product->getData('special_price')) {
                        $specialPrice = $product->getData('special_price');
                    }
                    if ($product->getTypeId() == 'bundle') {
                        if ($product->getData('special_price')) {
                            $specialPrice = $product->getData('price') * ($product->getData('special_price') / 100);
                        }
                    }
                }
                $specialPrice = $this->catalogHelper->getTaxPrice($product, $specialPrice, true);
            } else {
                if (array_key_exists($product->getId(), $configurableProductPrices)) {
                    $price = $this->catalogHelper->getTaxPrice($product,
                        $configurableProductPrices[$product->getId()]['price'], true);
                    $specialPrice = $configurableProductPrices[$product->getId()]['specialPrice'];
                } else {
                    $price = $product->getData('price');
                    $specialPrice = $product->getData('price');
                    if ($product->getData('special_price')) {
                        $specialPrice = $product->getData('special_price');
                    }
                    if ($product->getTypeId() == 'bundle') {
                        if ($product->getData('special_price')) {
                            $specialPrice = $price * ($specialPrice / 100);
                        }
                    }
                }
            }
            if (array_key_exists($product->getId(), $configurableProductPrices)) {
                $availability = $configurableProductPrices[$product->getId()]['stockStatus'] ? 'in stock' : 'out of stock';
            } else {
                $availability = $product->getIsInStock() ? 'in stock' : 'out of stock';
            }
            // Get brand attribute
            if ($brand = $product->getData('brand')) {
                $brand = $product->getAttributeText('brand');
            } elseif ($product->getData('manufacturer')) {
                $brand = $product->getAttributeText('manufacturer');
            }
            // Get color attribute
            if ($color = $product->getData('color')) {
                $color = $product->getAttributeText('color');
            }
            // Load image attribute
            $imageLink = $this->imageHelper->getDefaultPlaceholderUrl('image');
            if ($product->getImage()) {
                $imageLink = $storeMediaUrl . $product->getImage();
            }
            //Get product category (lowest category level)
            $productCategories = $product->getCategoryIds();
            $googleProductCategory = '';
            if (count($productCategories) >= 1) {
                $categoryId = end($productCategories);
                $googleProductCategory = !empty($categoriesArray[$categoryId]) ? $categoriesArray[$categoryId] : '';
            }
            //Getting product description and remmoving HTML tags
            if ($this->helper->useProductDescription($websiteId)) {
                $_description = $product->getDescription();
            } else {
                $_description = $product->getShortDescription();
            }
            $description = $product->getFacebookProductDescription() ? $product->getFacebookProductDescription() : $_description;
            $description = strip_tags(preg_replace("/\s+|\n+|\r/", ' ', $description));
            //Getting product URL
            $productUrl = $product->getProductUrl();
            //If product is not visile individually (child product), use parent product URL
            if ($product->getVisibility() == \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE) {
                if (isset($childProducts[$product->getId()])) {
                    $productUrl = $childProducts[$product->getId()]['parentUrl'];
                }
            }

            $productUrl = str_replace("botiga_sklep_administrador/","",$productUrl);
            if(strpos($productUrl, "/key/")){
                $productUrl = substr($productUrl, 0, strpos($productUrl, "/key/"));
            }

            $csvProductAttributes = [
                $useProductId ? $product->getId() : $product->getSku(), //id
                $product->getName(), //title
                $googleProductCategory, //google_product_category
                $description, //description
                $productUrl,
                $imageLink, //image_link
                'new', //condition
                $availability, //availability
                number_format($price, 2) . " " . $storeCurrency, //price
                number_format($specialPrice, 2) . " " . $storeCurrency, //sale_price
                $brand ? $brand : 'Undefined', //brand
                $color ? $color : 'Undefined' //color
            ];
            if ($extraAttributes) {
                foreach ($extraAttributes as $attribute) {
                    if ($attr = $product->getData($attribute)) {
                        $attr = $product->getAttributeText($attribute);
                    }
                    $csvProductAttributes[] = $attr ? $attr : 'Undefined';
                }
            }
            fputcsv($fopen, $csvProductAttributes, ",");
        }
        fclose($fopen);
    }
}