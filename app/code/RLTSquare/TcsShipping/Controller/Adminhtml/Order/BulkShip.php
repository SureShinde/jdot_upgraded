<?php

namespace RLTSquare\TcsShipping\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * Class BulkShip
 * @package RLTSquare\TcsShipping\Controller\Adminhtml\Order
 */
class BulkShip extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var \RLTSquare\TcsShipping\Helper\Data
     */
    protected $tcsHelper;
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
     * BulkShip constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \RLTSquare\TcsShipping\Helper\Data $tcsHelper
     * @param \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory
     * @param \Magento\Sales\Model\Convert\Order $covertOrder
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender
     * @param \RLTSquare\TcsShipping\Helper\TcsRegisterar $tcsRegistrar
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Magento\Framework\App\Request\Http $request,
        \RLTSquare\TcsShipping\Helper\Data $tcsHelper,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Magento\Sales\Model\Convert\Order $covertOrder,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender,
        \RLTSquare\TcsShipping\Helper\TcsRegisterar $tcsRegistrar
    )
    {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->request = $request;
        $this->tcsHelper = $tcsHelper;
        $this->convertOrder = $covertOrder;
        $this->tcsRegistrar = $tcsRegistrar;
        $this->trackFactory = $trackFactory;
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
        $this->shipmentSender = $shipmentSender;
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * @param AbstractCollection $collection
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    protected function massAction(AbstractCollection $collection)
    {
        $ordersArray = array();
        $shipOrders = array ();
        $alreadyShipped = array ();
        $orderAlreadyShipped = 0;

        if($this->tcsHelper->isEnabled()){
            foreach ($collection->getItems() as $order) {
                if (!$order->canCancel()) {
                    continue;
                }
                $shipment = $order->getShipmentsCollection()->getFirstItem();
                $shipmentIncrementId = $shipment->getIncrementId();

                if ($shipmentIncrementId == null) {
                    $shipOrders [] = $order;
                    $ordersArray[] = $order->getEntityId();
                } else {
                    $alreadyShipped[] = $order;
                }
            }

            $message = '';
            if (count ( $alreadyShipped ) > 0) {
                $message = __( "Shipment has already been created for the selected order(s) : " );
                for($x = 0; $x < count ( $alreadyShipped ); $x ++) {
                    $message .= $alreadyShipped[$x]->getIncrementId();
                    if (! $x == (count ( $alreadyShipped ) - 1))
                        $message .= ', ';
                }

            }

            if(!empty($message)) {
                $this->messageManager->addErrorMessage($message);
            }

            $this->shipment($shipOrders);
            $this->_redirect ( 'sales/order/' );

        } else {
            $this->messageManager->addErrorMessage('TCS Shippement Not Enabled');
        }
    }

    /**
     * @param $shipOrders
     */
    public function shipment($shipOrders)
    {
        foreach ($shipOrders as $order){
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

                        $sendEmail = $this->scopeConfig->getValue('sales_email/shipment/enabled',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                        if ($sendEmail) {
                            $this->shipmentSender->send($shipment);
                        }
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage(__('We can\'t generate shipment. ' . $e->getMessage()));
                    }

                } else {
                    $this->messageManager->addErrorMessage(__("Error getting tracking info from TCS API"));
                }
            }
        }
    }
}
