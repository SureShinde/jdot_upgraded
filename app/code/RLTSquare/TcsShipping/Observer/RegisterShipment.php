<?php
/**
 * NOTICE OF LICENSE
 * You may not sell, distribute, sub-license, rent, lease or lend complete or portion of software to anyone.
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @package   RLTSquare_TcsShipping
 * @copyright Copyright (c) 2018 RLTSquare (https://www.rltsquare.com)
 * @contacts  support@rltsquare.com
 * @license  See the LICENSE.md file in module root directory
 */

namespace RLTSquare\TcsShipping\Observer;

/**
 * Class RegisterShipment
 * @package RLTSquare\TcsShipping\Observer
 * @author Umar Chaudhry <umarch@rltsquare.com>
 */
class RegisterShipment implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \RLTSquare\TcsShipping\Helper\TcsRegisterar
     */
    protected $_tcsRegistrar;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     */
    protected $_trackFactory;

    protected $_request;

    /**
     * RegisterShipment constructor.
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory
     * @param \RLTSquare\TcsShipping\Helper\TcsRegisterar $tcsRegistrar
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \RLTSquare\TcsShipping\Helper\TcsRegisterar $tcsRegistrar,
        \Magento\Framework\App\RequestInterface $request)
    {
        $this->_messageManager = $messageManager;
        $this->_trackFactory = $trackFactory;
        $this->_tcsRegistrar = $tcsRegistrar;
        $this->_request = $request;  
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipment = $observer->getShipment();
        $order = $shipment->getOrder();

        $selectedPaymentMethod = $order->getPayment()->getMethod();
        $selectedShippingMethod = $order->getShippingMethod();

        $postData = $this->_request->getPost();

        //if selected shipping is tcs and payment method is cash on delivery, register shipment in api
        if ($selectedShippingMethod == "tcsshipping_tcsshipping" && $selectedPaymentMethod == "cashondelivery") {
            if($postData['shipment']['custom_weight'] > 0 ){
	            $consignmentNo = $this->_tcsRegistrar->shipOrder($order, $postData['shipment']['custom_weight']);            	
            }else{
	            $consignmentNo = $this->_tcsRegistrar->shipOrder($order);            	
            }

            
            //$consignmentNo = "DUMMY_NUMBER";
            if ($consignmentNo) {
                //save tracking number in magento
                $data = array(
                    'carrier_code' => 'tcsshipping',
                    'title' => 'TCS COD Shipping',
                    'number' => $consignmentNo
                );
                $track = $this->_trackFactory->create()->addData($data);
                $shipment->addTrack($track);
            } else {
                $message = "Error getting tracking info from TCS API";
                $this->_messageManager->addError($message);
            }
        }
    }
}
