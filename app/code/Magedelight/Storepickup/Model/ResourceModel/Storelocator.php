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

namespace Magedelight\Storepickup\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime as LibDateTime;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;
use Magento\Framework\Event\ManagerInterface;
use Magento\Catalog\Model\Product;

class Storelocator extends AbstractDb
{

    /**
     * Store model
     *
     * @var \Magento\Store\Model\Store
     */
    protected $store = null;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param Context $context
     * @param DateTime $date
     * @param StoreManagerInterface $storeManager
     * @param LibDateTime $dateTime
     * @param ManagerInterface $eventManager
     * @param AuthorProduct $authorProduct
     * @param AuthorCategory $authorCategory
     */
    public function __construct(
        Context $context,
        DateTime $date,
        StoreManagerInterface $storeManager,
        LibDateTime $dateTime,
        ManagerInterface $eventManager
    ) {
        $this->date = $date;
        $this->storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->eventManager = $eventManager;

        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('magedelight_storelocator', 'storelocator_id');
    }

    /**
     * Process author data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        $condition = ['storelocator_id = ?' => (int) $object->getId()];
        $this->getConnection()->delete($this->getTable('magedelight_storelocator'), $condition);
        return parent::_beforeDelete($object);
    }

    protected function _beforeSave(AbstractModel $object)
    {

        $urlKey = $object->getData('url_key');
        if ($urlKey == '') {
            $urlKey = $object->getStorename();
        }
        $urlKey = $object->formatUrlKey($urlKey);
        $object->setUrlKey($urlKey);
        $validKey = false;
        while (!$validKey) {
            if ($this->getIsUniqueStorelocatorToStores($object)) {
                $validKey = true;
            } else {
                $parts = explode('-', $urlKey);
                $last = $parts[count($parts) - 1];
                if (!is_numeric($last)) {
                    $urlKey = $urlKey . '-1';
                } else {
                    $suffix = '-' . ($last + 1);
                    unset($parts[count($parts) - 1]);
                    $urlKey = implode('-', $parts) . $suffix;
                }
                $object->setData('url_key', $urlKey);
            }
        }
        return parent::_beforeSave($object);
    }

    public function getIsUniqueStorelocatorToStores(AbstractModel $object)
    {
        if ($this->storeManager->hasSingleStore() || !$object->hasStores()) {
            $stores = [Store::DEFAULT_STORE_ID];
        } else {
            $stores = (array) $object->getData('stores');
        }
        $select = $this->getLoadByUrlKeySelect($object->getData('url_key'), $stores);
        if ($object->getId()) {
            $select->where('storelocator.storelocator_id <> ?', $object->getId());
        }
        if ($this->getConnection()->fetchRow($select)) {
            return false;
        }
        return true;
    }

    protected function getLoadByUrlKeySelect($urlKey, $store, $isActive = null)
    {
        $select = $this->getConnection()
                ->select()
                ->from(['storelocator' => $this->getMainTable()])
                ->where(
                    'storelocator.url_key = ?',
                    $urlKey
                );
        if (!is_null($isActive)) {
            $select->where('storelocator.is_active = ?', $isActive);
        }
        return $select;
    }

    /**
     * Set store model
     *
     * @param Store $store
     * @return $this
     */
    public function setStore(Store $store)
    {
        $this->store = $store;
        return $this;
    }

    /**
     * Retrieve store model
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->storeManager->getStore($this->store);
    }

    public function checkUrlKey($urlKey, $storeId)
    {
        $stores = [Store::DEFAULT_STORE_ID, $storeId];
        $select = $this->getLoadByUrlKeySelect($urlKey, $stores, 1);
        $select->reset(\Zend_Db_Select::COLUMNS)
                ->columns('storelocator.storelocator_id')
                ->limit(1);
        return $this->getConnection()->fetchOne($select);
    }
}
