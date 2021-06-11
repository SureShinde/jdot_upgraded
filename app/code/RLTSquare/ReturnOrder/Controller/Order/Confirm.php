<?php

namespace RLTSquare\ReturnOrder\Controller\Order;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Confirm extends \Magento\Framework\App\Action\Action
{
    protected $order;
    protected $_pageFactory;
    protected $_postFactory;
    protected $encryptor;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \RLTSquare\ReturnOrder\Model\PostFactory $postFactory
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->_postFactory = $postFactory;
        $this->order = $order;
        $this->encryptor = $encryptor;
        parent::__construct($context);
    }

    public function execute()
    {
        $response = $this->getRequest()->getParams();
        $orderId = $this->encryptor->decrypt($response['order_id']);
        $delivered_status = array_key_exists('delivered_status', $response) ? $response['delivered_status'] : "";
        $want_redelivered = array_key_exists('want_redelivered', $response) ? $response['want_redelivered'] : "";
        $page = $this->_pageFactory->create();
        $block = $page->getLayout()->getBlock('rltsquare_order_return_confirm');
        $order_increment_id = '';

        if($orderId != '') {
            $order = $this->order->load($orderId);
            $order_increment_id = $order->getIncrementId();

            $post = $this->_postFactory->create();
            $collection = $post->getCollection()->addFieldToFilter('order_id', $order->getIncrementId());

            if (count($collection) > 0) {
                $post = $post->load($collection->getFirstItem()->getId());

                $delivered_status = $delivered_status == "" ? $post->getDeliveredStatus() : $delivered_status;
                $want_redelivered = $want_redelivered == "" ? $post->getWantRedelivered() : $want_redelivered;

                $post->setDeliveredStatus($delivered_status)
                    ->setWantRedelivered($want_redelivered)
                    ->save();
            } else {
                $post->setOrderId($order->getIncrementId())
                    ->setDeliveredStatus($delivered_status)
                    ->setWantRedelivered($want_redelivered)
                    ->save();
            }
        }
        $block->setData('order_data', ['order_id' => $order_increment_id]);

        return $page;
    }
}