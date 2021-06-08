<?php

namespace RLTSquare\TcsShipping\Observer\Sales\Tcs;


use Magento\Framework\Event\Observer;

/**
 * Class Ship
 * @package RLTSquare\TcsShipping\Observer\Sales\Tcs
 */
class Ship implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \RLTSquare\TcsShipping\Helper\TcsRegisterar
     */
    protected $tcsRegistrar;
    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     */
    protected $trackFactory;
    /**
     * @var \Magento\Sales\Model\Convert\Order
     */
    protected $convertOrder;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
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
     * Ship constructor.
     * @param \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory
     * @param \Magento\Sales\Model\Convert\Order $covertOrder
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender
     * @param \RLTSquare\TcsShipping\Helper\TcsRegisterar $tcsRegistrar
     */
    public function __construct(
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Magento\Sales\Model\Convert\Order $covertOrder,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender,
        \RLTSquare\TcsShipping\Helper\TcsRegisterar $tcsRegistrar
    ) {
        $this->convertOrder = $covertOrder;
        $this->tcsRegistrar = $tcsRegistrar;
        $this->trackFactory = $trackFactory;
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
        $this->shipmentSender = $shipmentSender;
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * @param Observer $observer
     * @return \Magento\Sales\Model\Order\Shipment|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getData('order');
        if (isset($order)) {
            /** @var \Magento\Sales\Model\Order\Shipment $shipment */
            $shipment = $this->convertOrder->toShipment($order);
            $consignmentNo = $this->tcsRegistrar->shipOrder($order);
            if (isset($consignmentNo)) {
                try {
                    foreach ($order->getAllItems() as $orderItem) {
                        if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                            continue;
                        }
                        $qtyShipped = $orderItem->getQtyToShip();
                        $shipmentItem = $this->convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
                        $shipment->addItem($shipmentItem);
                    }

                    /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
                    $track = $this->trackFactory->create();
                    $track->setCarrierCode('tcsshipping');
                    $track->setTitle('TCS Shipping');
                    $track->setTrackNumber($consignmentNo);
                    $shipment->addTrack($track);
                    $shipment->register();
                    $shipment->getOrder()->setIsInProcess(true);
                    $shipment->save();
                    $shipment->getOrder()->save();
//                    $this->transactionFactory->create()->addObject($shipment)
//                        ->addObject($shipment->getOrder())
//                        ->save();

                    $sendEmail = $this->scopeConfig->getValue('sales_email/shipment/enabled',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                    if ($sendEmail) {
                        $this->shipmentSender->send($shipment);
                    }
                    return $shipment;
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__('We can\'t generate shipment. ' . $e->getMessage()));
                }

            } else {
                $this->messageManager->addErrorMessage(__("Error getting tracking info from TCS API"));
            }
        }
        throw new \Magento\Framework\Exception\LocalizedException(__('The order no longer exists.'));
    }
}
