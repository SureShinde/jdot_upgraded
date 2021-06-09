<?php

namespace RLTSquare\BulkCreditMemo\Controller\Adminhtml\Index;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * Class BulkDownloadConsignment
 * @package RLTSquare\BulkDownloadConsignment\Controller\Adminhtml\Download
 */
class BulkCreditMemo extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{

    /**
     * BulkShipInvoice constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Model\Order\Invoice $invoice,
        CollectionFactory $collectionFactory,
        \Magento\Sales\Model\Service\CreditmemoService $creditmemoService,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->messageManager = $messageManager;
        $this->order = $order;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoService = $creditmemoService;
        $this->invoice = $invoice;
    }

    /**
     * @param AbstractCollection $collection
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    protected function massAction(AbstractCollection $collection)
    {
        $noCreditMemo = [];
        $creditMemos = [];
        foreach ($collection->getItems() as $order) {
            $invoices = $order->getInvoiceCollection();
            $invoiceincrementid = null;
            foreach ($invoices as $invoice) {
                $invoiceincrementid = $invoice->getIncrementId();
            }
            if($invoiceincrementid != null){
                $creditMemos[] = $order->getIncrementId();
                $invoiceobj = $this->invoice->loadByIncrementId($invoiceincrementid);
                $creditmemo = $this->creditmemoFactory->createByOrder($order);

                /**
                 * Process back to stock flags
                 */
                foreach ($creditmemo->getAllItems() as $creditmemoItem) {
                    $orderItem = $creditmemoItem->getOrderItem();
                    $creditmemoItem->setBackToStock(true);
                }

                $this->creditmemoService->refund($creditmemo,true);

                $order->setState(Order::STATE_CLOSED)->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CLOSED));
                $this->creditmemoService->notify($creditmemo->getId());
            }
            else{
                $noCreditMemo[] = $order->getIncrementId();
            }
        }

        $message = '';
        if (count ( $noCreditMemo ) > 0) {
            $message = __( "Credit Memo could not be generated for the following orders(s) : " );
            for($x = 0; $x < count ( $noCreditMemo ); $x ++) {
                $message .= $noCreditMemo[$x];
                if (! $x == (count ( $noCreditMemo ) - 1))
                    $message .= ', ';
            }
        }

        if(!empty($message)) {
            $this->messageManager->addErrorMessage($message);
        }

        if (count($creditMemos) > 0) {
            $this->messageManager->addSuccess("Credit Memos created successfully");
        }

        $this->_redirect ( 'sales/order/' );
    }
}
