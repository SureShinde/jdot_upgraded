<?php
 
namespace AaLogics\Lcs\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Aalogics\Lcs\Model\System\OrderStatusOptions;
 
class Index extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
    protected $_orderStatus;
    protected $_client;
    protected $_lcsHelper;

    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        OrderStatusOptions $orderStatus,
        \Aalogics\Lcs\Model\Carrier\Lcs $client,
        \Aalogics\Lcs\Helper\Data $lcsHelper
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_orderStatus = $orderStatus;
        $this->_client = $client;
        $this->_lcsHelper = $lcsHelper;
        parent::__construct($context);
    }
 
    public function execute()
    {
        // $resultPage = $this->_resultPageFactory->create();
        // return $resultPage;

        // echo "index data";
        // $cities = $this->_lcsHelper->getLcsCityData('karachi');
        // var_dump($cities);

        // $data = $this->_client->collectRates();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $block = $objectManager->create('\Aalogics\Lcs\Model\Config\Source\CityOptions');

        $data = $block->toOptionArray();

        // $data = $this->_orderStatus->toOptionArray();
        // $data = $this->_costCenter->getAddButtonHtml();
        echo "<pre>";
        print_r($data);
        die;

    }
}