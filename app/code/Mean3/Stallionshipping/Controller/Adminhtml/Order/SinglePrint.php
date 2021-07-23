<?php
namespace Mean3\Stallionshipping\Controller\Adminhtml\Order;

Use \Magento\Framework\View\Asset\Repository;

class SinglePrint extends \Magento\Backend\App\Action
{
    protected $order;
    protected $request;
    protected $helper;
    protected $_assetRepo;
  
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Mean3\Stallionshipping\Helper\Data $helper
    ) {
        $this->helper = $helper;
        $this->_assetRepo = $assetRepo;
        $this->request = $context->getRequest();
        $this->order = $order;
        parent::__construct($context);
    }

    public function getOrder()
    {
        $orderId = $this->request->getParam("order_id");
        $order = $this->order->load($orderId, 'entity_id');
        return $order;
    }
    
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id = $this->request->getParam("order_id")) {
            $order = $this->getOrder();
            $orderIdBook = $order->getId();
                $item_name = array();

            $companyInfo = $this->helper->getCompanyInfo();
            if($companyInfo['companyNoHide'] === "1"){
                $companyNo ="<b>Company Phone:</b>Hide";
            }else{
                $companyNo =$companyInfo['companyNo'];
            }

                $tracksCollection =  $order->getTracksCollection();
                foreach ($tracksCollection->getItems() as $track) {
                    $trackingNumber = $track->getTrackNumber();
                    $code = $track->getDescription();
                    $chargeTypeForLabel = $track->getData('charge_type_stallion');
                }

                if (!empty($trackingNumber)) {
                    $orderId = $order->getRealOrderId();

                    $shippingaddress = $order->getShippingAddress();
                    $shippingtelephone = $shippingaddress->getTelephone();
                    $shippingstreet = $shippingaddress->getStreet();
                    $shippingpostcode = $shippingaddress->getPostcode();
                    $shippingcity = $shippingaddress->getCity();
                    $shippingstate = $shippingaddress->getRegion();


                    foreach($order->getShipmentsCollection() as $shipment){
                        /** @var $shipment Mage_Sales_Model_Order_Shipment */
                        $shipmentDate = $shipment->getCreatedAt();
                    }

                    $orderItems = $order->getAllItems();

                    foreach ($orderItems as $item) {
                        $item_name[] = $item->getName() . ' ' . number_format($item->getQtyOrdered());
                    }

                    $weight = number_format($order->getWeight());

                    $receiver = array(
                        'receiver_name' => $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname(),
                        'receiver_phone' => $shippingtelephone,
                        'receiver_address' => $shippingstreet[0] . ' ' . $shippingpostcode . ' ' . $shippingcity . ' ' . $shippingstate,
                        'receiver_postcode' => $shippingpostcode,
                    );

                    //getting order payment method
                    $payment = $order->getPayment();
                    $method = $payment->getMethodInstance();
                    $paymentMethod = $payment->getMethod();

                    if($paymentMethod == "cashondelivery"){
                        $orderAmount = $order->getGrandTotal();
                    }else{
                        $orderAmount = "0";
                    }

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
                                $orderItemsArray[] = $orderItem ." x ". intval($item->getQtyOrdered());
                                $childQuantity = $childQuantity === 0 ? $item->getQtyOrdered() : $childQuantity + $item->getQtyOrdered();
                            }
                        }
                        elseif(!$item->getParentItemId()){
                                $orderItem = $item->getName();
                                $orderItemsArray[] = $orderItem ." x ". intval($item->getQtyOrdered());
                                $parentQuantity = $parentQuantity === 0 ? $item->getQtyOrdered() : $parentQuantity + $item->getQtyOrdered();
                        }
                        
                        
                    }
                    //count order product quantity
                    $Quantity = $childQuantity + $parentQuantity;

                    //convert product name in single string with ;
                    $itemType = implode(';',$orderItemsArray);
                    for ($repeat = 1; $repeat <= 2 ; $repeat++) {
                    print_r('<html>
                    <head>
                        <title>Staallion Waybill</title>
                        <style>
                            *{
                        margin:0;
                        padding: 0;
                        }
                        </style>
                        <script src="'.$this->_assetRepo->getUrl('Mean3_Stallionshipping::js/JsBarcode.all.min.js').'"></script>
                    </head>
                    <body>
                        <div style="padding:20px 10px 20px 10px">
                            <table border="2" cellspacing="0"  style="text-align:center;width: 100%;font-size: 14px;">
                                <tr>
                                    <th colspan="2" style="border-right: none;text-align: right; ">
                                        <img  style=" width: 10%; padding: 5px 10px 5px 5px;" src="'.$this->_assetRepo->getUrl("Mean3_Stallionshipping::images/logo.png").'"> 
                                    </th>
                                    <th colspan="2"  style="border-left: none;text-align: left;">
                                        
                                        <h5 style="margin-bottom: 0px;  margin-top: 2px; margin-left: 5px; color: red"><u><b>Stallion Deliveries</b></u></h5>
                                        <h6 style="margin-top: 0px; margin-left: 20px; margin-bottom: 15px; " >Customer Copy</h6>
                                    </th> 
                                </tr>
                                <tr>
                                    <td colspan="4"><b>Order ID:'.$trackingNumber.':<span style="font-size: 16px">'.$orderId.'</span></b></td>
                                </tr>
                                <tr>
                                    <td colspan="2" width="50%">Consignee Info</td>
                                    <td colspan="2">Shiper Info</td>
                                </tr>
                                <tr>
                                    <td>Consignee Name</td>
                                    <td >'. $receiver['receiver_name'] .'</td>
                                    <td>Company Name</td>
                                    <td>'.$companyInfo['companyName'].'</td>
                                </tr>
                                <tr>
                                    <td>Consignee Address</td>
                                    <td>' . $receiver['receiver_address'] . '</td>
                                    <td>Company Phone</td>
                                    <td>'.$companyNo .'</td>
                                </tr>
                                <tr>
                                    <td>Consignee City</td>
                                    <td>'.$shippingcity.'</td>
                                    <td>StatusName</td>
                                    <td>Parcel Booked</td>
                                </tr>
                                <tr>
                                    <td>Consignee Phone</td>
                                    <td>' . $receiver['receiver_phone'] . '</td>
                                    <td>Cityname</td>
                                    <td>'.$companyInfo['companyCity'].'</td>
                                </tr>
                                <tr>
                                    <td>Booked Time</td>
                                    <td>'.$shipmentDate.'</td>
                                    <td>Charge Type</td>
                                    <td>'.$chargeTypeForLabel.'</td>
                                </tr>
                                <tr>
                                    <td rowspan="3" colspan="2" cellspacing="0"><div>
                                    <img id="barcode2" width="380" height="80" />
                                    <script>
                                        JsBarcode("#barcode2", "'.$trackingNumber.'", {
                                        format:"CODE39",
                                        displayValue:true,
                                        fontSize:24,
                                        text: "*'.$trackingNumber.'*",
                                        lineColor: "#000"
                                        });
                                    </script>
                                </div></td>
                                    <td colspan="2">
                                        <br>
                                        <table border="1" cellspacing="0" style="text-align:center;width:100%;font-size: 12px;" > 
                                            <tr>
                                                <td><b>Quantity</b></td>	
                                                <td><b>ItemType</b></td>
                                                <td><b>CODAmount</b></td>	
                                            </tr>
                                            <tr>
                                                <td>'.intval($Quantity).'</td>	
                                                <td> '.$itemType.' </td>
                                                <td>'.intval($orderAmount).'</td>		
                                            </tr>
                                        </table>
                                        <tr><td colspan="2"><b>Special Instruction</b></td></tr>
                                        <tr><td colspan="2" style="padding: 10px;"> </td></tr>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    <hr style="border: dotted;" >
                    </body>
                    </html>');
                    }}

        } else {
            $this->messageManager->addError(__('Order does not exist.'));
        }

    }
}
