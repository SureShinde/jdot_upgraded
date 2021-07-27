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
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;

class Country implements ArrayInterface
{

    /**
     * @var \Magedelight\Storepickup\Model\Author
     */
    protected $countryCollectionFactory;

    /**
     * @var array
     */
    protected $options;

    /**
     * constructor
     *
     * @param CountryCollectionFactory $countryCollectionFactory
     */
    public function __construct(CountryCollectionFactory $countryCollectionFactory)
    {
        $this->countryCollectionFactory = $countryCollectionFactory;
    }

    /**
     * get options as key value pair
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (is_null($this->options)) {
            $this->options = $this->countryCollectionFactory->create()->toOptionArray(' ');
        }
        return $this->options;
    }

    /**
     * @param array $options
     * @return array
     */
    public function getOptions(array $options = [])
    {
        $countryOptions = $this->toOptionArray($options);
        $_options = [];
        foreach ($countryOptions as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }
}
