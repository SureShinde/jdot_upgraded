<?php
/**
 * Pmclain_Twilio extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GPL v3 License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl.txt
 *
 * @category       Pmclain
 * @package        Twilio
 * @copyright      Copyright (c) 2017
 * @license        https://www.gnu.org/licenses/gpl.txt GPL v3 License
 */

namespace Aalogics\Lcs\Observer\Sales\Lcs;

use Magento\Framework\Event\ObserverInterface;
use \Aalogics\Lcs\Helper\Data as Helper;
use \Aalogics\Lcs\Model\Api\Lcs\Api\Request;
use \Magento\Sales\Model\Order\ShipmentFactory;
use \Magento\Sales\Api\ShipmentRepositoryInterface;
use \Magento\Shipping\Model\ShipmentNotifier;
use \Magento\Sales\Api\ShipmentTrackRepositoryInterface;
use \Magento\Sales\Api\Data\ShipmentTrackInterface;
use \Magento\Sales\Model\Order\Shipment\TrackFactory;

class Ship implements ObserverInterface
{
    /**
     * @var \Pmclain\Twilio\Helper\Data
     */
    protected $_helper;

    /** @var \Magento\Sales\Api\ShipmentRepositoryInterface */
    protected $shipmentRepository;

    /** @var \Magento\Sales\Model\Order\ShipmentFactory */
    protected $shipmentFactory;

    /** @var \Magento\Shipping\Model\ShipmentNotifier */
    protected $shipmentNotifier;

    /** @var \Magento\Sales\Model\Order\Shipment\TrackFactory */
    protected $_trackFactory;

    protected $_trackCollectionFactory;


    /** @var \Magento\Sales\Api\ShipmentTrackRepositoryInterface */
    protected $_trackRepository;

    /** @var \Aalogics\Lcs\Model\Api\Lcs\Api\Request */
    protected $_apiRequest;

    protected $_transaction;

    protected $shipmentSender;

    public function __construct(
        Helper $helper,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollectionFactory,
        \Aalogics\Lcs\Model\Api\Lcs\Api\Request $apiRequest,
        \Magento\Sales\Api\ShipmentTrackRepositoryInterface $trackRepository,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender
    ) {
        $this->_apiRequest = $apiRequest;
        $this->_helper = $helper;
        $this->_shipmentFactory = $shipmentFactory;
        $this->_shipmentNotifier = $shipmentNotifier;
        $this->_shipmentRepository = $shipmentRepository;
        $this->_trackFactory = $trackFactory;
        $this->_trackCollectionFactory = $trackCollectionFactory;
        $this->_trackRepository = $trackRepository;
        $this->_transaction = $transaction;
        $this->shipmentSender = $shipmentSender;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Framework\Event\Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_helper->debug('execute observer sales order ship');

        $destCity = $observer->getData('destCity');

        $order = $observer->getOrder();
        $costcenter = $observer->getCostCenter();
        if($this->_helper->isEnabled()
        ) {
            $this->_helper->debug('lcs ship observer execute Start');
// 	        if ($order->getBillingAddress()->getTelephone()) {
            $this->lcsTrackingNumberShip($order, $costcenter,$destCity);
// 	        }
        }
        return $observer;
    }

    /**
     *
     * @param unknown $observer
     */
    public function lcsTrackingNumberShip($order, $costcenter, $destCity) {
        $message = array ();
        $parameters = array();
        $order->getCustomerId (); // get customer Id
        $parameters['order_id'] = str_replace ( '-', '', $order->getIncrementId () ); // removing - from order id
        // $parameters['costcenter'] = $costcenter;//@todo fix this
        $parameters['consignee_email'] = $order->getCustomerEmail (); // get customer Email
        $parameters['consignee_name'] = $order->getShippingAddress ()->getFirstname () . ' ' . $order->getShippingAddress ()->getLastname (); // get customer Name
        $shipping_address = $order->getShippingAddress (); // get customer Address
        $telephone = $order->getShippingAddress()->getTelephone (); // get customer Telephone
        $parameters['telephone'] = ltrim ( str_replace ( '+92', '', $telephone ), '0' );

        $shipAddress = $order->getShippingAddress();
        $street = $shipAddress->getStreet();

        // $this->_helper->debug('ShipAddress',$shipAddress->debug());

        if(!is_null($destCity)){
            $destinationCityName = $destCity;
        }else{
            $destinationCityName = $shipAddress->getCity (); // load city
        }

        $parameters['destination_city_name'] = $this->_helper->getLcsCityData(strtolower($destinationCityName));

        if(empty($parameters['destination_city_name'])){
            throw new \Exception ( 'Destination City is not Valid' );
        }

        $street1 = isset($street[0]) ? $street[0]: '';
        $street2 = isset($street[1]) ? $street[1]: '';

        $parameters['consignee_address'] = $street1. ' '. $street2; // get customer street address
        $parameters['grant_total'] = $order->getBaseGrandTotal (); // Cod Amount
        $Qty = $order->getData ( 'total_qty_ordered' ); // prodcut Qty
        $parameters['qty'] = round ( $Qty );
        list($parameters['sku'],$parameters['weight']) =  $this->_processWeight($order);
        $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();

        $this->_helper->debug('Parameters',$parameters);
        $this->_helper->debug('Payment Method',$paymentMethod);

        $trackingResponse = $this->_apiRequest->bookCodOrder($parameters);

        // if($paymentMethod != 'cashondelivery') {
        // 	if($this->_helper->isNonCodEnabled()) {
        // 		// $trackingNumber = $this->_apiRequest->trackNonCodOrder($parameters);
        // 	}else {
        // 		$this->_helper->debug('NON COD is not enabled from configurations');
        // 	}
        // }else {
        // 	$trackingNumber = $this->_apiRequest->trackCodOrder($parameters);
        // }

        $this->_helper->debug('trackingResponse: ',$trackingResponse);

        if(isset($trackingResponse['status']) && !$trackingResponse['status']){
            throw new \Exception ( $trackingResponse['error'] );
        }else{
            $this->_addTrackingNumber( $order, $trackingResponse['track_number'],$trackingResponse);
        }

    }

    protected function _processWeight($order) {
        $weight = 0;
        $sku = '';
        // Product Sku
        foreach ( $order->getAllItems () as $item ) {
            /* @var $item Mage_Sales_Model_Quote_Item */
            if ($item->getProduct ()->isVirtual () || $item->getParentItem ()) {
                continue;
            }
            $sku .= $item->getSku();
            $weight += $item->getRowWeight() ;
        }
        //round weight to nearest digit
        if($weight < 0.5) {
            $weight = 0.5;
        }else {
            $weight = round($weight, 1);
        }
        return [$sku,$weight];
    }

    /**
     *
     * @param unknown $observer
     * @throws Exception
     */
    public function lcsTrackingNumberInvoice($observer) {
        // @todo add code here if needed
    }

    /**
     * Prepares tracking data form tracking number.
     *
     * @param $trackingNumber
     *
     * @return \Magento\Sales\Model\Order\Shipment\Track
     */
    protected function setTrackingData($trackingNumber)
    {
        $track = $this->trackingFactory->create();
        $track->setTrackNumber($trackingNumber);
        //Carrier code can not be null/empty. Default carrier code is used
        $track->setCarrierCode('custom');//Put your carrier code here
        $track->setTitle('');//add your title here
        $trackInfo[] = $track;

        return $trackInfo;
    }

    /**
     * Create shipment items required to create shipment.
     *
     * @param array                      $items
     * @param \Magento\Sales\Model\Order $order
     *
     * @return array
     */
    protected function createShipmentItems(array $items, $order)
    {
        $shipmentItem = [];
        foreach ($order->getAllItems() as $orderItem) {
            if (array_key_exists($orderItem->getId(), $items)) {
                $shipmentItem[] = $this->orderConverter
                    ->itemToShipmentItem($orderItem)
                    ->setQty($items[$orderItem->getId()]);
            }
        }

        return $shipmentItem;
    }

    /**
     *
     * @param unknown $_order
     * @param unknown $tracking_no
     */
    protected function _addTrackingNumber($_order, $tracking_no, $trackingResponse) {
        if ($_order->canShip()) {
            $_order->setShippingMethod('aalcs_lcs');
            $this->_helper->debug('creating order shipment');
            $items = [];

            foreach ($_order->getAllItems() AS $orderItem) {
                if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                    continue;
                }
                $qtyShipped = $orderItem->getQtyToShip();
                $items[$orderItem->getId()] = $qtyShipped;
            }

            // create the shipment
            $shipment = $this->_shipmentFactory->create($_order, $items);

            preg_match('/\d{1,9}$/', $tracking_no,$track_num);
            $lcs_track_num = (isset($track_num[0])) ? $track_num[0] : '';

            /**** Create Tracking ****/
            $data = [
                ShipmentTrackInterface::ENTITY_ID => null,
                ShipmentTrackInterface::ORDER_ID => $shipment->getOrderId(),
                ShipmentTrackInterface::PARENT_ID => $shipment->getId(),
                ShipmentTrackInterface::TRACK_NUMBER => $lcs_track_num,
                ShipmentTrackInterface::DESCRIPTION => 'LCS Shipment',
                ShipmentTrackInterface::TITLE => 'LCS',
                ShipmentTrackInterface::CARRIER_CODE => 'aalcs',
                'awb'=> $trackingResponse['slip_link']
            ];
            $track = $this->_trackFactory->create()->addData($data);
            $this->_helper->debug('Track Shipment Data: '.print_r($data,true));

            $shipment->addTrack($track);
            /********** End Tracking **********/

            $shipment->register();

            //$shipment->setTracks($this->_trackCollectionFactory->create()->setShipmentFilter($shipment->getId()));

            $shipment->getOrder()->setIsInProcess(true);
            $shipment->getOrder()->setState(\Magento\Sales\Model\Order::STATE_COMPLETE)->setStatus(\Magento\Sales\Model\Order::STATE_COMPLETE);
            $this->_saveShipment($shipment);

            /******* save the newly created shipment ********/
            $this->_shipmentRepository->save($shipment);

            $this->shipmentSender->send($shipment);

            /* Notify the customer*/
//			$sentShipmentEmail = $this->_helper->getAdminField ( 'lcs_inv_shipp_action/shipment_email' );
//			if($sentShipmentEmail)
//				$this->_shipmentNotifier->notify($shipment);

        } else {
            $this->_helper->debug('in else check can ship');
        }
    }

    /**
     * Save shipment and order in one transaction
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return $this
     */
    protected function _saveShipment($shipment)
    {
        $this->_transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->save();

        return $this;
    }
}