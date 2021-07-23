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

namespace Magedelight\Storepickup\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\ObjectManagerInterface;

class StoreList implements ArrayInterface
{

    /**
     * @var Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager
        /* Context $context */
    ) {
        $this->_objectManager = $objectManager;
        /* parent::__construct($context); */
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Storelocator::STATUS_ENABLED,
                'label' => __('Enable')
            ], [
                'value' => Storelocator::STATUS_DISABLED,
                'label' => __('Disable')
            ],
        ];
    }

    /**
     * get options as key value pair
     *
     * @return array
     */
    public function getOptions()
    {
        $default_store_id = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        $storelocator = $this->_objectManager->create('Magedelight\Storepickup\Model\ResourceModel\Storelocator\Collection')
                             ->addFieldToFilter('store_id', $default_store_id);
        $_options = [];
        foreach ($storelocator as $option) {
            $_options[] = [
                'value' => $option['storelocator_id'],
                'label' => $option['storename'],
            ];
        }
        return $_options;
    }
}
