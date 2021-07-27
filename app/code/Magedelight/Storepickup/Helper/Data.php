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
 * @package Magedelight_ScheduleShippig
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Storepickup\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    
    /**
     * Custom fee config path
     */
    const CONFIG_MODULE_IS_ENABLED = 'magedelight_storepickup/storeinfo/storepickupenable';
    const CONFIG_MODULE_IS_ALLOW_GEST_CUSTOMER = "magedelight_storepickup/storeinfo/allowguestcustomer";
    
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) 
    {
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }
    
    public function isEnabled()
    {
        $currentUrl = $this->_storeManager->getStore()->getBaseUrl();
        $domain = $this->getDomainName($currentUrl);
        $selectedWebsites = $this->getConfig('magedelight_storepickup/storeinfo/select_website');
        $websites = explode(',',$selectedWebsites);
        if(in_array($domain, $websites) && $this->getConfig('magedelight_storepickup/storeinfo/enable') && $this->getConfig('magedelight_storepickup/license/data'))
        {
          return true;
        }else{
          return false;
        }
    }

    public function getDomainName($domain){
        $string = '';
        
        $withTrim = str_replace(array("www.","http://","https://"),'',$domain);
        
        /* finding the first position of the slash  */
        $string = $withTrim;
        
        $slashPos = strpos($withTrim,"/",0);
        
        if($slashPos != false){
            $parts = explode("/",$withTrim);
            $string = $parts[0];
        }
        return $string;
    }

    public function getWebsites()
    {
        $websites = $this->_storeManager->getWebsites();
        $websiteUrls = array();
        foreach($websites as $website)
        {
            foreach($website->getStores() as $store){
                $wedsiteId = $website->getId();
                $storeObj = $this->_storeManager->getStore($store);
                $storeId = $storeObj->getId();
                $url = $storeObj->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
                $parsedUrl = parse_url($url);
                $websiteUrls[] = str_replace(array('www.', 'http://', 'https://'), '', $parsedUrl['host']);
            }
        }

        return $websiteUrls;
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * @return mixed
     */
    public function isModuleEnabled()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $isEnabled = $this->scopeConfig->getValue(self::CONFIG_MODULE_IS_ENABLED, $storeScope);
        return $isEnabled;
    }

    public function isAllowGuestCustomer()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $isAllow= $this->scopeConfig->getValue(self::CONFIG_MODULE_IS_ALLOW_GEST_CUSTOMER, $storeScope);
        return $isAllow;
    }
    
    public function _getDefaultStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }
}
