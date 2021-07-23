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

namespace Magedelight\Storepickup\Block\Adminhtml;

use \Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magedelight\Storepickup\Model\Source\IsOpen;

/**
 * Adminhtml Store time renderer
 */
class Storetime extends Template
{

    /**
     * @var string
     */
    protected $_template = 'storelocator/storetime.phtml';

    /**
     *
     * @param Context $context
     * @param IsOpen $IsOpen
     */
    public function __construct(
        Context $context,
        IsOpen $IsOpen
    ) {
        $this->IsOpen = $IsOpen;
        parent::__construct($context);
    }

    /**
     * Call Template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('storelocator/storetime.phtml');
    }

    /**
     * Retrieve list of initial customer groups
     *
     * @return array
     */
    protected function _getInitialCustomerGroups()
    {
        return [$this->_groupManagement->getAllCustomersGroup()->getId() => __('ALL GROUPS')];
    }

    /**
     *
     * @return Array
     */
    public function getDays()
    {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    }

    /**
     *
     * @return Array
     */
    public function getIsOpen()
    {
        return $this->IsOpen->getOptions();
    }
}
