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

namespace Magedelight\Storepickup\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magedelight\Storepickup\Model\StorelocatorFactory;

class Loadstore extends Action
{

    public function __construct(ScopeConfigInterface $scopeConfig, PageFactory $pageFactory, StorelocatorFactory $modelStorelocatorFactory, \Magento\Framework\Serialize\Serializer\Json $serialize, Context $context)
    {
        $this->scopeConfig = $scopeConfig;
        $this->pageFactory = $pageFactory;
        $this->_modelstorelocatorFactory = $modelStorelocatorFactory;
        $this->serialize = $serialize;
        parent::__construct($context);
    }

    /**
     *
     * @return Array
     */
    public function execute()
    {
        /*$storeId = $this->getRequest()->getParam('StoreID');*/

        $storecollection = $this->getStoreCollection();

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($_disabledays);
        return $resultJson;
    }

    public function getStoreCollection()
    {
        $storeModel = $this->_modelstorelocatorFactory->create();
        $storeCollection = $storeModel->getCollection();
        $storeCollection->addFieldToFilter('is_active', 1);

        $id = $this->getRequest()->getParam('productid');
        
        if ($id) {
            foreach ($storeCollection as $store) {
                $productIds = explode(',', $store->getProductIds());
                if (in_array($id, $productIds)) {
                    $selectedProductids[] = $store->getStorelocatorId();
                }
            }
            $storeCollection->addFieldToFilter('storelocator_id', [$selectedProductids]);
        }
    }

    /**
     *
     * @param object $storelocatorCollection
     * @param int $storeId
     * @return Array
     */
    public function getStoreFilter($storelocatorCollection, $storeId)
    {
        $_storecollection = $storelocatorCollection->getData();
        /* holiday_applied_stores || 0 */
        $_elementkey = 0;
        foreach ($storelocatorCollection as $collection) {
            $_appliedStore = $collection->getHolidayAppliedStores();
            $_appliedStore = explode(',', $_appliedStore);

            if (in_array('0', $_appliedStore, true) || in_array($storeId, $_appliedStore, true)) {
            } else {
                unset($_storecollection[$_elementkey]);
            }
            $_elementkey += 1;
        }
        return $_storecollection;
    }

    public function getStoreOffdays($storeId)
    {

        $storeModel = $this->_modelstorelocatorFactory->create();
        $storeCollection = $storeModel->getCollection();
        $storeCollection->addFieldToFilter('storelocator_id', $storeId);
        $storeCollection->addFieldToSelect('storetime');

        $storeData = $storeCollection->getData();
        $storetime = $this->serialize->unserialize($storeData[0]['storetime']);

        $daysIndex = ['Sunday' => 0, 'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6 ];

        if (!empty($storetime)) {
            foreach ($storetime as $day) {
                if ($day['day_status'] == 0) {
                    $days[] = $daysIndex[$day['days']];
                }
            }
        }

        if (empty($days)) {
            return true;
        }
        return $days;
    }
}
