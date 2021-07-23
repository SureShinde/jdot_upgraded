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

namespace Magedelight\Storepickup\Model\Config\Backend;


class ImportStore extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magedelight\Storelocator\Model\ResourceModel\Storelocator
     */
    protected $_storelocatorFactory;

    /**
     * @param \Magento\Framework\Model\Context                           $context
     * @param \Magento\Framework\Registry                                $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface         $config
     * @param \Magento\Framework\App\Cache\TypeListInterface             $cacheTypeList
     * @param \Magedelight\Customerprice\Model\ResourceModel\CustomerpriceFactory $customerpriceFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource    $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb              $resourceCollection
     * @param array                                                      $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magedelight\Storepickup\Model\StorelocatorFactory $storelocatorFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_storelocatorFactory = $storelocatorFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return $this
     */
    public function afterSave()
    {   
        $tableRate = $this->_storelocatorFactory->create();
        $tableRate->uploadAndImport($this);

        return parent::afterSave();
    }
}
