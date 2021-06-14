<?php

namespace RLTSquare\SMS\Block\Order;

use Magento\Framework\View\Element\Template;

/**
 * Class Confirm
 * @package RLTSquare\SMS\Block\Order
 */
class Confirm extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Confirm constructor.
     * @param Template\Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
    }

    public function getCmsBlockId(){
        return $this->coreRegistry->registry('cms_block_id');
    }

    /**
     * @return string
     */
    public function getShopUrl()
    {
        return $this->getBaseUrl();
    }
}
