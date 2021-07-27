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

namespace Magedelight\Storepickup\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\UrlInterface;

class TopMenu implements ObserverInterface
{

    protected $request;
    protected $scopeConfig;

    /**
     * url builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     *
     * @param Http $request
     * @param UrlInterface $urlBuilder
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Http $request,
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
    }

    /**
     * @param $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {

        /** @var \Magento\Framework\Data\Tree\Node $menu */
        $storelocatorflag = $this->scopeConfig->getValue(
            'magedelight_storepickup/storeinfo/storepickupenable',
            ScopeInterface::SCOPE_STORES
        );

        $topmenufalag = $this->scopeConfig->getValue(
            'magedelight_storepickup/storeinfo/displaytopmenu',
            ScopeInterface::SCOPE_STORES
        );

        if ($storelocatorflag && $topmenufalag == 'topnavagation') {
            $fronturlkey = $this->scopeConfig->getValue(
                'magedelight_storepickup/listviewinfo/frontend_url',
                ScopeInterface::SCOPE_STORES
            );

            if ($fronturlkey) {
                $fronturl = $this->urlBuilder->getUrl('', ['_direct' => $fronturlkey]);
            } else {
                $fronturlkey = 'storepickup';
                $fronturl = $this->urlBuilder->getUrl('storepickup');
            }
            
            $fronturl = rtrim($fronturl, "/");
            $menu = $observer->getMenu();
            $tree = $menu->getTree();
            $storelocatorNodeId = 'storepickup';
            $data = [
                'name' => $this->getLabel(),
                'id' => $storelocatorNodeId,
                'url' => $fronturl,
            ];
            $storelocatorNode = new Node($data, 'id', $tree, $menu);
            $menu->addChild($storelocatorNode);
        }
        return $this;
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
}
