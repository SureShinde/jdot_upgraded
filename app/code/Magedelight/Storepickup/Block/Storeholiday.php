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

namespace Magedelight\Storepickup\Block;

use \Magento\Framework\View\Element\Template;
use Magedelight\Storepickup\Model\StorelocatorFactory;
use Magedelight\Storepickup\Model\StoreholidayFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\UrlFactory;
use Magedelight\Storepickup\Model\Source\Country;
use Magedelight\Storepickup\Model\Storelocator\Image as ImageModel;
use Magento\Store\Model\ScopeInterface;

class Storeholiday extends Template
{

    
    protected $_modelstorelocatorFactory;
    protected $urlFactory;
    protected $countryOptions;
    protected $scopeConfig;

    /**
     * image model
     *
     * @var \Magedelight\Storepickup\Model\Storelocator\Image
     */
    protected $imageModel;

    /**
     *
     * @param Context $context
     * @param Registry $registry
     * @param Country $countryOptions
     * @param ImageModel $imageModel
     * @param UrlFactory $urlFactory
     * @param StorelocatorFactory $modelStorelocatorFactory
     * @param StoreholidayFactory $storeholidayFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Country $countryOptions,
        ImageModel $imageModel,
        UrlFactory $urlFactory,
        StorelocatorFactory $modelStorelocatorFactory,
        StoreholidayFactory $storeholidayFactory
    ) {
        $this->_modelstorelocatorFactory = $modelStorelocatorFactory;
        $this->storeholidayFactory = $storeholidayFactory;
        $this->urlFactory = $urlFactory;
        $this->countryOptions = $countryOptions;
        $this->registry = $registry;
        $this->imageModel = $imageModel;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
    }

    
    /**
     *
     * @return Array
     */
    public function getSingleStoreData()
    {
        $storelocatorid = $this->registry->registry('storelocatorid');
        $storelocatorModel = $this->_modelstorelocatorFactory->create();
        $storelocatorCollection = $storelocatorModel->getCollection();
        $storelocatorCollection->addFieldToFilter('storelocator_id', $storelocatorid);
        $storelocatorData = $storelocatorCollection->getData();
        return $storelocatorData;
    }

    /**
     *
     * @param type $storeId
     * @return array
     */
    public function getHoliday($storeId)
    {
        $storelocatorModel = $this->storeholidayFactory->create();
        $md_store_id = $this->_storeManager->getStore()->getId();
        $storelocatorCollection = $storelocatorModel->getCollection();
        $storelocatorCollection->addFieldToFilter('is_active', '1')
                               ->addFieldToFilter('store_id', [0,$md_store_id]);
                               
        if (!empty($storeId)) {
            $tmp_store_id = $storeId;
            $parent_id = $this->_modelstorelocatorFactory->create()
                                     ->getCollection()
                                     ->addFieldToFilter('is_active', '1')
                                     ->addFieldToFilter('store_id', $md_store_id)
                                     ->addFieldToFilter('storelocator_id', $tmp_store_id)
                                     ->getFirstItem();
            $parent_store_id = $parent_id->getStoreParentId();
            if (!empty($parent_store_id)) {
                $storeId = $parent_store_id;
            } else {
                $storeId = $tmp_store_id;
            }
        }
        $tmp_collection = $this->storeholidayFactory->create()
             ->getCollection()
             ->addFieldToFilter('holiday_applied_stores', ['like' => '%'.$storeId.'%'])
             ->addFieldToFilter('store_id', $md_store_id);
             
        if (!empty($tmp_collection->getData())) {
            $finalcollection = $this->getStoreFilter($tmp_collection, $storeId);
            $_holidaydays = $this->getStoreHolidays($finalcollection);
        } else {
            $_storecollection = $this->getStoreFilter($storelocatorCollection, $storeId);
            $_holidaydays = $this->getStoreHolidays($_storecollection);
        }
       
        return $_holidaydays;
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
     * @param Array $_storecollection
     * @return Array
     */
    public function getStoreHolidays($_storecollection)
    {
        $_holidaydays = [];
        foreach ($_storecollection as $key => $store) {
            $from_date = date_create($store['holiday_date_from']);
            $to_date = date_create($store['holiday_date_to']);
            $diff = date_diff($from_date, $to_date);

            if ($diff->format("%a") == 0) {
                if ($store['is_repetitive'] == 1) {
                    list($y, $m, $d) = explode('-', $store['holiday_date_from']);
                    $newdate = (date("Y")) . "-$m-$d";
                    $date = date("l jS F", strtotime($newdate . "+0 days"));
                    $_holidaydays[] = [
                        'date' => $date,
                        'holiday_name' => $store['holiday_name'],
                        'holiday_comment' => $store['holiday_comment']
                        ];
                } else {
                    $_year_check = date_create($store['holiday_date_from']);
                    if ($_year_check->format("Y") == date("Y")) {
                        $date = date("l jS F", strtotime($store['holiday_date_from'] . "+0 days"));
                        $_holidaydays[] = [
                        'date' => $date,
                        'holiday_name' => $store['holiday_name'],
                        'holiday_comment' => $store['holiday_comment']
                        ];
                    }
                }
            } elseif ($diff->format("%a") > 0) {
                if ($store['is_repetitive'] == 1) {
                    list($y, $m, $d) = explode('-', $store['holiday_date_from']);
                    $newdate = (date("Y")) . "-$m-$d";

                    for ($i = 0; $i <= $diff->format("%a"); $i++) {
                        $date = date("l jS F", strtotime($newdate . "+" . $i . "days"));
                         $_holidaydays[] = [
                        'date' => $date,
                        'holiday_name' => $store['holiday_name'],
                        'holiday_comment' => $store['holiday_comment']
                         ];
                    }
                } else {
                    $_year_check = date_create($store['holiday_date_from']);
                    if ($_year_check->format("Y") == date("Y")) {
                        for ($i = 0; $i <= $diff->format("%a"); $i++) {
                            $date = date("l jS F", strtotime($store['holiday_date_from'] . "+" . $i . "days"));
                             $_holidaydays[] = [
                            'date' => $date,
                            'holiday_name' => $store['holiday_name'],
                            'holiday_comment' => $store['holiday_comment']
                             ];
                        }
                    }
                }
            }
        }
        if (empty($_holidaydays)) {
            return false;
        }
        return $_holidaydays;
    }
}
