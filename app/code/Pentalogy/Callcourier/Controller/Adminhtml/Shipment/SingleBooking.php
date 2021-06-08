<?php

namespace Pentalogy\Callcourier\Controller\Adminhtml\Shipment;

use Magento\Sales\Model\Order;
use Pentalogy\Callcourier\Model\Api\Integration;

class SingleBooking extends \Magento\Backend\App\Action
{


    protected $resultPageFactory;
    protected $shipmentSender;
    protected $trackFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->shipmentSender = $shipmentSender;
        $this->trackFactory = $trackFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $response= array();
        $booking = new Integration();
        $consigneeInfo = array();
        $post = $this->getRequest()->getPostValue();
        $consigneeInfo['ConsigneeRefNo'] = trim($post['ConsigneeRefNo']);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_order = $objectManager->get('\Magento\Sales\Api\Data\OrderInterface')->loadByIncrementId($consigneeInfo['ConsigneeRefNo']);
        if($_order->getPayment()->getMethod() == 'etisalatpay') {
            if ($_order->getStatus() == 'capture') {
                if ($this->getRequest()->isAjax()) {
                    $consigneeInfo['ConsigneeName'] = trim($post['ConsigneeName']);
                    $consigneeInfo['ConsigneeRefNo'] = trim($post['ConsigneeRefNo']);
                    $consigneeInfo['ConsigneeCellNo'] = $post['ConsigneeCellNo'];
                    $consigneeInfo['Address'] = trim($post['Address']);
                    $consigneeInfo['ServiceTypeId'] = $post['ServiceTypeId'];
                    $consigneeInfo['DestCityId'] = $post['DestCityId'];
                    $consigneeInfo['Description'] = trim($post['Description']);
                    $consigneeInfo['CodAmount'] = $post['CodAmount'];
                    $consigneeInfo['Weight'] = $post['Weight'];
                    $consigneeInfo['Pcs'] = $post['Pcs'];
                    $consigneeInfo['Remarks'] = trim($post['Remarks']);
                    $shipperInfo = $booking->getSystemConfigurationData();

                    $curlData = array_merge($shipperInfo, $consigneeInfo);
                    $shipResponse = $booking->createShipment($curlData);
                    if ($shipResponse['status'] == true) {
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        $_order = $objectManager->get('\Magento\Sales\Api\Data\OrderInterface')->loadByIncrementId($consigneeInfo['ConsigneeRefNo']);
                        $this->_createShipment($_order, $shipResponse);
                        $response = array('error' => false, 'message' => $shipResponse['message']);
                    } else {
                        $response = array('error' => $shipResponse['status'], 'message' => $shipResponse['message']);
                    }
                }
            }else{
                $this->messageManager->addErrorMessage('Capture Status Required For Shipment');
            }
        }else
        {
            if ($this->getRequest()->isAjax()) {
                $consigneeInfo['ConsigneeName'] = trim($post['ConsigneeName']);
                $consigneeInfo['ConsigneeRefNo'] = trim($post['ConsigneeRefNo']);
                $consigneeInfo['ConsigneeCellNo'] = $post['ConsigneeCellNo'];
                $consigneeInfo['Address'] = trim($post['Address']);
                $consigneeInfo['ServiceTypeId'] = $post['ServiceTypeId'];
                $consigneeInfo['DestCityId'] = $post['DestCityId'];
                $consigneeInfo['Description'] = trim($post['Description']);
                $consigneeInfo['CodAmount'] = $post['CodAmount'];
                $consigneeInfo['Weight'] = $post['Weight'];
                $consigneeInfo['Pcs'] = $post['Pcs'];
                $consigneeInfo['Remarks'] = trim($post['Remarks']);
                $shipperInfo = $booking->getSystemConfigurationData();

                $curlData = array_merge($shipperInfo, $consigneeInfo);
                $shipResponse = $booking->createShipment($curlData);
                if ($shipResponse['status'] == true) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $_order = $objectManager->get('\Magento\Sales\Api\Data\OrderInterface')->loadByIncrementId($consigneeInfo['ConsigneeRefNo']);
                    $this->_createShipment($_order, $shipResponse);
                    $response = array('error' => false, 'message' => $shipResponse['message']);
                } else {
                    $response = array('error' => $shipResponse['status'], 'message' => $shipResponse['message']);
                }
            }
        }

        echo json_encode($response);
        return false;
    }

    private function _createShipment($_order, $shipResponse)
    {
        if ($_order->canShip()) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $convertOrder = $objectManager->create('Magento\Sales\Model\Convert\Order');
            $shipment = $convertOrder->toShipment($_order);
            foreach ($_order->getAllItems() AS $orderItem) {
                if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                    continue;
                }
                $qtyShipped = $orderItem->getQtyToShip();
                $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
                $shipment->addItem($shipmentItem);
            }

            /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
            $track = $this->trackFactory->create();
            $track->setCarrierCode('callcourier');
            $track->setTitle('Call Courier');
            $track->setTrackNumber($shipResponse['consigneeNo']);
            $shipment->addTrack($track);

            $shipment->register();
            $shipment->getOrder()->setIsInProcess(true);
            try {
                /*$this->_objectManager->create('Magento\Shipping\Model\ShipmentNotifier')
                    ->notify($shipment);*/
                $shipment->save();
                $shipment->getOrder()->setState(Order::STATE_COMPLETE)->setStatus($shipment->getOrder()->getConfig()->getStateDefaultStatus(Order::STATE_COMPLETE));
                $shipment->getOrder()->save();
                foreach ($_order->getShipmentsCollection() as $_shipment) {
                    $this->addTrackingNumbersToShipment($_shipment->getId(), $shipResponse['consigneeNo']);
                }
                $this->shipmentSender->send($shipment);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($e->getMessage())
                );
            }
        }
    }


    private function addTrackingNumbersToShipment($shipmentId, $trackingNumber)
    {
        $carrierCode = Integration::COURIER_CODE;
        $carrierTitle = Integration::COURIER_TITLE;
        $carrier = $carrierCode;
        $number = $trackingNumber;
        $title = $carrierTitle;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $objectManager->get('Magento\Framework\Registry')->unregister('current_shipment');
        $shipmentLoader = $objectManager->get('Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader');
        $shipmentLoader->setShipmentId($shipmentId);
        $shipment = $shipmentLoader->load();

        if ($shipment) {
            $track = $objectManager->create(
                \Magento\Sales\Model\Order\Shipment\Track::class
            )->setNumber(
                $number
            )->setCarrierCode(
                $carrier
            )->setTitle(
                $title
            );
            $shipment->addTrack($track)->save();
        }

    }
}