<?php

namespace RLTSquare\ConsigneeRating\Controller\Adminhtml\Consignee;

use Magento\Setup\Exception;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Bootstrap;

//require __DIR__ . '/app/bootstrap.php';

class Rating extends \Magento\Framework\App\Action\Action
{
    private $resultJsonFactory;
    protected $_curl;
    protected $xy;
    /**
     * Constructor.
     *
     * @param Magento\Framework\HTTP\Client\Curl $curl
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\HTTP\Client\Curl $curl,
        ResultFactory $resultFactory,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->_curl = $curl;
        $this->xy = $resultFactory;
        $this->resultJsonFactory = $resultJsonFactory;
    }


    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $phone = $this  ->getRequest()->getParam('phone');
        $url= 'http://cod.callcourier.com.pk/api/CallCourier/GetConsigneeRating?ContactNo='.$phone;

        $this->_curl->setOption(CURLOPT_HEADER, 0);
        $this->_curl->setOption(CURLOPT_TIMEOUT, 60);
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOption(CURLOPT_CUSTOMREQUEST, 'GET');
        //set curl header
        $this->_curl->addHeader("Content-Type", "application/json");
        //get request with url
        $this->_curl->get($url);
        //read response
        $response = $this->_curl->getBody();
        return $resultJson->setData(['response' => $response]);

    }
}
