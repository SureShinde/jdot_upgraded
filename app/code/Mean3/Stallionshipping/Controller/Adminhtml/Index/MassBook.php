<?php
namespace Mean3\Stallionshipping\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class MassBook extends Action
{
    protected $_orderCollectionFactory;
    protected $request;
    protected $filter;
    protected $helper;
    
   
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $orderCollectionFactory,
        \Mean3\Stallionshipping\Helper\Data $helper
    ) {
        $this->helper = $helper;
        $this->filter = $filter;
        $this->request = $context->getRequest();
        $this->_orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context);
    }

    public function getOrderCollection()
    {
        $collection = $this->_orderCollectionFactory->create();
        return $collection;
    }
 
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $collection = $this->filter->getCollection($this->getOrderCollection());
        
        if ($this->filter->getCollection($this->getOrderCollection())) {
            try {
                $collection = $this->filter->getCollection($this->getOrderCollection());
                $countBookOrder = $this->helper->book($collection);
                
                if(!is_array($countBookOrder)){
                    if(strpos($countBookOrder, 'Wrong User Name or Password') !== false){

                        $this->messageManager->addError(__(' %1 ',$countBookOrder));
                        return $resultRedirect->setPath('sales/order/index');
                    }
                    if(strpos($countBookOrder, 'Connection Error') !== false){

                        $this->messageManager->addError(__(' %1 ',$countBookOrder));
                        return $resultRedirect->setPath('sales/order/index');
                    }
                }
                if(is_array($countBookOrder)){
                    $countNonBookOrder = $collection->count() - $countBookOrder['countOrder'];

                    if ($countNonBookOrder > 0) {
                        
                        $this->messageManager->addError(__('Total of %1 order(s) could not be Booked. Only selected order status can be Booked. ',$countNonBookOrder));
                        $this->messageManager->addError(__('Following orders are not booked on Stallion:- %1  ', $countBookOrder['unBookedOrdersErrorsForDisplay']));
                    }
                    if ($countBookOrder) {
                        $this->messageManager->addSuccess(__('Total of %1 record were successfully Booked.', $countBookOrder['countOrder']));
                    }
                    return $resultRedirect->setPath('sales/order/index');
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('sales/order/index');
            }
        } else {
            return $resultRedirect->setPath('sales/order/index');
        }
    }
}
