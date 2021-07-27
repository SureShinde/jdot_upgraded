<?php

/**
 * Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>.
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
 * @category MD
 *
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Storepickup\Controller\Adminhtml\Index;

use Magento\Framework\App\ResponseInterface;
use Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker;
use Magento\Framework\App\Filesystem\DirectoryList;

class Export extends \Magento\Config\Controller\Adminhtml\System\AbstractConfig
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    protected $_customerFactory;
    protected $_storeLocaterFactory;

    /**
     * @param \Magento\Backend\App\Action\Context                              $context
     * @param \Magento\Config\Model\Config\Structure                           $configStructure
     * @param \Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker
     * @param \Magento\Framework\App\Response\Http\FileFactory                 $fileFactory
     * @param \Magento\Store\Model\StoreManagerInterface                       $storeManager
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Config\Model\Config\Structure $configStructure,
        ConfigSectionChecker $sectionChecker,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
         \Magedelight\Storepickup\Model\StorelocatorFactory $storeLocaterFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Serialize\Serializer\Json $serialize
    ) {
        $this->_storeManager = $storeManager;
        $this->_fileFactory = $fileFactory;
        $this->_storeLocaterFactory = $storeLocaterFactory;
        $this->_customerFactory = $customerFactory;
        $this->serialize = $serialize;
        parent::__construct($context, $configStructure, $sectionChecker);
    }

    /**
     * Export shipping table rates in csv format.
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'storepickup.csv';
        $content = '';
        $_columns = array(
            'storename',
            'url_key',
            'description',
            'website_url',
            'facebook_url',
            'twitter_url',
            'address',
            'city',
            'state',
            'country',
            'zipcode',
            'longitude',
            'latitude',
            'phone_frontend_status',
            'telephone',
            'storeimage',
            'meta_title',
            'meta_keywords',
            'meta_description',
            'is_active',
            'region_id',
            'store_parent_id',
            'store_id',
            'storeemail',
            'days',
            'day_status',
            'open_hour',
            'open_minute',
            'close_hour',
            'close_minute',
            'delete',
        );
        
        $data = array();
        foreach ($_columns as $column) {
            $data[] = '"'.$column.'"';
        }
        
        $content .= implode(',', $data)."\n";

        $storelocaterCollection = $this->_storeLocaterFactory->create()->getCollection();

        foreach($storelocaterCollection as $storelocater){
            $store = [];
            $storeIds = [];
            $store = $storelocater->getData();
            unset($store['product_ids']);
            unset($store['storelocator_id']);
            $storeIds = explode(',', $storelocater->getData('store_ids'));
            $storeIdsString = implode('-', $storeIds);
            $store['store_ids'] = $storeIdsString;
            $store['address'] = str_replace(',', ' ' ,$storelocater->getData('address'));

            unset($store['conditions_serialized']);
            $storetimes = [];
            $storetimes = $this->serialize->unserialize($storelocater->getData('storetime'));
            unset($store['storetime']);
            $content .= implode(',', $store);
            $flag = '1';
            if($storetimes) {
                foreach ($storetimes as $storetime) {
                    if($flag == '1'){
                        $content .= "\n";
                    }
                    $content .= ",,,,,,,,,,,,,,,,,,,,,,,,".implode(',', $storetime)."\n";
                    $flag = '2';
                }
            } else {
                $content .= "\n";
            }
        }

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
