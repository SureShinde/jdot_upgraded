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
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime as LibDateTime;
use Magedelight\Storepickup\Model\StorelocatorFactory;
use Magento\Store\Model\ScopeInterface;

class Storetime extends Action
{

    protected $pageFactory;

    /* Shipping Time Interval */

    const XML_PATH_STORE_TIME_INTERVAL = 'magedelight_storepickup/timesloat/timeratio';

    /**
     *
     * @param StoreholidayFactory $storeholidayFactory
     * @param PageFactory $pageFactory
     * @param Context $context
     */
    public function __construct(StorelocatorFactory $modelStorelocatorFactory, ScopeConfigInterface $scopeConfig, PageFactory $pageFactory, DateTime $date, LibDateTime $dateTime, \Magento\Framework\Serialize\Serializer\Json $serialize, Context $context)
    {
        $this->_modelstorelocatorFactory = $modelStorelocatorFactory;
        $this->scopeConfig = $scopeConfig;
        $this->pageFactory = $pageFactory;
        $this->date = $date;
        $this->dateTime = $dateTime;
        $this->serialize = $serialize;
        parent::__construct($context);
    }

    /**
     *
     * @return Array
     */
    public function execute()
    {
        
        $storeDate = $this->getRequest()->getParam('StoreDate');
        $storeID = $this->getRequest()->getParam('storeID');

        $timestamp = strtotime($storeDate);
        $selectedday = date('l', $timestamp);

        $timeSloats = true;

        $_timeInterval = $this->scopeConfig->getValue(self::XML_PATH_STORE_TIME_INTERVAL, ScopeInterface::SCOPE_STORE);

        $storelocatorModel = $this->_modelstorelocatorFactory->create();
        $storelocatorCollection = $storelocatorModel->getCollection();
        $storelocatorCollection->addFieldToFilter('storelocator_id', $storeID);
        $storelocatorCollection->addFieldToSelect('storetime');
        $storelocatorData = $storelocatorCollection->getData();

        $storetime = $this->serialize->unserialize($storelocatorData[0]['storetime']);

        if (!empty($storetime)) {
            foreach ($storetime as $day) {
                if ($day['days'] == $selectedday) {
                    $timeSloats = $this->getTimeSloats($storeDate, $day, $_timeInterval);
                    break;
                }
            }
        }

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($timeSloats);
        return $resultJson;
    }

    /**
     *
     * @param Date $storeDate
     * @param string $day
     * @param int $_timeInterval
     * @return boolean|array
     */
    public function getTimeSloats($storeDate, $day, $_timeInterval)
    {
        
        $tmpstoreDate = $storeDate;
        $storeDate = explode("-", $storeDate);

        $d = mktime($day['open_hour'], $day['open_minute'], 00, $storeDate[1], $storeDate[2], $storeDate[0]);
        $stTime = date("Y-m-d h:i:sa", $d);

        $storeDate = $tmpstoreDate;
        if ($day['close_hour'] == 00) {
            $date = $storeDate;
            $date1 = str_replace('-', '/', $date);
            $storeDate = date('Y-m-d', strtotime($date1 . "+1 days"));
        }

        $storeDate = explode("-", $storeDate);
        $d = mktime($day['close_hour'], $day['close_minute'], 00, $storeDate[1], $storeDate[2], $storeDate[0]);
        $enTime = date("Y-m-d h:i:sa", $d);

        $duration = $_timeInterval;
        $break = 0;

        $start = new \DateTime($stTime);
        $end = new \DateTime($enTime);
        $interval = new \DateInterval("PT" . $duration . "M");
        $breakInterval = new \DateInterval("PT" . $break . "M");

        for ($intStart = $start; $intStart < $end; $intStart->add($interval)->add($breakInterval)) {
            $endPeriod = clone $intStart;
            $endPeriod->add($interval);
            if ($endPeriod > $end) {
                $endPeriod = $end;
            }
            $periods[] = $intStart->format('H:i');
        }

        if (empty($periods)) {
            return true;
        }
        return $periods;
    }
}
