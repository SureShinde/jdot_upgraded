<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2017-12-20T15:05:50+00:00
 * File:          app/code/Xtento/OrderExport/Model/Export/Entity/AbstractEntity.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Entity;

abstract class AbstractEntity extends \Magento\Framework\Model\AbstractModel
{
    protected $collection;
    protected $entityType;
    protected $exportOnlyNewFilter = false;
    protected $returnArray = [];

    /**
     * @var \Xtento\OrderExport\Model\ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var \Xtento\OrderExport\Model\ResourceModel\History\CollectionFactory
     */
    protected $historyCollectionFactory;

    /**
     * @var \Xtento\OrderExport\Model\Export\Data
     */
    protected $exportDataSingleton;

    /**
     * AbstractEntity constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\OrderExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\OrderExport\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory
     * @param \Xtento\OrderExport\Model\Export\Data $exportData
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\OrderExport\Model\ProfileFactory $profileFactory,
        \Xtento\OrderExport\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory,
        \Xtento\OrderExport\Model\Export\Data $exportData,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->profileFactory = $profileFactory;
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->exportDataSingleton = $exportData;
    }

    public function runExport($forcedCollectionItem = false)
    {
        return $this->_runExport($forcedCollectionItem);
    }

    protected function _runExport($forcedCollectionItem = false)
    {
        $exportFields = [];
        // Get validation profile
        /* Alternative approach if conditions check fails, we've seen this happening in Magento 1 installations, the profile conditions were simply empty and the profile needed to be loaded again: */
        $validationProfile = $this->getProfile();
        $exportConditions = $validationProfile->getData('conditions_serialized');
        if (strlen($exportConditions) > 90) {
            // Force load profile for rule validation, as it fails on some stores if the profile is not re-loaded
            $validationProfile = $this->profileFactory->create()->load($this->getProfile()->getId());
        }
        // Reset export classes
        $this->exportDataSingleton->resetExportClasses();
        // Get export fields
        if ($forcedCollectionItem === false) {
            $collectionCount = null;
            $currItemNo = 1;
            $originalCollection = $this->collection;
            $currPage = 1;
            $lastPage = 0;
            $break = false;
            while ($break !== true) {
                $collection = clone $originalCollection;
                $collection->setPageSize(100);
                $collection->setCurPage($currPage);
                $collection->load();
                if (is_null($collectionCount)) {
                    $collectionCount = $collection->getSize();
                    $lastPage = $collection->getLastPageNumber();
                }
                if ($currPage == $lastPage) {
                    $break = true;
                }
                $currPage++;
                foreach ($collection as $collectionItem) {
                    $collectionItemValidated = true;

                    $this->_eventManager->dispatch('xtento_orderexport_custom_validation', [
                        'validationProfile'             => $validationProfile,
                        'collectionItem'                => $collectionItem,
                        'collectionItemValidated'       => &$collectionItemValidated,
                    ]);

                    if ($this->getExportType() == \Xtento\OrderExport\Model\Export::EXPORT_TYPE_TEST || ($collectionItemValidated && $validationProfile->validate($collectionItem))) {
                        $returnData = $this->exportData(new \Xtento\OrderExport\Model\Export\Entity\Collection\Item($collectionItem, $this->entityType, $currItemNo, $collectionCount), $exportFields);
                        if (!empty($returnData)) {
                            $this->returnArray[] = $returnData;
                            $currItemNo++;
                        }
                    }
                }
            }
        } else {
            $rawFilters = $this->getRawCollectionFilters();
            $collectionItemValidated = true;
            // Manually check collection filters against collection item as there is no real collection
            if (is_array($rawFilters)) {
                foreach ($rawFilters as $filter) {
                    foreach ($filter as $filterField => $filterCondition) {
                        $filterField = str_replace("main_table.", "", $filterField);
                        $itemData = $forcedCollectionItem->getData($filterField);
                        foreach ($filterCondition as $filterConditionType => $acceptedValues) {
                            if ($filterConditionType == 'in') {
                                if (!in_array($itemData, $acceptedValues)) {
                                    $collectionItemValidated = false;
                                    break 3;
                                }
                            }
                            // Date filters not implemented (yet?)
                            #var_dump($filterField, $itemData, $acceptedValues);
                        }
                    }
                }
            }
            // "Export only new" filter: For collections, this is joined in the \Xtento\OrderExport\Model\Export model with the exported entity collection directly. This doesn't work for direct model exports. Thus, we need to add the filter here, too.
            if ($this->exportOnlyNewFilter) {
                $historyCollection = $this->historyCollectionFactory->create();
                $historyCollection->addFieldToFilter('entity_id', $forcedCollectionItem->getData('entity_id'));
                $historyCollection->addFieldToFilter('entity', $this->getProfile()->getEntity());
                $historyCollection->addFieldToFilter('profile_id', $this->getProfile()->getId());
                if ($historyCollection->getSize() > 0) {
                    $collectionItemValidated = false;
                }
            }
            #Zend_Debug::dump($forcedCollectionItem->getData());
            #var_dump($collectionItemValidated);
            #die();
            $this->_eventManager->dispatch('xtento_orderexport_custom_validation', [
                'validationProfile'             => $validationProfile,
                'collectionItem'                => $forcedCollectionItem,
                'collectionItemValidated'       => &$collectionItemValidated,
            ]);
            // If all filters pass, then export the item
            if ($this->getExportType() == \Xtento\OrderExport\Model\Export::EXPORT_TYPE_TEST || ($collectionItemValidated && $validationProfile->validate($forcedCollectionItem))) {
                $returnData = $this->exportData(new \Xtento\OrderExport\Model\Export\Entity\Collection\Item($forcedCollectionItem, $this->entityType, 1, 1), $exportFields);
                if (!empty($returnData)) {
                    $this->returnArray[] = $returnData;
                }
            }
        }
        #var_dump(__FILE__, $this->returnArray); die();
        return $this->returnArray;
    }

    public function setCollectionFilters($filters)
    {
        if (is_array($filters)) {
            foreach ($filters as $filter) {
                foreach ($filter as $attribute => $filterArray) {
                    $this->collection->addAttributeToFilter($attribute, $filterArray);
                }
            }
        }
        $this->setRawCollectionFilters($filters);
        return $this->collection;
    }

    public function addExportOnlyNewFilter()
    {
        $this->exportOnlyNewFilter = true;
    }

    protected function exportData($collectionItem, $exportFields)
    {
        return $this->exportDataSingleton
            ->setShowEmptyFields($this->getShowEmptyFields())
            ->setProfile($this->getProfile() ? $this->getProfile() : new \Magento\Framework\DataObject)
            ->setExportFields($exportFields)
            ->getExportData($this->entityType, $collectionItem);
    }
}