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

use Magento\Framework\View\Element\Template;
use Magedelight\Storepickup\Model\StorelocatorFactory;
use Magedelight\Storepickup\Model\TagFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\UrlFactory;
use Magedelight\Storepickup\Model\Storelocator\Image as ImageModel;
use Magedelight\Storepickup\Model\Source\Country;
use Magedelight\Storepickup\Model\Source\Region;
use Magento\Framework\View\Result\PageFactory;
use Magento\Checkout\Model\Cart;
use \Magento\Framework\Controller\Result\JsonFactory;

class PaypalReview extends Template
{

    protected $_modelstorelocatorFactory;
    protected $urlFactory;
    protected $countryOptions;
    protected $regionOptions;
    protected $pageFactory;
    
    /**
     * @var \Magedelight\Storepickup\Helper\Data
     */
    protected $dataHelper;
    
    /**
     * @var \Magedelight\Storepickup\Helper\Data
     */
    protected $imageResizerHelper;

    /**
     * @ Default Store image
     */
    const XML_PATH_STORE_IMAGE = 'magedelight_storepickup/storeinfo/logo';

    /**
     *  Store Title
     */
    const XML_PATH_STORE_TITLE = 'magedelight_storepickup/listviewinfo/frontend_title';

    /**
     *  Meta Information
     */
    const META_DESCRIPTION_CONFIG_PATH = 'magedelight_storepickup/listviewinfo/meta_description';
    const META_Title_CONFIG_PATH = 'magedelight_storepickup/listviewinfo/meta_title';
    const META_KEYWORDS_CONFIG_PATH = 'magedelight_storepickup/listviewinfo/meta_keywords';

    /* Distance in Kilometer/Miles  */
    const XML_PATH_STORE_DISTANCE = 'magedelight_storepickup/storesearch/distanceunit';
    
    /* Default Distance  */
    const XML_PATH_STORE_RADIOUS= 'magedelight_storepickup/storesearch/defaultradious';
    
    /* Max Radious */
    const XML_PATH_STORE_MAXRADIOUS = 'magedelight_storepickup/storesearch/maxradious';
    
    /* Google Map Api Key */
    const XML_PATH_STORE_MAP_API_KEY = 'magedelight_storepickup/googlemap/mapapi';

    /* Map Marker Image*/
    const XML_PATH_STORE_MARKER_IMAGE ='magedelight_storepickup/googlemap/markericon';
    
    /* Map Marker Image*/
    const XML_PATH_STORE_TIME ='magedelight_storepickup/timesloat/timesloatenable';

    /* Map Marker Image*/
    const ENABLE_CURRENT_LOCATION ='magedelight_storepickup/googlemap/enable_current_location';

    /**
     * image model
     *
     * @var \Magedelight\Storepickup\Model\Storelocator\Image
     */
    protected $imageModel;

    /**
     *
     * @param Context $context
     * @param Registry $registry
     * @param Country $countryOptions
     * @param Region $regionOptions
     * @param ImageModel $imageModel
     * @param UrlFactory $urlFactory
     * @param PageFactory $pageFactory
     * @param StorelocatorFactory $modelStorelocatorFactory
     * @param Cart $cart
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magedelight\Storepickup\Helper\Data $dataHelper,
        \Magedelight\Storepickup\Helper\imageResizer $imageResizerHelper,
        Context $context,
        Registry $registry,
        Country $countryOptions,
        Region $regionOptions,
        ImageModel $imageModel,
        UrlFactory $urlFactory,
        PageFactory $pageFactory,
        StorelocatorFactory $modelStorelocatorFactory,
        TagFactory $tagFactory,
        Cart $cart,
        JsonFactory $resultJsonFactory
    ) {
        $this->dataHelper = $dataHelper;
        $this->imageResizerHelper = $imageResizerHelper;
        $this->_modelstorelocatorFactory = $modelStorelocatorFactory;
        $this->_tagFactory = $tagFactory;
        $this->urlFactory = $urlFactory;
        $this->pageFactory = $pageFactory;
        $this->imageModel = $imageModel;
        $this->countryOptions = $countryOptions;
        $this->regionOptions = $regionOptions;
        $this->registry = $registry;
        $this->scopeConfig = $context->getScopeConfig();
        $this->cart = $cart;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return $this
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


    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    public function getStoreUrl()
    {
        return $this->getUrl('storepickup/index/index', ['productid' => $this->getCurrentProduct()->getId()]);
    }

    public function isModuleEnabled()
    {
        return $this->dataHelper->isModuleEnabled();
    }
    /**
     *
     * @return Array
     */
    public function getStorelist()
    {
        
        $id = $this->getRequest()->getParam('productid');
        
        $md_store_id = $this->_storeManager->getStore()->getId();
        $storelocatorModel = $this->_modelstorelocatorFactory->create();
        $storelocatorCollection = $storelocatorModel->getCollection();
        $storelocatorCollection->addFieldToFilter('is_active', 1)
                                ->addFieldToFilter('store_id', [0,$md_store_id]);
        if ((count($storelocatorCollection->getData())>0)) {
            $selectedProductids = [];
            foreach ($storelocatorCollection as $store) {
                $parent_id = $store->getStoreParentId();
                $productIds = explode(',', $store->getProductIds());
                if ($parent_id) {
                    $slocatorModel= $this->_modelstorelocatorFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('is_active', '1')
                        ->addFieldToFilter('store_parent_id', $parent_id)
                        ->addFieldToFilter('store_id', [$md_store_id])
                        ->getFirstItem();
                    $slocator_id = $slocatorModel->getStorelocatorId();
                    if ($slocator_id) {
                        if (!empty($id) && in_array($id, $productIds)) {
                            $selectedProductids[] = $slocator_id;
                        } else {
                            if (empty($id)) {
                                $selectedProductids[] = $slocator_id;
                            }
                        }
                    } else {
                        if (!empty($id) && in_array($id, $productIds)) {
                            $selectedProductids[] = $parent_id;
                        } else {
                            if (empty($id)) {
                                $selectedProductids[] = $parent_id;
                            }
                        }
                    }
                }
            }
            $selProductids = array_unique($selectedProductids);
        }
        if (!empty($selProductids)) {
              $storelocatorCollection->addFieldToFilter('storelocator_id', [$selProductids])
                    ->addFieldToFilter('is_active', 1);
                    $storelocatorData = $storelocatorCollection->getData();
        } else {
            $storelocatorData = null;
        }

        return $storelocatorData;
    }

    public function getAvailabelStoreForCurrentProduct()
    {
        
        $id = $this->getCurrentProduct()->getId();
        if ($id) {
            $md_store_id = $this->_storeManager->getStore()->getId();
            $storelocatorModel = $this->_modelstorelocatorFactory->create();
            $storelocatorCollection =
                    $storelocatorModel->getCollection()
                                      ->addFieldToFilter('is_active', 1)
                                      ->addFieldToFilter('store_id', [0,$md_store_id]);
            $selectedProductids = [];
            
            if ((count($storelocatorCollection->getData())>0)) {
                $selectedProductids = [];
                foreach ($storelocatorCollection as $store) {
                    $parent_id = $store->getStoreParentId();
                    $productIds = explode(',', $store->getProductIds());
                    if ($parent_id) {
                        $slocatorModel= $this->_modelstorelocatorFactory->create()
                            ->getCollection()
                            ->addFieldToFilter('is_active', '1')
                            ->addFieldToFilter('store_parent_id', $parent_id)
                            ->addFieldToFilter('store_id', [$md_store_id])
                            ->getFirstItem();
                        $slocator_id = $slocatorModel->getStorelocatorId();
                       
                        if ($slocator_id) {
                            if (in_array($id, $productIds)) {
                                $selectedProductids[] = $slocator_id;
                            }
                        } else {
                            if (in_array($id, $productIds)) {
                                $selectedProductids[] = $parent_id;
                            }
                        }
                    }
                }
                $selProductids = array_unique($selectedProductids);
            }
            if (!empty($selProductids)) {
                    $storelocatorCollection->addFieldToFilter('storelocator_id', $selProductids)
                        ->addFieldToFilter('is_active', 1);
                    $storelocatorData = $storelocatorCollection->getData();
            } else {
                $storelocatorData = null;
            }
            

            //Ends here
            
            return $storelocatorData;
        }
    }


    public function getTaglist()
    {
        $tag = $this->_tagFactory->create();
        $md_store_id = $this->_storeManager->getStore()->getId();
        $tagCollection = $tag->getCollection();
        $tagCollection->addFieldToFilter('is_active', 1)
                      ->addFieldToFilter('store_ids', ['neq' => null])
                      ->addFieldToFilter('store_id', [0,$md_store_id]);
        
        if ((count($tagCollection->getData())>0)) {
            $selectedtagids = [];
            foreach ($tagCollection as $storetags) {
                $parent_id = $storetags->getTagParentId();
                
                if ($parent_id) {
                    $stagModel= $this->_tagFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('is_active', '1')
                        ->addFieldToFilter('store_ids', ['neq' => null])
                        ->addFieldToFilter('tag_parent_id', $parent_id)
                        ->addFieldToFilter('store_id', [$md_store_id])
                        ->getFirstItem();
                    $stag_id = $stagModel->getTagId();
                    if ($stag_id) {
                        $selectedTagids[] = $stag_id;
                    } else {
                        $selectedTagids[] = $parent_id;
                    }
                }
            }
            
            $Tagids = array_unique($selectedTagids);
            $tagCollection->addFieldToFilter('tag_id', [$Tagids]);
        }
        $tag_collection = $tagCollection->getData();
        return $tag_collection;
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
     * @param int $storelocatorid
     * @return string
     */
    public function getStoreImage($storelocatorid)
    {
        $storelocatorModel = $this->_modelstorelocatorFactory->create();
        $storelocatorModel->load($storelocatorid);
        $storeimage = $storelocatorModel->getStoreimage();
        //$storename = $storelocatorModel->getStorename();

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

    public function getTagIcon($tagIcon)
    {
        if ($tagIcon != '') {
            $imagehtml = $this->imageModel->getBaseUrl() . $tagIcon;
            return $newimage  = $this->imageResizerHelper->resize($imagehtml, 60, 60);
        }
        return '';
    }

    /**
     *
     * @return string
     */
    public function getStoreTitle()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $_config_Title = $this->scopeConfig->getValue(self::META_Title_CONFIG_PATH, $storeScope);

        if ($_config_Title != '') {
            return $_config_Title;
        } else {
            return 'Storelocator';
        }
    }

    /**
     *
     * @param int $storelocatorid
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
            return $html;
        }
        return;
    }

    /**
     *
     * @return type string
     */
    public function getDistanceUnit()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $_config_DistanceUnit = $this->scopeConfig->getValue(self::XML_PATH_STORE_DISTANCE, $storeScope);
        return $_config_DistanceUnit;
    }
    
    /**
     *
     * @return Int
     */
    public function getDefaultRadious()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $_config_Radious = $this->scopeConfig->getValue(self::XML_PATH_STORE_RADIOUS, $storeScope);
        if (empty($_config_Radious)) {
            $_config_Radious = 10;
        }
        return $_config_Radious;
    }
    
    /**
     *
     * @return int
     */
    public function getMaxRadious()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $_config_Radious = $this->scopeConfig->getValue(self::XML_PATH_STORE_MAXRADIOUS, $storeScope);
        if (empty($_config_Radious)) {
            $_config_Radious = 100;
        }
        return $_config_Radious;
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

    public function IsEnableCurrentLocation()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $_Enable_Current_Location = $this->scopeConfig->getValue(self::ENABLE_CURRENT_LOCATION, $storeScope);
        return $_Enable_Current_Location;
    }
    
    /**
     *
     * @param string $urlKey
     * @return string
     */
    public function getStoreUrlFromKey($urlKey)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $suffix = trim($this->scopeConfig->getValue('magedelight_storepickup/listviewinfo/listpage_suffix', $storeScope), '/');
        $urlKey = $this->getUrl($urlKey);
        $urlKey = rtrim($urlKey, "/");
        $urlKey .= (strlen($suffix) > 0 || $suffix != '') ? '.' . str_replace('.', '', $suffix) : '';
        return $urlKey;
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
        } else {
            return $new_marker_image_Url  = $this->imageResizerHelper->resize($_marker_image_Url, 40, 60);
        }
    }

    /**
     *
     * @return boolean
     */
    public function getIsTimesloatEnabel()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $_Time_sloat = $this->scopeConfig->getValue(self::XML_PATH_STORE_TIME, $storeScope);
        return $_Time_sloat;
    }

    /**
     *
     * @return array
     */
    public function getCheckoutQuoteForStore()
    {
        return $this->cart->getQuote()->getData();
    }
    
    public function getQuoteId()
    {
        return $this->cart->getQuote()->getId();
    }
    public function getPickupStore()
    {
        if($this->cart->getQuote()->getPickupStore()){
            $pickupStore = $this->cart->getQuote()->getPickupStore();
        }else{
            $pickupStore = false;
        }
        return $pickupStore;
    }
    public function getPickupDate()
    {
        if($this->cart->getQuote()->getPickupDate()){
            $pickupDate = date("Y-m-d", strtotime($this->cart->getQuote()->getPickupDate()));
        }else{
            $pickupDate =  false;
        }
        return $pickupDate;
    }
    public function getPickupTime()
    {
        if($this->cart->getQuote()->getPickupDate()){
            $pickupTime = date("H:i", strtotime($this->cart->getQuote()->getPickupDate()));
        }else{
            $pickupTime =  false;
        }
        return $pickupTime;
        
    }
    /**
     *
     * @param int $storeId
     * @return json array
     */
    public function getStoreAdress($storeId)
    {
        $fields = ['storename', 'address', 'city', 'state', 'country', 'zipcode', 'telephone'];
        $storelocatorModel = $this->_modelstorelocatorFactory->create();
        $storelocatorCollection = $storelocatorModel->getCollection();
        $storelocatorCollection->addFieldToFilter('storelocator_id', $storeId);
        $storelocatorCollection->addFieldToSelect($fields);
        $storelocatorData = $storelocatorCollection->getData();

        return json_encode($storelocatorData);
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

    public function paginate_function($item_per_page, $current_page, $total_records, $total_pages)
    {
        $pagination = '';
        if ($total_pages > 0 && $total_pages != 1 && $current_page <= $total_pages) { //verify total pages and current page number
            $pagination .= '<ul class="pagination">';
            
            $right_links    = $current_page + 3;
            $previous       = $current_page - 3; //previous link
            $next           = $current_page + 1; //next link
            $first_link     = true; //boolean var to decide our first link
            
            if ($current_page > 1) {
                $previous_link = ($previous==0)? 1: $previous;
                $pagination .= '<li class="first"><a href="#" data-page="1" title="First">&laquo;</a></li>'; //first link
                $pagination .= '<li><a href="#" data-page="'.$previous_link.'" title="Previous">&lt;</a></li>'; //previous link
                for ($i = ($current_page-2); $i < $current_page; $i++) { //Create left-hand side links
                    if ($i > 0) {
                        $pagination .= '<li><a href="#" data-page="'.$i.'" title="Page'.$i.'">'.$i.'</a></li>';
                    }
                }
                $first_link = false; //set first link to false
            }
            
            if ($first_link) { //if current active page is first link
                $pagination .= '<li class="first active">'.$current_page.'</li>';
            } elseif ($current_page == $total_pages) { //if it's the last active link
                $pagination .= '<li class="last active">'.$current_page.'</li>';
            } else { //regular current link
                $pagination .= '<li class="active">'.$current_page.'</li>';
            }
                    
            for ($i = $current_page+1; $i < $right_links; $i++) { //create right-hand side links
                if ($i<=$total_pages) {
                    $pagination .= '<li><a href="#" data-page="'.$i.'" title="Page '.$i.'">'.$i.'</a></li>';
                }
            }
            if ($current_page < $total_pages) {
                    $next_link = ($i > $total_pages) ? $total_pages : $i;
                    $pagination .= '<li><a href="#" data-page="'.$next_link.'" title="Next">&gt;</a></li>'; //next link
                    $pagination .= '<li class="last"><a href="#" data-page="'.$total_pages.'" title="Last">&raquo;</a></li>'; //last link
            }
            
            $pagination .= '</ul>';
        }
        return $pagination; //return pagination links
    }

    public function getClusterImage()
    {
        return $mcOptions=  [
                 [
                    "height" => 53,
                    "url" => $this->getViewFileUrl('Magedelight_Storepickup::images/m1.png'),
                    "width" => 53
                 ],
                 [
                    "height" => 56,
                    "url" => $this->getViewFileUrl('Magedelight_Storepickup::images/m2.png'),
                    "width" => 56
                 ],
                 [
                    "height" => 66,
                    "url" => $this->getViewFileUrl('Magedelight_Storepickup::images/m3.png'),
                    "width" => 66
                 ],
                 [
                    "height" => 78,
                    "url" => $this->getViewFileUrl('Magedelight_Storepickup::images/m4.png'),
                    "width" => 78
                 ],
                 [
                    "height" => 90,
                    "url" => $this->getViewFileUrl('Magedelight_Storepickup::images/m5.png'),
                    "width" => 90
                 ]
            ];
    }
}
