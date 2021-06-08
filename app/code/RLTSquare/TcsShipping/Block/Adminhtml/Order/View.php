<?php

namespace RLTSquare\TcsShipping\Block\Adminhtml\Order;

class View extends \Magento\Sales\Block\Adminhtml\Order\View
{

    protected $_context;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Sales\Helper\Reorder $reorderHelper,
        array $data = []
    ) {
        $this->_context = $context;
        parent::__construct($context, $registry, $salesConfig, $reorderHelper, $data);
    }
}