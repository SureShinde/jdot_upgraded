<?php

namespace RLTSquare\CancelReasonOnEditOrder\Block\Adminhtml\Order;

use Magento\Framework\Data\Form\FormKey;

class View extends \Magento\Sales\Block\Adminhtml\Order\View
{
    protected $_context;

    protected $formKey;
    
    protected $order;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Sales\Helper\Reorder $reorderHelper,
        FormKey $formKey,
        \Magento\Sales\Api\Data\OrderInterface $order,
        array $data = []
    )
    {
        $this->_context = $context;
        $this->formKey = $formKey;
        $this->order = $order;
        parent::__construct($context, $registry, $salesConfig, $reorderHelper, $data);
    }

    public function getOrderCancelStatuses()
    {
        return $this->_scopeConfig->getValue("order_cancel/general/order_statuses");
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
    
    public function getOrderByIncrementId() 
    {
    	$orderId = $this->getRequest()->getParam('order_id');
    	$order = $this->order->loadByIncrementId($orderId);
    	return $order;
    }

}
