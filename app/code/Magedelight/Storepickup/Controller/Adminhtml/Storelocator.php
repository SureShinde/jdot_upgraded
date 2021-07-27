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

namespace Magedelight\Storepickup\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magedelight\Storepickup\Model\StorelocatorFactory;
use Magento\Framework\Registry;
use Magedelight\Storepickup\Helper\Data as Storehelper;

abstract class Storelocator extends Action
{

    /**
     * storelocator factory
     *
     * @var AuthorFactory
     */
    protected $storelocatorFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    
    /**
     * date filter
     *
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    /**
     * @param Registry $registry
     * @param AuthorFactory $storelocatorFactory
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        StorelocatorFactory $storelocatorFactory,
        Context $context,
        Storehelper $storeHelper
    ) {
        $this->coreRegistry = $registry;
        $this->storelocatorFactory = $storelocatorFactory;
        $this->resultRedirectFactory = $context->getRedirect();
        $this->storeHelper = $storeHelper;
        parent::__construct($context);
    }

    /**
     *
     * @return object Magedelight\Storepickup\Model\Storelocator
     */
    protected function initStorelocator()
    {
        $parent_id = (int) $this->getRequest()->getParam('store_parent_id');
        $storelocatorId = (int) $this->getRequest()->getParam('storelocator_id');
        $md_store_id = $this->getRequest()->getParam('store');

        /** @var \Magedelight\Storepickup\Model\Storelocator $storelocator */
        if ($parent_id) {
            $storelocator = $this->storelocatorFactory->create()
                         ->getCollection()
                         ->addFieldToFilter('store_parent_id', $parent_id)
                         ->addFieldToFilter('store_id', $md_store_id)
                         ->getLastItem();

            $slocatorid = $storelocator->getStorelocatorId();
            if (isset($slocatorid)) {
                $storelocator->load($slocatorid);
            } else {
                $storelocator->load($parent_id);
            }
        } else {
            $storelocator = $this->storelocatorFactory->create();
        }
        $this->coreRegistry->register('magedelight_storelocator_storelocator', $storelocator);
        return $storelocator;
    }
}
