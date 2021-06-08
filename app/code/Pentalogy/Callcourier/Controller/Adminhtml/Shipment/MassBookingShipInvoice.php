<?php

namespace Pentalogy\Callcourier\Controller\Adminhtml\Shipment;

use Braintree\Exception;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Pentalogy\Callcourier\Model\Api\Integration;


class MassBookingShipInvoice extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $invoiceSender;
    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
    * @var \Magento\Sales\Model\Order\Email\Sender\ShipmentSender
    */
    protected $shipmentSender;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     */
    protected $trackFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory
    )
    {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->shipmentSender = $shipmentSender;
        $this->invoiceSender = $invoiceSender;
        $this->invoiceService = $invoiceService;
        $this->invoiceSender = $invoiceSender;
        $this->invoiceService = $invoiceService;
        $this->scopeConfig = $scopeConfig;
        $this->transactionFactory = $transactionFactory;
        $this->trackFactory = $trackFactory;
    }


    /**
     * Booking selected orders
     * @param AbstractCollection $collection
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $booking = new Integration();
        $systemValidator = $booking->systemConfigurationValidator();
        $shipperRemarks = $booking->getRemarks();
        $remarksValidator = $booking->bulkShipmentValidator();
        $errorMsg = null;
        if ($systemValidator != null || $remarksValidator != null) {
            $errorMsg = ($systemValidator == null) ? $remarksValidator : $remarksValidator;
        } else {
            $shipperInfo = $booking->getSystemConfigurationData();
            foreach ($collection->getItems() as $_order) {

                $curlData = array();
                $consigneeInfo = $this->_getOrderData($_order);
                $validCityId = $booking->validateConsigneeCity($_order->getShippingAddress()->getCity());
                if ($validCityId) {
                    $curlData = array_merge($shipperInfo, $consigneeInfo);
                    $curlData['DestCityId'] = $validCityId;
                    $curlData['Remarks'] = $shipperRemarks;
                    $curlData["ServiceTypeId"] = $booking->getConfigServiceType();
                    $shipResponse = $booking->createShipment($curlData);
                    if ($shipResponse['status'] == true) {
                        $this->_createShipment($_order, $shipResponse);
                    }
                    else {
                        $errorMsg .= 'Order Id: ' . $_order->getIncrementId() . " <b>" . $shipResponse['message'] . "</b><br>";
                    }
                } else {
                    $errorMsg .= 'Order Id: ' . $_order->getIncrementId() . " <b>Error: Invalid City.</b><br>";
                }
                $this->invoice($_order);
            }
        }
        if ($errorMsg != null) {
            $this->messageManager->addError(__($errorMsg));
        } else {
            $this->messageManager->addSuccess(__('Selected Order(s) booked successfully.'));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order');
        return $resultRedirect;
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
                $shipment->save();
                $shipment->getOrder()->save();
                /*$this->_objectManager->create('Magento\Shipping\Model\ShipmentNotifier')
                    ->notify($shipment);*/
                $shipment->save();
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

    private function _getOrderData($_order)
    {
        try {
            $description = array();
            $data = array();
            $data['ConsigneeRefNo'] = $_order->getIncrementId();
            $shippingAddress = $_order->getShippingAddress();
            $data['ConsigneeName'] = ucwords($shippingAddress->getName());
            //$data['city'] = $shippingAddress->getCity();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $country = $objectManager->create('\Magento\Directory\Model\Country')
                ->load($shippingAddress->getCountryId())->getName();

            $data['Address'] = implode('', $shippingAddress->getStreet()) . " " . $shippingAddress->getRegion() . " " . $country;

            $data['ConsigneeCellNo'] = $shippingAddress->getTelephone();
            foreach ($_order->getAllVisibleItems() as $_item) {
                $description[] = $_item->getName() . " " . $_item->getSku() . " (" . (int)$_item->getQtyOrdered() . ")";
            }
            $data['Description'] = implode(',', $description);
            $data['CodAmount'] = (int)$_order->getBaseGrandTotal();
            $data['Pcs'] = (int)$_order->getTotalQtyOrdered();
            $data['Weight'] = floatval($_order->getWeight());
        } catch (Exception $e) {

        }
        return $data;
    }

    public function invoice($_order)
    {
        if (isset($_order)) {
            if (!$_order->canInvoice()) {
                return null;
            }
            if (!$_order->getState() == 'new') {
                return null;
            }
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
                $_order->addStatusHistoryComment('Exception message: ' . $exception->getMessage(), false);
                $_order->save();
                return null;
            }
        }

    }
}