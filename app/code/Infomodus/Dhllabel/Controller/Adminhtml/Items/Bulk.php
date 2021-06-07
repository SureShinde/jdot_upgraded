<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Dhllabel\Controller\Adminhtml\Items;

class Bulk extends \Infomodus\Dhllabel\Controller\Adminhtml\Items\AbstractMassAction
{
    protected $_conf;
    protected $collectionFactory;
    protected $shipmentCollectionFactory;
    protected $creditmemoCollectionFactory;
    protected $labelCollectionFactory;
    protected $fileFactory;
    protected $handy;
    protected $orderRepository;

    public function __construct(
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $creditmemoCollectionFactory,
        \Infomodus\Dhllabel\Model\ResourceModel\Items\CollectionFactory $labelCollectionFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Infomodus\Dhllabel\Helper\Config $conf,
        \Infomodus\Dhllabel\Helper\Handy $handy,
        \Magento\Sales\Model\OrderRepository $orderRepository
    )
    {
        $this->_conf = $conf;
        $this->collectionFactory = $collectionFactory;
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->creditmemoCollectionFactory = $creditmemoCollectionFactory;
        $this->labelCollectionFactory = $labelCollectionFactory;
        $this->fileFactory = $fileFactory;
        $this->handy = $handy;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $filter);
    }

    protected function massAction(\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection)
    {
        $ids = $collection->getAllIds();
        if (count($ids) > 0) {
            $isOrder = false;
            switch ($this->getRequest()->getParam('namespace')) {
                case 'sales_order_grid':
                    $type = 'shipment';
                    $isOrder = true;
                    $errorLink = 'sales/order';
                    break;
                case 'sales_order_shipment_grid':
                    $isOrder = true;
                    $type = 'shipment';
                    $errorLink = 'sales/shipment';
                    break;
                case 'sales_order_creditmemo_grid':
                    $type = 'refund';
                    $errorLink = 'sales/creditmemo';
                    break;
                default:
                    $type = 'shipment';
                    $errorLink = 'infomodus_dhllabel/items';
                    break;
            }
            $isOk = false;
            foreach ($ids as $id) {
                $handy = $this->handy;
                $isNotError = true;
                if ($isOrder === true) {
                    $order = $this->orderRepository->get($id);
                    $storeId = $order->getStoreId();
                    $isShippingActiveMethods = $this->_conf
                        ->getStoreConfig('dhllabel/bulk_create_labels/bulk_shipping_methods', $storeId);
                    if ($isShippingActiveMethods == 'specify') {
                        $shippingActiveMethods = trim($this->_conf
                            ->getStoreConfig('dhllabel/bulk_create_labels/apply_to', $storeId), " ,");
                        $shippingActiveMethods = strlen($shippingActiveMethods) > 0 ?
                            explode(",", $shippingActiveMethods) : [];
                    }
                    $isOrderStatuses = $this->_conf
                        ->getStoreConfig('dhllabel/bulk_create_labels/bulk_order_status', $storeId);
                    if ($isOrderStatuses == 'specify') {
                        $orderStatuses = explode(",", trim($this->_conf
                            ->getStoreConfig('dhllabel/bulk_create_labels/orderstatus', $storeId),
                            " ,"));
                    }
                    if (
                        (
                            $isShippingActiveMethods == 'all'
                            || (
                                isset($shippingActiveMethods)
                                && !empty($shippingActiveMethods)
                                && in_array($order->getShippingMethod(), $shippingActiveMethods)
                            )
                        )
                        &&
                        (
                            $isOrderStatuses == 'all'
                            ||
                            (
                                isset($orderStatuses)
                                && !empty($orderStatuses)
                                && in_array($order->getStatus(), $orderStatuses)
                            )
                        )
                    ) {
                        $handy->intermediate($id, $type);
                        $handy->defConfParams['package'] = $handy->defPackageParams;
                        $isNotError = $handy->getLabel(null, $type, null, $handy->defConfParams);
                    }
                } else {
                    $handy->intermediate(null, $type, $id);
                    $handy->defConfParams['package'] = $handy->defPackageParams;
                    $isNotError = $handy->getLabel(null, $type, $id, $handy->defConfParams);
                }

                if ($isNotError == true) {
                    $isOk = true;
                } else {
                    $this->messageManager->addErrorMessage(__('For the selected items are not created labels.'));
                }

            }

            if ($isOk === true) {
                $this->messageManager->addSuccessMessage(__('For the selected items are created labels.'));
            }

            return $this->resultRedirectFactory->create()->setPath($errorLink . '/');
        }
    }
}
