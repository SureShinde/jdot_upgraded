<?php
/**
 * @author  Dawood Gondal <dawood.gondal@rltsquare>
 */

namespace RLTSquare\CancelOrder\Block\Adminhtml\Order;

use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\OrderRepository;
use Magento\Backend\Model\Session\Quote;

/**
 * Class CancelStatuses
 * @package RLTSquare\CancelOrder\Block\Adminhtml\Order
 */
class CancelStatuses extends Template
{
    /**
     * @var OrderRepository
     */
    protected $orderRepo;

    /**
     * @var Quote
     */
    protected $quoteSession;

    /**
     * CancelStatuses constructor.
     * @param Template\Context $context
     * @param OrderRepository $orderRepository
     * @param Quote $quote
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        OrderRepository $orderRepository,
        Quote $quote,
        array $data = []
    ) {
        $this->orderRepo = $orderRepository;
        $this->quoteSession = $quote;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getOrderCancelStatusesConfig()
    {
        return $this->_scopeConfig->getValue("order_cancel/general/order_statuses");
    }

    /**
     * @return bool
     */
    public function checkOrderPaymentMethod()
    {
        $action = $this->_request->getFullActionName();
        if ($action == "sales_order_edit_index") {
            $orderId = (int)$this->quoteSession->getOrder()->getId();
            $order = $this->orderRepo->get($orderId);
            if ($order->getPayment()->getMethod() == "etisalatpay") {
                return true;
            }
        }
        return false;
    }

    /**
     * @return mixed
     */
    /*public function getOrderCancelInventoryRestockConfig()
    {
        return $this->_scopeConfig->getValue("order_cancel/general/order_inventory_status");
    }*/

    public function getInventoryStatuses()
    {
        return [
            ['value' => 1, 'label' => 'Yes i want to restock inventory'],
            ['value' => 0, 'label' => 'No i dont want to restock inventory']
        ];
    }

    public function getOrderedProducts() {
        //$orderId = (int)$this->quoteSession->getOrder()->getId();
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderRepo->get($orderId);
        //$items = [];
        //$i = 0;
        foreach($order->getAllItems() as $item) { // we can also use getItems() and getAllItems() according to our need
            //$items[] = $item;
            if ($item->getProductType() == "simple") {
                $products_array[] = array(
                    'value' => $item->getProductId(),
                    'name' => $item->getName()
                );
            }
        //$i++;
        }
            //$products_array['pid'] = $item->getProductId();
            //$products_array['ptype'] = $item->getProductType();
           // $products_array['sku'] = $item->getSku();
            //$products_array['name'] = $item->getName();
           // $products_array['item_id'] = $item->getItemId();
           // $products_array['qty_ordered'] = $item->getQtyOrdered();
           // $products_array['price'] = $item->getPrice();
           // $products_array['original_price'] = $item->getOriginalPrice();
            //return $products_array;
        //return $order->getAllItems();
        //var_dump($items);
        return $products_array;
    }
}