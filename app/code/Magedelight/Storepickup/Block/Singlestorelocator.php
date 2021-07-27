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

use \Magento\Framework\View\Element\Template;
use Magedelight\Storepickup\Model\StorelocatorFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\UrlFactory;
use Magedelight\Storepickup\Model\Source\Country;
use Magedelight\Storepickup\Model\Source\Region;
use Magedelight\Storepickup\Model\Storelocator\Image as ImageModel;
use Magento\Store\Model\ScopeInterface;

class Singlestorelocator extends Template
{

    /**
     * Default image for storelist
     */
    const XML_PATH_STORE_IMAGE = 'magedelight_storepickup/storeinfo/logo';

    /**
     *  Distance in Kilometer/Miles
     */
    const XML_PATH_STORE_DISTANCE = 'magedelight_storepickup/storesearch/distanceunit';
    
    /* Google Map Api Key */
    const XML_PATH_STORE_MAP_API_KEY = 'magedelight_storepickup/googlemap/mapapi';

    /* Map Marker Image*/
     const XML_PATH_STORE_MARKER_IMAGE ='magedelight_storepickup/googlemap/markericon';

     /* Map Marker Image*/
     const IS_ENABLE_FACEBOOK_COMMENT ='magedelight_storepickup/facebookapi/enable_facebook_comment';

     /* Map Marker Image*/
     const FACEBOOK_COMMENT_API ='magedelight_storepickup/facebookapi/facebook_api_key';

     /* Map Marker Image*/
     const FACEBOOK_API_COMMENT_LANGUAGE ='magedelight_storepickup/facebookapi/facebook_language';

    protected $_modelstorelocatorFactory;
    protected $urlFactory;
    protected $countryOptions;
    protected $scopeConfig;

    /**
     * image model
     *
     * @var \Magedelight\Storepickup\Model\Storelocator\Image
     */
    protected $imageModel;
    
    /**
     * @var \Magedelight\Storepickup\Helper\Data
     */
    protected $imageResizerHelper;

    /**
     *
     * @param Context $context
     * @param Registry $registry
     * @param Country $countryOptions
     * @param ImageModel $imageModel
     * @param UrlFactory $urlFactory
     * @param StorelocatorFactory $modelStorelocatorFactory
     */
    public function __construct(
        \Magedelight\Storepickup\Helper\imageResizer $imageResizerHelper,
        Context $context,
        Registry $registry,
        Country $countryOptions,
        Region $regionOptions,
        ImageModel $imageModel,
        UrlFactory $urlFactory,
        StorelocatorFactory $modelStorelocatorFactory,
        \Magento\Framework\Serialize\Serializer\Json $serialize
    ) {
        $this->imageResizerHelper = $imageResizerHelper;
        $this->_modelstorelocatorFactory = $modelStorelocatorFactory;
        $this->urlFactory = $urlFactory;
        $this->countryOptions = $countryOptions;
        $this->regionOptions = $regionOptions;
        $this->registry = $registry;
        $this->imageModel = $imageModel;
        $this->scopeConfig = $context->getScopeConfig();
        $this->serialize = $serialize;
        parent::__construct($context);
    }

    /**
     *
     * @return \Magedelight\Storepickup\Block\Singlestorelocator
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($this->getStoreTitle());
        }

        return $this;
    }

    /**
     *
     * @param string $countryval
     * @return string
     */
    public function getCountryName($countryval)
    {
        $countryArray = $this->countryOptions->getOptions();
        return $countryArray[$countryval];
    }

    /**
     *
     * @return Array
     */
    public function getSingleStoreData()
    {
        $storelocatorid = $this->registry->registry('storelocatorid');
        $storelocatorModel = $this->_modelstorelocatorFactory->create();
        $storelocatorCollection = $storelocatorModel->getCollection();
        $storelocatorCollection->addFieldToFilter('storelocator_id', $storelocatorid);
        $storelocatorData = $storelocatorCollection->getData();
        return $storelocatorData;
    }

    /**
     *
     * @return string
     */
    public function getBackUrl()
    {
        $fronturl = $this->scopeConfig->getValue(
            'magedelight_storepickup/listviewinfo/frontend_url',
            ScopeInterface::SCOPE_STORES
        );

        return $fronturl;
    }

    /**
     *
     * @param type $storelocatorid
     * @return string
     */
    public function getStoreImage($storelocatorid)
    {
        $storelocatorModel = $this->_modelstorelocatorFactory->create();
        $storelocatorModel->load($storelocatorid);
        $storeimage = $storelocatorModel->getStoreimage();
        $storename = $storelocatorModel->getStorename();

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $_config_image = $this->scopeConfig->getValue(self::XML_PATH_STORE_IMAGE, $storeScope);
        if ($storeimage != '') {
            $imagehtml = $this->imageModel->getBaseUrl() . $storeimage;
            return $imagehtml;
        } elseif ($_config_image != '') {
            return $this->imageModel->getBaseUrl() . '/' . $_config_image;
        } else {
            return $this->getViewFileUrl('Magedelight_Storepickup::images/image-default.png');
        }
    }

    /**
     *
     * @param type $storelocatorid
     * @return string
     */
    public function getContactInfo($storelocatorid)
    {
        $storelocatorModel = $this->_modelstorelocatorFactory->create();
        $storelocatorModel->load($storelocatorid);
        $phone = $storelocatorModel->getTelephone();
        $phone_frontend_status = $storelocatorModel->getPhoneFrontendStatus();
        $html = "";

        if ($phone_frontend_status) {
            if ($phone != '') {
                $html .= "<tr><td><strong>Phone no:</strong></td><td>";
                $televalue = explode(':', $phone);
                foreach ($televalue as $key => $singlevalue) {
                    $html .= "<p>" . $singlevalue . "</p>";
                }
                $html .= "</td></tr>";
            }
        }
        return $html;
    }

    /**
     *
     * @return String
     */
    public function getStoreTitle()
    {
        $_storeData = $this->getSingleStoreData();
        return $_storeData[0]['storename'];
    }

    /**
     *
     * @return string
     */
    public function getDistanceUnit()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $_config_DistanceUnit = $this->scopeConfig->getValue(self::XML_PATH_STORE_DISTANCE, $storeScope);
        return $_config_DistanceUnit;
    }
    
    /**
     *
     * @return String
     */
    public function getGoogleMapApiKey()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $_Map_Api_Key = $this->scopeConfig->getValue(self::XML_PATH_STORE_MAP_API_KEY, $storeScope);
        return $_Map_Api_Key;
    }

    /**
     *
     * @return String
     */
    public function isFacebookCommentEnable()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $_Map_Api_Key = $this->scopeConfig->getValue(self::IS_ENABLE_FACEBOOK_COMMENT, $storeScope);
        return $_Map_Api_Key;
    }

    /**
     *
     * @return String
     */
    public function getFacebookCommentApiKey()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $_Map_Api_Key = $this->scopeConfig->getValue(self::FACEBOOK_COMMENT_API, $storeScope);
        return $_Map_Api_Key;
    }

    /**
     *
     * @return String
     */
    public function getFacebookCommentlang()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $_Map_Api_Key = $this->scopeConfig->getValue(self::FACEBOOK_API_COMMENT_LANGUAGE, $storeScope);
        return $_Map_Api_Key;
    }

    /**
     * Get current page Url.
     *
     * @return string
     */
    public function getCurrentPageUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }

    /**
     *
     * @return string
     */
    public function getMarkerImage()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $_marker_image = $this->scopeConfig->getValue(self::XML_PATH_STORE_MARKER_IMAGE, $storeScope);
        $_marker_image_Url = $this->imageModel->getBaseUrl() . '/' . $_marker_image;
        if (empty($_marker_image)) {
            return '';
        }
        return $new_marker_image_Url  = $this->imageResizerHelper->resize($_marker_image_Url, 40, 60);
    }

    /**
     *
     * @param string $regionvalue
     * @return string
     */
    public function getRegionName($region_id)
    {
        $regionArray = $this->regionOptions->getOptions();
        return $regionArray[$region_id];
    }

    /**
     *
     * @param string $storetime
     * @return Array
     */
    public function getStoreTime($storetime)
    {
        return $this->serialize->unserialize($storetime);
    }
}
