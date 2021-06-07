<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Dhllabel\Controller\Adminhtml\Pdflabels;

class Many extends \Infomodus\Dhllabel\Controller\Adminhtml\Pdflabels\AbstractMassAction
{
    protected $_conf;
    protected $_pdf;

    protected $fileFactory;

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
        \Infomodus\Dhllabel\Helper\Pdf $pdf
    ) {
        $this->_conf = $conf;
        $this->_pdf = $pdf;
        $this->collectionFactory = $collectionFactory;
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->creditmemoCollectionFactory = $creditmemoCollectionFactory;
        $this->labelCollectionFactory = $labelCollectionFactory;
        $this->fileFactory = $fileFactory;
        parent::__construct($context, $filter);
    }

    protected function massAction(\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection)
    {
        $ids = $collection->getAllIds();
        if (count($ids) > 0) {
            $labels = $this->labelCollectionFactory->create()->addFieldToFilter('lstatus', 0);
            if ($this->_conf->getStoreConfig('dhllabel/printing/bulk_printing_all') == 1) {
                $labels->addFieldToFilter('rva_printed', 0);
            }

            $paramName = $this->getRequest()->getParam('namespace', null);
            if($paramName === null){
                $paramName = $this->getRequest()->getParam('massaction_prepare_key', null);
            }

            switch ($paramName) {
                case 'sales_order_grid':
                    $labels->addFieldToFilter('order_id', ['in' => $ids]);
                    $errorLink = 'sales/order';
                    break;
                case 'sales_order_shipment_grid':
                    $labels->addFieldToFilter('shipment_id', ['in' => $ids])->addFieldToFilter('type', 'shipment');
                    $errorLink = 'sales/shipment';
                    break;
                case 'sales_order_creditmemo_grid':
                    $labels->addFieldToFilter('shipment_id', ['in' => $ids])->addFieldToFilter('type', 'refund');
                    $errorLink = 'sales/creditmemo';
                    break;
                default:
                    $labels->addFieldToFilter('dhllabel_id', ['in' => $ids]);
                    $errorLink = 'infomodus_dhllabel/items';
                    break;
            }
            if ($labels->count() > 0) {
                $data = $this->_pdf->createManyPDF($labels);
                if ($data !== false) {
                    return $data;
                }
            } else {
                $this->messageManager->addErrorMessage(__('For the selected items are not created labels.'));
                return $this->resultRedirectFactory->create()->setPath($errorLink . '/');
            }
        }
    }
}
