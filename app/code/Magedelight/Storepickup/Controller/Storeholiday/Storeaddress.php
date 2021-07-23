<?php

/**
 * Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category Magedelight
 * @package Magedelight_Storepickup
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Storepickup\Controller\Storeholiday;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Action\Context;
use Magedelight\Storepickup\Model\Source\Country;
use Magedelight\Storepickup\Model\Source\Region;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magedelight\Storepickup\Model\StorelocatorFactory;
use Magento\Store\Model\ScopeInterface;

class Storeaddress extends Action
{

    protected $pageFactory;

    
    /* Shipping Off Days */
//    const XML_PATH_STORE_OFFDAY = 'carriers/storepickup/offdays';

    /**
     *
     * @param StoreholidayFactory $storeholidayFactory
     * @param PageFactory $pageFactory
     * @param Context $context
     */
    public function __construct(StorelocatorFactory $modelStorelocatorFactory, ScopeConfigInterface $scopeConfig, PageFactory $pageFactory, Country $countryOptions, Region $regionOptions, Context $context)
    {
        $this->_modelstorelocatorFactory = $modelStorelocatorFactory;
        $this->scopeConfig = $scopeConfig;
        $this->countryOptions = $countryOptions;
        $this->regionOptions = $regionOptions;
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    /**
     *
     * @return Array
     */
    public function execute()
    {
        $storeId = $this->getRequest()->getParam('StoreID');
        $fields = ['storename', 'address', 'city', 'state','region_id', 'country', 'zipcode', 'telephone'];
        $storelocatorModel = $this->_modelstorelocatorFactory->create();
        $storelocatorCollection = $storelocatorModel->getCollection();
        $storelocatorCollection->addFieldToFilter('storelocator_id', $storeId);
        $storelocatorCollection->addFieldToSelect($fields);
        $storelocatorData = $storelocatorCollection->getData();
        
        if ($storelocatorData) {
            $countryname = $this->getCountryName($storelocatorData[0]['country']);
            $storelocatorData[0]['countryname'] = $countryname;
            
            $storelocatorData[0]['statename'] ='';
            if (is_null($storelocatorData[0]["state"])) {
                if (isset($storelocatorData[0]["region_id"]) && $storelocatorData[0]["region_id"] != 0) {
                        $storelocatorData[0]['statename'] = $this->getRegionName($storelocatorData[0]["region_id"]);
                        $storelocatorData[0]['region_code'] = $this->getRegionCode($storelocatorData[0]["region_id"]);
                }
            } else {
                $storelocatorData[0]['statename'] = $storelocatorData[0]['state'];
            }
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($storelocatorData);
        return $resultJson;
    }

    /**
     *
     * @param string $countryval
     * @return string
     */
    public function getCountryName($countryval)
    {
        $countryArray = $this->countryOptions->getOptions();
        return $countryArray[$countryval];
    }

    /**
     *
     * @param string $regionvalue
     * @return string
     */
    public function getRegionName($region_id)
    {
        $regionArray = $this->regionOptions->getOptions();
        return $regionArray[$region_id];
    }

    /**
     *
     * @param string $regioncode
     * @return string
     */
    public function getRegionCode($region_id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $region = $objectManager->get('Magento\Directory\Model\Region')->load($region_id);
        return $region->getCode();
    }
}
