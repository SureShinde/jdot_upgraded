<?php
namespace Pentalogy\Callcourier\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Pentalogy\Callcourier\Model\Api;
/*use Pentalogy\Callcourier\Helper\Data;*/

class ShipperAreaCollect extends Action
{
    protected $resultJsonFactory;

    public function __construct(Context $context, JsonFactory $resultJsonFactory)
    {
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }
    public function execute(){
        $post = $this->getRequest()->getPostValue();
        $cityId =(int) $post['city_id'];
        $response = (new Api\Integration())->getShippingArea($cityId);
        $result = $this->resultJsonFactory->create();
        return $result->setData(['success' => true, 'data' => $response]);
    }

}