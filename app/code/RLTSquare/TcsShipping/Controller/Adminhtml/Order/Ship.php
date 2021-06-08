<?php

namespace RLTSquare\TcsShipping\Controller\Adminhtml\Order;


class Ship extends \Magento\Framework\App\Action\Action
{

    public $resultJsonFactory;
    public $tcsHelper;
    protected $orderRepository;
    protected $coreRegistry;
    protected $tcsOrderManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Registry $coreRegistry,
        \RLTSquare\TcsShipping\Helper\Data $tcsHelper,
        \RLTSquare\TcsShipping\Model\OrderManager $orderManager
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->orderRepository = $orderRepository;
        $this->coreRegistry = $coreRegistry;
        $this->tcsHelper = $tcsHelper;
        $this->tcsOrderManager = $orderManager;
    }

    public function execute()
    {
        $redirectURL = $this->_url->getUrl('sales/order/view',
            ['order_id' => $this->getRequest()->getParam('order_id')]);
        if ($this->tcsHelper->isEnabled()) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->_initOrder();
            if ($order ) {
                if ($order->getPayment()->getMethod() == 'etisalatpay') {
                    if ($order->getStatus() == 'capture') {
                        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
                        $shipment = $order->getShipmentsCollection()->getFirstItem();
                        if (!$shipment->getIncrementId()) {
                            // Process Shipments
                            $result = $this->tcsOrderManager->invoiceAndShip($order);

                            // Add results to session
                            $this->addResultsToSession($result);
                        } else {
                            $this->messageManager->addErrorMessage(
                                __('Shipment has already been created for the selected order')
                            );
                        }
                    } else {
                        $this->messageManager->addErrorMessage('Capture Status required for Shipment');
                    }
                } else {
                    /** @var \Magento\Sales\Model\Order\Shipment $shipment */
                    $shipment = $order->getShipmentsCollection()->getFirstItem();
                    if (!$shipment->getIncrementId()) {
                        // Process Shipments
                        $result = $this->tcsOrderManager->invoiceAndShip($order);

                        // Add results to session
                        $this->addResultsToSession($result);
                    } else {
                        $this->messageManager->addErrorMessage(
                            __('Shipment has already been created for the selected order')
                        );
                    }

                }
            }

        } else {
            $this->messageManager->addErrorMessage(__('TCS Shippement Not Enabled'));
        }

        return $this->resultJsonFactory->create()->setData([
            'returnUrl' => $redirectURL
        ]);
    }

    public function _initOrder()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        $this->coreRegistry->register('sales_order', $order);
        $this->coreRegistry->register('current_order', $order);
        return $order;

    }

    public function addResultsToSession($result)
    {
        if (!empty($result['error'])) {
            $this->messageManager->addErrorMessage($result['error']);
        }
        if (!empty($result['success'])) {
            $this->messageManager->addSuccessMessage($result['success']);
        }
    }
}
