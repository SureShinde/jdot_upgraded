<?php

namespace RLTSquare\SMS\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 * @package RLTSquare\SMS\Controller\Index
 */
class Confirm extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $pageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Confirm constructor.
     * @param Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->pageFactory = $pageFactory;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Layout|\Magento\Framework\View\Result\Page
     * @throws \Exception
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('orderId');

        if (isset($orderId)) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderRepository->get((int) $orderId);
            $orderStatus = $order->getStatus();

            // checking if order already confirmed
            if($orderStatus == "pending"){
                $order->setStatus('verified');
                $order->save();
                $this->coreRegistry->register('cms_block_id', "customer_order_confirmation");
            }
            else{
                $this->coreRegistry->register('cms_block_id', "customer_order_already_confirmed");
            }
        }

        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->set('Order Confirmation');
        return $resultPage;
    }
}
