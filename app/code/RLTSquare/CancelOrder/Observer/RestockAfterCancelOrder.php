<?php
namespace RLTSquare\CancelOrder\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use \Magento\Framework\App\RequestInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Model\ProductRepository;
use \Magento\Framework\Message\ManagerInterface;
//use RLTSquare\CancelOrder\Helper\Data;

class RestockAfterCancelOrder implements ObserverInterface {

 protected $order;

 protected $_request;

 protected $productRepository;

 protected $stockRegistry;

 protected $messageManager;

 //protected $helperData;


 public function __construct(
    Order $order,
    RequestInterface $request,
    ProductRepository $productRepository,
    StockRegistryInterface $stockRegistry,
    ManagerInterface $messageManager
    //Data $helperData
 ) {
     $this->order = $order;
     $this->_request = $request;
     $this->productRepository = $productRepository;
     $this->stockRegistry = $stockRegistry;
     $this->messageManager = $messageManager;
     //$this->helperData = $helperData;
 }

 public function execute(Observer $observer) {

     try {
         $order = $observer->getEvent()->getOrder();
         $productIds = $this->_request->getParam('products_restock');
         $explodedProductIds = explode(",", $productIds);
         $items = $order->getAllItems();
         foreach($items as $item) {
            $productId = $item->getProductId();
            if(!in_array($productId, $explodedProductIds)) {
                $product = $this->productRepository->getById($productId);
                $sku = $product->getSku();
                $stockItem = $this->stockRegistry->getStockItemBySku($sku);
                $qty = $stockItem->getQty() - $item->getQtyOrdered();
                $stockItem->setQty($qty);
                $stockItem->setIsInStock((bool)$qty);
                $this->stockRegistry->updateStockItemBySku($sku, $stockItem);
            }
         }
         $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/observer_code_testing.log');
         $logger = new \Zend\Log\Logger();
         $logger->addWriter($writer);
         $logger->info(print_r($this->_request->getParams(), true));

     }
     catch (\Exception $e)
     {
         return $e->getMessage();
     }

 }

}