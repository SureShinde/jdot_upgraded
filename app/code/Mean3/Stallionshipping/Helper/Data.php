<?php
namespace Mean3\Stallionshipping\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Simplexml\Element;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;


/**
 * @category   Mean3
 * @package    Mean3_Stallionshipping
 * @author     info@mean3.com
 * @website    http://www.Mean3.com
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    //configure filed data from admin dasboard from filed
    const XML_PATH_ENABLE = 'Mean3_Stallionshipping/general/enabled';
    const XML_PATH_USERNAME = 'Mean3_Stallionshipping/general/userName';
    const XML_PATH_PASSWORD = 'Mean3_Stallionshipping/general/pasword';
    const XML_PATH_BASEURL = 'Mean3_Stallionshipping/general/baseUrl';
    const XML_PATH_ORDER_STATUS = 'Mean3_Stallionshipping/general/order_status';
    const XML_PATH_COMPANY_NAME = 'Mean3_Stallionshipping/general/companyName';
    const XML_PATH_COMPANY_NO = 'Mean3_Stallionshipping/general/companyPhone';
    const XML_PATH_COMPANY_CITY = 'Mean3_Stallionshipping/general/companyCity';
    const XML_PATH_COMPANY_HIDE_NO = 'Mean3_Stallionshipping/general/hideNumber';
    const XML_PATH_CHARGETYPE = 'Mean3_Stallionshipping/general/chargeType';


    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleList;
    protected $invoiceService;
    protected $transaction;
    protected $invoiceSender;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        InvoiceService $invoiceService,
        InvoiceSender $invoiceSender,
        Transaction $transaction
    ) {
        $this->_logger                  = $context->getLogger();
        $this->_moduleList              = $moduleList;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;

        parent::__construct($context);
    }
    public function book($collection)
    {
        $countOrder = 0;
        $unBookedOrdersErrors = array();
        foreach ($collection as $order) {
            $result = $this->bookById($order);
            if (is_numeric($result)) {
                $countOrder++;
            }
            if(strpos($result, 'Wrong User Name or Password') !== false){

                return $result;
            }
            if(strpos($result, 'Connection Error') !== false){

                return $result;
            }
            
            $allowedOrderStatus = $this->getAllowedOrderStatus();
            $orderId = $order->getRealOrderId();
            $unBookedOrdersErrorsForDisplay = array();

            if(!is_numeric($result) && in_array($order->getStatus(), $allowedOrderStatus)){
                
                $unBookedOrdersErrors[] = $orderId.'('.$result.')';
                $unBookedOrdersErrorsForDisplay = implode(', ',$unBookedOrdersErrors);
                    
            }
            if($result == false && !in_array($order->getStatus(), $allowedOrderStatus)){
                $unBookedOrdersErrors[] = $orderId.'(Only selected order status can booked)';
                $unBookedOrdersErrorsForDisplay = implode(', ',$unBookedOrdersErrors);
            }
        }
        return array('countOrder' => $countOrder,
                 'unBookedOrdersErrorsForDisplay' => $unBookedOrdersErrorsForDisplay);
    }

    public function bookById($order)
    {
        $parcelNo = false;
        $allowedOrderStatus = $this->getAllowedOrderStatus();
        if (in_array($order->getStatus(), $allowedOrderStatus)) {
            $orderIdBook = $order->getId();

            $parcelNo = $this->orderApi($orderIdBook);
        }
        if (is_numeric($parcelNo)) {
            return $parcelNo;
        } else {
            return $parcelNo;
        }
    }
    /**
     * Order Api Function
     *
     * @return string|null
     */
    public function orderApi($orderIdBook){

            $storeScopeEnable = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $enable = $this->scopeConfig->getValue(self::XML_PATH_ENABLE, $storeScopeEnable);

            $storeScopeUserName = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $userName = $this->scopeConfig->getValue(self::XML_PATH_USERNAME, $storeScopeUserName);

            $storeScopePassword = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $password = $this->scopeConfig->getValue(self::XML_PATH_PASSWORD, $storeScopePassword);
            
            $storeScopeBaseUrl = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $baseUrl = $this->scopeConfig->getValue(self::XML_PATH_BASEURL, $storeScopeBaseUrl);

            $storeChargeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $chargeType = $this->scopeConfig->getValue(self::XML_PATH_CHARGETYPE, $storeChargeType);

            if($enable === "1"){

                //get placed order data from event
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($orderIdBook);

                // Check if order has already shipped or can be shipped
                if (!$order->canShip()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('You can\'t create an shipment.')
                    );
                }
                
                $orderId = $order->getRealOrderId();   
                $orderDate =  date('m/d/Y', strtotime($order->getCreatedAt()));            
                $customerFirstName = $order->getCustomerFirstname();
                $customerLastName = $order->getCustomerLastname();
                $shippingCity = $order->getShippingAddress()->getCity();
                $shippingAddress =$order->getShippingAddress()->getData();
                $shippingTelephone = $order->getShippingAddress();
                $consignee_address =str_replace("&"," and ", $shippingAddress['street']);
                $shippingAddressApi = $consignee_address;
                $shippingTelephoneToConvert = $shippingTelephone->getTelephone();; 
                $shippingTelephoneApi = strtolower(preg_replace('/[^a-zA-Z0-9]+/','', $shippingTelephoneToConvert));

                //product order product data
                $items = $order->getAllItems();

                $orderItemsArray =array();
                $Quantity = 0;
                $parentQuantity = 0;
                $childQuantity = 0;
                foreach ($items as $item) {
                    //child product logic
                    if ($item->getHasChildren() ) {
                        foreach ($item->getChildrenItems() as $child) {
                            $orderItem = $child->getName();
                            $orderItemsArray[] = $orderItem;
                            $childQuantity = $childQuantity === 0 ? $item->getQtyOrdered() : $childQuantity + $item->getQtyOrdered();
                        }
                    }
                    elseif(!$item->getParentItemId()){
                        $orderItem = $item->getName();
                        $orderItemsArray[] = $orderItem;
                        $parentQuantity = $parentQuantity === 0 ? $item->getQtyOrdered() : $parentQuantity + $item->getQtyOrdered();
                    }
                    
                }
                //count order product quantity
                $Quantity = $childQuantity + $parentQuantity;

                //convert product name in single string with ;
                $itemType = implode(';',$orderItemsArray);


                //getting order payment method
                $payment = $order->getPayment();
                $method = $payment->getMethodInstance();
                $paymentMethod = $payment->getMethod();

                if($paymentMethod == "cashondelivery"){
                    $orderAmount = $order->getGrandTotal();
                }else{
                    $orderAmount = "0";
                }

            //post api hit 
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $baseUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_HEADER => 0,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => 'username='.$userName.'&password='.$password.'&ConsigneeAddress1='.$shippingAddressApi.'&ConsigneeName='.$customerFirstName.' '.$customerLastName.'&ConsigneeCityid='.$shippingCity.'&ConsigneePhone1='.$shippingTelephoneApi.'&ItemType='.$itemType.'&PickupDate='.$orderDate.'&SpecialInstruction=%20&CODAmount='.intval($orderAmount).'&PickupAddressid=&Hide=Show&Quantity='.intval($Quantity).'&shiperOrderId='.$orderId.'&ChargeType='.$chargeType.'&InsuranceAmount=0',
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/x-www-form-urlencoded'
                    ),
                    ));

            $response = curl_exec($curl);
            $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            $curl_error = curl_error($curl);

            if($http_status == 200 ){
                if(curl_errno($curl)){
                    echo $curlError = "cURL Error(".$curl_errno.")". $curl_error;
                    curl_close($curl);
                    
    
                    $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/templog.log');
                    $logger = new \Zend\Log\Logger();
                    $logger->addWriter($writer);
                    $logger->info($curlError); 
    
                    return $curlError;
                }
                else{
    
                    curl_close($curl);
    
                    $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/templog.log');
                    $logger = new \Zend\Log\Logger();
                    $logger->addWriter($writer);
                    $logger->info($response);             
                    
                    //change response into jason array
                    $xml = simplexml_load_string($response, "SimpleXMLElement", LIBXML_NOCDATA);
                    $json = json_encode($xml);
                    $array = json_decode($json,TRUE);
                    $convertedResponse = $array[0];
    
                    //check wether order is placed than change status
                        if(is_numeric($convertedResponse)){
    
                            // Initialize the order shipment object
                            $convertOrder = $objectManager->create('Magento\Sales\Model\Convert\Order');
                            $shipment = $convertOrder->toShipment($order);
    
                            // Loop through order items
                            foreach ($order->getAllItems() as $orderItem) {
                            // Check if order item is virtual or has quantity to ship
                                if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                                    continue;
                                }
    
                                $qtyShipped = $orderItem->getQtyToShip();
    
                            // Create shipment item with qty
                                $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
    
                            // Add shipment item to shipment
                                $shipment->addItem($shipmentItem);
                            }
    
                            // Register shipment
                            $shipment->register();
    
                            $data = array(
                                'carrier_code' => 'stallion',
                                'title' => 'Stallion Shipping ',
                                'number' => $convertedResponse, // Replace with your tracking number
                                '' =>'',
                            );
    
                            $shipment->getOrder()->setIsInProcess(true);
    
                            if($chargeType == "1"){
                                $chargeTypeForSaving = "OverLand";
                            }else{
                                $chargeTypeForSaving = "OverNight";
                            }
    
                            try {
                            // Save created shipment and order
                                $track = $objectManager->create('Magento\Sales\Model\Order\Shipment\TrackFactory')->create()->addData($data);
                                $track->setData('track_url_stallion','https://www.stalliondeliveries.com/trackParcel?Id='.$convertedResponse);
                                $track->setData('charge_type_stallion', $chargeTypeForSaving);
                                $shipment->addTrack($track)->save();
                                $shipment->save();
                                $shipment->getOrder()->save();
    
                            // Send email
                                //  $this->_objectManager->create('Magento\Shipping\Model\ShipmentNotifier')->notify($shipment);
    
                                // $shipment->save();

                                
                            

                            } catch (\Exception $e) {
                                throw new \Magento\Framework\Exception\LocalizedException(
                                    __($e->getMessage())
                                );
                            }
                            
                            $order->setState('processing')->setStatus('booked_stallion');
                            $history = $order->addStatusHistoryComment('Order is booked On Stallion', $order->getStatus());
                            $order->save();

                            if ($order->canInvoice()) {
                                $invoice = $this->invoiceService->prepareInvoice($order);
                                $invoice->register();
                                $invoice->save();
                                $transactionSave = $this->transaction->addObject(
                                    $invoice
                                )->addObject(
                                    $invoice->getOrder()
                                );
                                $transactionSave->save();
                                $this->invoiceSender->send($invoice);
                                // //Send Invoice mail to customer
                                $order->addStatusHistoryComment(
                                    __('Notified customer about invoice creation #%1.', $invoice->getId())
                                )
                                    ->setIsCustomerNotified(true)
                                    ->save();

                            }

                            $bookResponse = $convertedResponse ;
                        }
                        else{
                            $bookResponse = $convertedResponse;
                        }
    
                    return $bookResponse;
                }

            }
            else{
                return "Connection Error";
            }
            
        }
        else{
        }
    }

    public function getExtensionVersion()
    {
        $moduleCode = 'Mean3_Stallionshipping';
        $moduleInfo = $this->_moduleList->getOne($moduleCode);
        return $moduleInfo['setup_version'];
    }

    /**
     *
     * @param $message
     * @param bool|false $useSeparator
     */
    public function log($message, $useSeparator = false)
    {
        if ($this->getDebugStatus()) {
            if ($useSeparator) {
                $this->_logger->addDebug(str_repeat('=', 100));
            }

            $this->_logger->addDebug($message);
        }
    }

    public function getAllowedOrderStatus()
    {
        $statuses = $this->scopeConfig->getValue(
            self::XML_PATH_ORDER_STATUS,
            ScopeInterface::SCOPE_STORE
        );
        $allowedStatus = explode(",", $statuses);
        return $allowedStatus;
    }

    public function getCompanyInfo()
    {
        $company = array(
            'companyName' => $this->scopeConfig->getValue(self::XML_PATH_COMPANY_NAME,ScopeInterface::SCOPE_STORE),
            'companyNo' =>$this->scopeConfig->getValue(self::XML_PATH_COMPANY_NO,ScopeInterface::SCOPE_STORE) ,
            'companyNoHide' =>$this->scopeConfig->getValue(self::XML_PATH_COMPANY_HIDE_NO,ScopeInterface::SCOPE_STORE),
            'companyCity' =>$this->scopeConfig->getValue(self::XML_PATH_COMPANY_CITY,ScopeInterface::SCOPE_STORE),
        );
        
        return $company;
    }

     /***********************************************************
     * General tools functions - linked to Magento system
     ***********************************************************/
    /**
     * Get values from the Magento configuration
     * @param string $field : field in the Magento configuration. Get exact name from /etc/adminhtml/system.xml file
     * @param string $group : group the searched field is belonging to. Get exact name from /etc/adminhtml/system.xml file
     * @param string $section : section in the Magento configuration.
     * @param $scope : scope of the config value
     * @param null|string $scopeCode : scope of the config value
     * @return mixed : saved value of the field
     */
    public function getConfigValue(
        $field = '',
        $group = '',
        $section = '',
        $scope = ScopeInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ){
        // Construct field path in configuration
        if (empty($section) || empty($group) || empty($field)) {
            return '';
        }
        $configValue = $this->scopeConfig->getValue($section.'/'.$group.'/'.$field, $scope, $scopeCode);

        return $configValue;
    }
}