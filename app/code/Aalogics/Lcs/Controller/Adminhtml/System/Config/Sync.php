<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Aalogics\Lcs\Controller\Adminhtml\System\Config;

use \Aalogics\Lcs\Model\Api\Lcs\Api\Request;

class Sync extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Aalogics\Lcs\Model\Api\Lcs\Api\Request
     */
    protected $_lcsRequest;
    
    protected $_lcsHelper;

    protected  $_resource;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
    	\Aalogics\Lcs\Model\Api\Lcs\Api\Request $lcsRequest,
        \Aalogics\Lcs\Helper\Data $lcsHelper,
        \Magento\Framework\Setup\ModuleDataSetupInterface $resource
    ) {
        parent::__construct($context);
        $this->_resource = $resource;
        $this->_lcsRequest = $lcsRequest;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_lcsHelper = $lcsHelper;
    }

    /**
     * Retrieve synchronize process state and it's parameters in json format
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $result = [];

        $setup = $this->_resource;
        $setup->startSetup();

        //truncate table 
        $connection = $setup->getConnection('default');
        $connection->truncateTable($setup->getTable('pakistan_cities_lcs'));
        
        // fetch latest cities from LCS API
        $cities = $this->_lcsRequest->getCities();

        //insert into table
        if(!empty($cities) && isset($cities['status'])){

            if($cities['status'] && isset($cities['city_list'])){
                foreach ($cities['city_list'] as $city) {
                	$pCity = [];
                    if(isset($city['name'])){
                        $pCity['city_id'] = $city['id'];
                        $pCity['default_name'] = $city['name'];
                        $setup->getConnection()
                        ->insertArray($setup->getTable('pakistan_cities_lcs'), ['city_id','default_name'], array($pCity));
                    }
                }

                $result['message'] = 'Successfully Fetch.';
            }else{
                $result['message'] = 'Error: Something went Wrong.';
            }

        }
        
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
