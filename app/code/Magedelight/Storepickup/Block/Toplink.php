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

class Toplink extends \Magento\Framework\View\Element\Html\Link
{

    
    protected $_template = 'Magedelight_Storepickup::toplink.phtml';

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     *
     * @return string
     */
    public function getHref()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $urlKey = trim($this->scopeConfig->getValue('magedelight_storepickup/listviewinfo/frontend_url', $storeScope), '/');
        if (empty($urlKey)) {
            $urlKey = 'storepickup';
        }
        $suffix = trim($this->scopeConfig->getValue('magedelight_storepickup/listviewinfo/listpage_suffix', $storeScope), '/');
        $urlKey .= (strlen($suffix) > 0 || $suffix != '') ? '.' . str_replace('.', '', $suffix) : '/';
        return $this->_storeManager->getStore()->getBaseUrl() . $urlKey;
    }

    /**
     *
     * @return label
     */
    public function getLabel()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $label = $this->scopeConfig->getValue('magedelight_storepickup/storeinfo/linktitle', $storeScope);
        if (empty($label)) {
            $label = 'storelocator';
        }
        return __($label);
    }

    /**
     *
     * @return boolean
     */
    public function isStorelocatorEnable()
    {
        return $this->scopeConfig->getValue('magedelight_storepickup/storeinfo/storepickupenable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     *
     * @return boolean
     */
    public function isTopLinkDisplay()
    {
        return $this->scopeConfig->getValue('magedelight_storepickup/storeinfo/displaytopmenu', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'toplink' ? true : false;
    }
}
