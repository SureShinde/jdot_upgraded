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

namespace Magedelight\Storepickup\Block\Adminhtml\Storelocator;

use Magento\Backend\Block\Widget\Form\Container as FormContainer;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magedelight\Storepickup\Model\Storelocator\Image as ImageModel;
use Magento\Store\Model\ScopeInterface;

class Edit extends FormContainer
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    protected $scopeConfig;
    
    /**
     * @var \Magedelight\Storepickup\Helper\Data
     */
    protected $imageResizerHelper;

    /**
     * image model
     *
     * @var \Magedelight\Storepickup\Model\Storelocator\Image
     */
    protected $imageModel;

    /* Map Marker Image*/
     const XML_PATH_STORE_MARKER_IMAGE ='magedelight_storepickup/googlemap/markericon';

     /* Google Map Api Key */
    const XML_PATH_STORE_MAP_API_KEY = 'magedelight_storepickup/googlemap/mapapi';

    /**
     * constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magedelight\Storepickup\Helper\imageResizer $imageResizerHelper,
        Context $context,
        Registry $registry,
        ImageModel $imageModel,
        array $data = []
    ) {
        $this->imageResizerHelper = $imageResizerHelper;
        $this->coreRegistry = $registry;
        $this->imageModel = $imageModel;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);
    }

    /**
     * Initialize storelocator edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Magedelight_Storepickup';
        $this->_controller = 'adminhtml_storelocator';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Store'));
        // $this->buttonList->update('delete', 'label', __('Delete Block'));
        $this->buttonList->add(
            'saveandcontinue',
            [
            'label' => __('Save and Continue Edit'),
            'class' => 'save',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']]
                ]
                ],
            -100
        );

        $this->buttonList->update('delete', 'label', __('Delete Store'));
    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $storelocator = $this->coreRegistry->registry('magedelight_storelocator_storelocator');
        if ($storelocator->getId()) {
            return __("Edit Item '%1'", $this->escapeHtml($storelocator->getTitle()));
        } else {
            return __('New Storelocator');
        }
    }

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
     * @return String
     */
    public function getGoogleMapApiKey()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $_Map_Api_Key = $this->scopeConfig->getValue(self::XML_PATH_STORE_MAP_API_KEY, $storeScope);
        return $_Map_Api_Key;
    }
}
