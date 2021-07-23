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

namespace Magedelight\Storepickup\Controller\Tag;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magedelight\Storepickup\Model\TagFactory;
use Magedelight\Storepickup\Model\StorelocatorFactory;

class Stores extends Action
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
    public function __construct(
        TagFactory $tagFactory,
        Context $context,
        StorelocatorFactory $modelStorelocatorFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_tagFactory = $tagFactory;
        $this->_storelocatorFactory = $modelStorelocatorFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     *
     * @return Array
     */
    public function execute()
    {

        $selectedFilter = $this->getRequest()->getParam('SelectedFilter');
        $tagStores = [];
        
        $tagCollection = $this->_tagFactory->create();
        $md_store_id = $this->_storeManager->getStore()->getId();
        $collection = $tagCollection->getCollection()
                        ->addFieldToFilter('tag_id', $selectedFilter)
                        ->addFieldToFilter('is_active', 1);
        if ($collection->getData()>0) {
            foreach ($collection as $tag) {
                if ($tag->getStoreIds()) {
                    $stores = explode('&', $tag->getStoreIds());
                    foreach ($stores as $value) {
                        if (!in_array($value, $tagStores)) {
                            if ($value) {
                                $slocatorModel= $this->_storelocatorFactory->create()
                                    ->getCollection()
                                    ->addFieldToFilter('is_active', '1')
                                    ->addFieldToFilter('store_parent_id', $value)
                                    ->addFieldToFilter('store_id', $md_store_id)
                                    ->getFirstItem();
                                $slocator_id = $slocatorModel->getStorelocatorId();
                                if ($slocator_id) {
                                    $storeloactorid = $slocator_id;
                                } else {
                                    $slocatorModel->load($value);
                                    if ($slocatorModel->getData() >0 && $slocatorModel->getIsActive()) {
                                        $storeloactorid = $value;
                                    } else {
                                        $storeloactorid = 0;
                                    }
                                }
                                $tagStores[] = $storeloactorid;
                            }
                        }
                    }
                } else {
                    $tagStores[] = "empty";
                }
            }
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($tagStores);
        return $resultJson;
    }
}
