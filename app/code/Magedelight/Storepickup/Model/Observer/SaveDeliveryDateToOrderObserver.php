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

namespace Magedelight\Storepickup\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magedelight\Storepickup\Model\StorelocatorFactory;

class SaveDeliveryDateToOrderObserver implements ObserverInterface
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectmanager, StorelocatorFactory $modelStorelocatorFactory)
    {
        $this->_objectManager = $objectmanager;
        $this->_modelstorelocatorFactory = $modelStorelocatorFactory;
    }

    public function execute(EventObserver $observer)
    {
        $order = $observer->getOrder();
        $quoteRepository = $this->_objectManager->create('Magento\Quote\Model\QuoteRepository');
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $quoteRepository->get($order->getQuoteId());
        $customerId = $order->getCustomerId();
        $shippingMethod = $order->getShippingMethod();
        $order->setPickupStore($quote->getPickupStore());
        $order->setPickupDate($quote->getPickupDate());
        
        $storeId = $quote->getPickupStore();
        if($storeId != ''){
            if($shippingMethod == "storepickup_storepickup"){
                $storePickupData = $this->getStorePickupAddress($storeId);
                $order->getShippingAddress()->addData($storePickupData);
            }
        }
        return $this;
    }
    
    public function getStorePickupAddress($storeId){
        $fields = ['storename', 'address', 'city', 'state','region_id', 'country', 'zipcode', 'telephone'];
        $storelocatorModel = $this->_modelstorelocatorFactory->create();
        $storelocatorCollection = $storelocatorModel->getCollection();
        $storelocatorCollection->addFieldToFilter('storelocator_id', $storeId);
        $storelocatorCollection->addFieldToSelect($fields);
        $storelocatorData = $storelocatorCollection->getData();
        $storelocatorAddress = [
            'firstname' => $storelocatorData[0]['storename'],
            'lastname' => '',
            'street' => $storelocatorData[0]['address'],
            'city' => $storelocatorData[0]['city'],
            'country_id' => $storelocatorData[0]['country'],
            'region' => $storelocatorData[0]['region_id'],
            'postcode' => $storelocatorData[0]['zipcode'],
            'telephone' => $storelocatorData[0]['telephone'],
            'fax' => '',
            'save_in_address_book' => 1
        ];
        return $storelocatorAddress;
    } 
}
