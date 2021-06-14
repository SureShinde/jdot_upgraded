<?php

namespace RLTSquare\ExportProducts\Controller\Adminhtml\Export;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;

class Download extends \Magento\Backend\App\Action implements HttpPostActionInterface
{

    protected $resultRedirectFactory;

    protected $csvWriter;

    protected $fileFactory;

    protected $directoryList;

    protected $collectionFactory;

    protected $attributeSet;

    protected $_escaper;

    protected $_storeManager;

    protected $_stockItemRepository;

    protected $stockItem;

    protected $_category;

    protected $_resource;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\File\Csv $csvWriter,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Magento\CatalogInventory\Model\Stock\Item $stockItem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Category $category,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->csvWriter = $csvWriter;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->fileFactory = $fileFactory;
        $this->_storeManager = $storeManager;
        $this->directoryList = $directoryList;
        $this->attributeSet = $attributeSet;
        $this->_stockItemRepository = $stockItemRepository;
        $this->stockItem = $stockItem;
        $this->collectionFactory = $collectionFactory;
        $this->_category = $category;
        $this->_resource = $resource;

        parent::__construct($context);
    }

    /**
     * Upload and save file
     *
     */
    public function execute()
    {
        $productCollection = $this->collectionFactory->create();
        /** Apply filters here */
        $productCollection->addAttributeToSelect('*');
        $itr = 0;

        $connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $cce = $connection->fetchAll("SELECT * FROM catalog_category_entity");
        $ccev = $connection->fetchAll("SELECT cev.* FROM catalog_category_entity_varchar cev WHERE cev.attribute_id = (SELECT ea.attribute_id FROM eav_attribute ea  WHERE ea.entity_type_id = 3 AND attribute_code = 'name')");

        $cce_kvp = [];
        foreach ($cce as $element){
            $cce_kvp[$element['entity_id']] = array("parent_id" => $element['parent_id'], "level" => $element['level']);
        }


        $ccev_kvp = [];
        foreach ($ccev as $element){
            $ccev_kvp[$element['row_id']] = $element['value'];
        }

        foreach ($productCollection as $productRow) {
            $attributeSetRepository = $this->attributeSet->get($productRow->getAttributeSetId());
            $attribute_set_name = $attributeSetRepository->getAttributeSetName();
            $categories = '';
            $website_code = '';

            foreach($this->_storeManager->getWebsites() as $website){
                if(array_key_exists(0,$productRow->getWebsiteIds())){
                    if($website->getId() == $productRow->getWebsiteIds()[0]){
                        $website_code = $website->getCode();
                        break;
                    }
                }
                else{
                    $website_code = '';
                    break;
                }
            }
            if ($categoryIds = $productRow->getCategoryIds()) {

                for($i = 0; $i< count($categoryIds); $i++ ){
                    $currentCategory = $categoryIds[$i];
                    if(array_key_exists($currentCategory,$ccev_kvp)){
                                        $tmpCat = $ccev_kvp[$currentCategory];
                                        while($cce_kvp[$currentCategory]["level"] > 1){
                                            $currentCategory = $cce_kvp[$currentCategory]["parent_id"];
                                            $tmpCat = $ccev_kvp[$currentCategory] . '/' . $tmpCat;
                                        }

                                        $categories .= $tmpCat;
                                        if($i < count($categoryIds) - 1){
                                            $categories .= ' , ';
                                        }
                    }
                }
            }

            if($itr == 0)
            {
                $data[] = array(
                    'sku',
                    'name',
                    'price',
                    'attribute_set_code',
                    'product_websites',
                    'categories',
                    'product_online',
                    'quantity',
                    'special_price',
                    'special_price_to_date',
                    'special_price_from_date',
                    'image',
                    'short_description',
                    'visibility',
                    'created_at'
                );
            }

            $qty = 0;
            try{
                $stockItem = $this->stockItem->load($productRow->getId(), 'product_id');
                $qty = $stockItem->getQty();
            }
            catch(\Exception $e){
            }
            $data[] = array(
                $productRow->getSku(),
                $productRow->getName(),
                $productRow->getPrice(),
                $attribute_set_name,
                $website_code,
                $categories,
                $productRow->getStatus(),
                $qty,
                $productRow->getSpecial_price(),
                $productRow->getSpecial_to_date(),
                $productRow->getSpecial_from_date(),
                $productRow->getData('image'),
                $productRow->getShortDescription(),
                $productRow->getAttributeText('visibility'),
                $productRow->getCreatedAt()
            );
            $itr++;
        }

        $fileDirectory = \Magento\Framework\App\Filesystem\DirectoryList::MEDIA;
        $fileName = 'Products.csv';
        $filePath =  $this->directoryList->getPath($fileDirectory) . "/" . $fileName;

        $this->csvWriter
            ->setEnclosure('"')
            ->setDelimiter(',')
            ->saveData($filePath ,$data);

        $this->fileFactory->create(
            $fileName,
            [
                'type'  => "filename",
                'value' => $fileName,
                'rm'    => true,
            ],
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA,
            'text/csv',
            null
        );

        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect;
    }
}
