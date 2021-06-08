<?php

namespace RLTSquare\CallCourierInvoiceIssue\Controller\Adminhtml\Shipment;

use Magento\Sales\Model\Order;
use Pentalogy\Callcourier\Model\Api\Integration;

class SingleBooking extends \Pentalogy\Callcourier\Controller\Adminhtml\Shipment\SingleBooking
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Order\Email\Sender\ShipmentSender
     */
    protected $shipmentSender;
    /**
     * @var Order\Shipment\TrackFactory
     */
    protected $trackFactory;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;
    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * SingleBooking constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Order\Email\Sender\ShipmentSender $shipmentSender
     * @param Order\Shipment\TrackFactory $trackFactory
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
                                \Magento\Backend\App\Action\Context $context,
                                \Magento\Framework\View\Result\PageFactory $resultPageFactory,
                                Order\Email\Sender\ShipmentSender $shipmentSender,
                                Order\Shipment\TrackFactory $trackFactory,
                                \Magento\Sales\Model\Service\InvoiceService $invoiceService,
                                \Magento\Framework\DB\TransactionFactory $transactionFactory,
                                \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->shipmentSender = $shipmentSender;
        $this->trackFactory = $trackFactory;
        $this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $resultPageFactory, $shipmentSender, $trackFactory);
    }

    /**
     * Execute view action
     *
     * @return bool
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
                        $this->invoice($_order);
                        $this->_createShipment($_order, $shipResponse);
                        // new code added
                        $_order->setState("complete")->setStatus("complete");
                        $_order->save();
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
                    $this->invoice($_order);
                    $this->_createShipment($_order, $shipResponse);
                    // new code added
                    $_order->setState("complete")->setStatus("complete");
                    $_order->save();
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
                //$shipment->getOrder()->setState(Order::STATE_COMPLETE)->setStatus($shipment->getOrder()->getConfig()->getStateDefaultStatus(Order::STATE_COMPLETE));
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

    // new

    public function invoice($_order)
    {
        if (isset($_order)) {
//            if (!$_order->canInvoice()) {
//                return null;
//            }
//            if (!$_order->getState() == 'new') {
//                return null;
//            }
            try {
                /** @var \Magento\Sales\Model\Order\Invoice $invoice */
                $invoice = $this->invoiceService->prepareInvoice($_order);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                $invoice->register();
                /** @var \Magento\Framework\DB\Transaction $transaction */
                $transaction = $this->transactionFactory->create()
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
                $transaction->save();

                $invoiceEmail = $this->scopeConfig->getValue('sales_email/invoice/enabled',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            } catch (\Exception $exception) {
                $_order->addStatusHistoryComment('Exception Message: ' . $exception->getMessage(), false);
                $_order->save();
                return null;
            }
        }

    }

}
