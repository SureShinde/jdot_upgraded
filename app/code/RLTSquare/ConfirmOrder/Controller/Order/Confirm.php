<?php

namespace  RLTSquare\ConfirmOrder\Controller\Order;

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
        \RLTSquare\ConfirmOrder\Model\PostFactory $postFactory
    ){
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
        $order_increment_id = '';

        $success = true;
        $page = $this->_pageFactory->create();
        $block = $page->getLayout()->getBlock('rltsquare_order_confirm');
        if($orderId == ''){
            $success = false;
        }
        else {
            $order = $this->order->load($orderId);
            $order_increment_id = $order->getIncrementId();
            $post = $this->_postFactory->create();
            $collection = $post->getCollection();
            foreach($collection as $item){
                if($item->getOrderId() == $order->getIncrementId()){
                    $success = false;
                    break;
                }
            }

            if($success) {
                $post->setOrderId($order->getIncrementId())->save();
            }
        }
        $block->setData('order_data', ['order_id' => $order_increment_id, 'success' => $success]);

        return $page;
    }
}