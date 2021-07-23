<?php
namespace Mean3\Stallionshipping\Controller\Adminhtml\Order;

class Book extends \Magento\Backend\App\Action
{
    protected $order;
    protected $request;
    protected $helper;
  
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\Order $order,
        \Mean3\Stallionshipping\Helper\Data $helper
    ) {
        $this->helper = $helper;
        $this->request = $context->getRequest();
        $this->order = $order;
        parent::__construct($context);
    }

    public function getOrder()
    {
        $orderId = $this->request->getParam("order_id");
        $order = $this->order->load($orderId, 'entity_id');
        return $order;
    }
    
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id = $this->request->getParam("order_id")) {
            $order = $this->getOrder();
            $orderIdBook = $order->getId();
            try {
                $allowedOrderStatus = $this->helper->getAllowedOrderStatus();
                if (in_array($order->getStatus(), $allowedOrderStatus)) {
                    if ($order) {

                        $parcelNo = $this->helper->orderApi($orderIdBook);

                        if (is_numeric($parcelNo)) {
                            $this->messageManager->addSuccess(__('Parcel No#'. $parcelNo .' has been Booked successfully on Stallion.'));
                        }
                        else{
                            $this->messageManager->addError(__('Order No#'.$order->getRealOrderId() .' not Book  on Stallion. Due to '.$parcelNo));
                        }
                    } 
                }
                else {
                    $this->messageManager->addError(__('Only selected order status can be Booked.', ['_current'=>true]));
                }

            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            $this->messageManager->addError(__('Order does not exist.'));
        }
        return $resultRedirect->setPath('sales/order/view/order_id/'.$orderIdBook);
    }
}
