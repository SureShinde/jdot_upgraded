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
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Action\Context;
use Magedelight\Storepickup\Model\StoreholidayFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magedelight\Storepickup\Model\StorelocatorFactory;

class Holidayday extends Action
{

    protected $pageFactory;
    protected $storeholidayFactory;

    /* Shipping Off Days */
//    const XML_PATH_STORE_OFFDAY = 'carriers/storepickup/offdays';
    
    /**
     *
     * @param StoreholidayFactory $storeholidayFactory
     * @param PageFactory $pageFactory
     * @param Context $context
     */
    public function __construct(StoreholidayFactory $storeholidayFactory, ScopeConfigInterface $scopeConfig, PageFactory $pageFactory, StorelocatorFactory $modelStorelocatorFactory, \Magento\Framework\Serialize\Serializer\Json $serialize, Context $context)
    {
        $this->storeholidayFactory = $storeholidayFactory;
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
        $storeId = $this->getRequest()->getParam('StoreID');

        $storelocatorModel = $this->storeholidayFactory->create();
        $storelocatorCollection = $storelocatorModel->getCollection();
        $storelocatorCollection->addFieldToFilter('is_active', '1');
        $_storecollection = $this->getStoreFilter($storelocatorCollection, $storeId);

        $_disabledays['dates'] = $this->getStoreHolidays($_storecollection);

        $_disabledays['days'] = $this->getStoreOffdays($storeId);

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($_disabledays);
        return $resultJson;
    }

    /**
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

    /**
     *
     * @param array $_storecollection
     * @return array
     */
    public function getStoreHolidays($_storecollection)
    {
        $_holidaydays;
        foreach ($_storecollection as $key => $store) {
            $from_date = date_create($store['holiday_date_from']);
            $to_date = date_create($store['holiday_date_to']);
            $diff = date_diff($from_date, $to_date);

            if ($diff->format("%a") == 0) {
                if ($store['is_repetitive'] == 1) {
                    list($y, $m, $d) = explode('-', $store['holiday_date_from']);
                    $newdate = (date("Y")) . "-$m-$d";
                    $date = date("n-j-Y", strtotime($newdate . "+0 days"));
                    $_holidaydays['repetitive'][] = $date;
                } else {
                    $date = date("n-j-Y", strtotime($store['holiday_date_from'] . "+0 days"));
                    $_holidaydays['normal'][] = $date;
                }
            } elseif ($diff->format("%a") > 0) {
                if ($store['is_repetitive'] == 1) {
                    list($y, $m, $d) = explode('-', $store['holiday_date_from']);
                    $newdate = (date("Y")) . "-$m-$d";

                    for ($i = 0; $i <= $diff->format("%a"); $i++) {
                        $date = date("n-j-Y", strtotime($newdate . "+" . $i . "days"));
                        $_holidaydays['repetitive'][] = $date;
                    }
                } else {
                    for ($i = 0; $i <= $diff->format("%a"); $i++) {
                        $date = date("n-j-Y", strtotime($store['holiday_date_from'] . "+" . $i . "days"));
                        $_holidaydays['normal'][] = $date;
                    }
                }
            }
        }
        /* Shipping Off Days  */
        $_OffDays = $this->scopeConfig->getValue(
            'carriers/storepickup/offdays',
            ScopeInterface::SCOPE_STORES
        );
        if ($_OffDays >= 1) {
            for ($i=0; $i < $_OffDays; $i++) {
                $date = date("n-j-Y", strtotime(date("Y/m/d") . "+" . $i . "days"));
                $_holidaydays[] = $date;
            }
        }
        
        if (empty($_holidaydays)) {
            return true;
        }
        return $_holidaydays;
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
