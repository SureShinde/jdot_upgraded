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

namespace Magedelight\Storepickup\Block\Adminhtml\Storelocator\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magedelight\Storepickup\Model\Source\Country;
//use Magento\Directory\Model\Config\Source\Country;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magedelight\Storepickup\Helper\Data as Storehelper;

class Storelocator extends GenericForm implements TabInterface
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var WysiwygConfig
     */
    protected $wysiwygConfig;

    /**
     *
     * @param Country $countryOptions
     * @param WysiwygConfig $wysiwygConfig
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        Country $countryFactory,
        WysiwygConfig $wysiwygConfig,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = [],
        Storehelper $storeHelper
    ) {
        $this->_systemStore = $systemStore;
        $this->wysiwygConfig = $wysiwygConfig;
        $this->_countryFactory = $countryFactory;
        $this->storeHelper = $storeHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /* @var $model \Magento\Cms\Model\Page */
        $storelocator = $this->_coreRegistry->registry('magedelight_storelocator_storelocator');
       
        $switchedstoreid = $this->getRequest()->getParam('store');
       
        $default_store_id = $this->storeHelper->_getDefaultStoreId();

        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $data = $storelocator->getData();
        $form->setHtmlIdPrefix('storelocator_');
        $form->setFieldNameSuffix('storelocator');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
            'legend' => __('Store Information'),
            'class' => 'fieldset-wide'
                ]
        );
        if ($storelocator->getId()) {
            $fieldset->addField(
                'storelocator_id',
                'hidden',
                ['name' => 'storelocator_id']
            );
            $fieldset->addField(
                'store_parent_id',
                'hidden',
                ['name' => 'store_parent_id', 'value' => $storelocator->getStoreParentId()]
            );
        }

        $fieldset->addField(
            'storename',
            'text',
            [
            'name' => 'storename',
            'label' => __('Store Name'),
            'title' => __('Store Name'),
            'required' => true,
                ]
        );
        $fieldset->addField(
            'storeemail',
            'text',
            [
            'name' => 'storeemail',
            'label' => __('Store Email'),
            'title' => __('Store Email'),
            'required' => true,
                ]
        );
        $fieldset->addField(
            'url_key',
            'text',
            [
            'name' => 'url_key',
            'label' => __('Url Key'),
            'title' => __('Url Key'),
            'required' => false,
            'class' => 'validate-identifier'
                ]
        );

        $fieldset->addField(
            'description',
            'editor',
            [
            'label' => __('Description'),
            'title' => __('Description'),
            'name' => 'description',
            'style' => 'height:12em',
            'config' => $this->wysiwygConfig->getConfig()
                ]
        );

        $fieldset->addField(
            'website_url',
            'text',
            [
            'name' => 'website_url',
            'label' => __('Website Url'),
            'title' => __('Website Url'),
            'class' => 'validate-url',
                ]
        );
        $fieldset->addField(
            'facebook_url',
            'text',
            [
            'name' => 'facebook_url',
            'label' => __('Facebook Url'),
            'title' => __('Facebook Url'),
            'class' => 'validate-url',
                ]
        );
        $fieldset->addField(
            'twitter_url',
            'text',
            [
            'name' => 'twitter_url',
            'label' => __('Twitter Url'),
            'title' => __('Twitter Url'),
            'class' => 'validate-url',
                ]
        );

        $fieldset->addField(
            'is_active',
            'select',
            [
            'label' => __('Status'),
            'title' => __('Status'),
            'name' => 'is_active',
            'required' => true,
            'options' => $storelocator->getAvailableStatuses(),
            'disabled' => $isElementDisabled,
                ]
        );

        $fieldset = $form->addFieldset(
            'address_fieldset',
            [
            'legend' => __('Store Location Information'),
            'class' => 'fieldset-wide',
            'expanded' => false,
                ]
        );
        $fieldset->addType('image', 'Magedelight\Storepickup\Block\Adminhtml\Storelocator\Helper\Image');
        $_addressField = ['name' => 'address',
            'data' => isset($data['address']) ? $data['address'] : '',
            'type' => 'address'
        ];
        $fieldset->addField(
            'address',
            'note',
            [
            'name' => 'address',
            'label' => __('Address'),
            'title' => __('Address'),
            'required' => true,
            'text' => $this->getLayout()->createBlock(
                'Magedelight\Storepickup\Block\Adminhtml\Address'
            )->setData('field', $_addressField)->toHtml(),
                ]
        );
        $fieldset->addField(
            'city',
            'text',
            [
            'name' => 'city',
            'label' => __('City'),
            'title' => __('City'),
            'required' => true,
                ]
        );

        $countryfield = ['name' => 'country',
            'country_id' => isset($data['country']) ? $data['country'] : '',
            'region_id' => isset($data['region_id']) ? $data['region_id'] : '',
        ];
        $fieldset->addField(
            'country_id',
            'note',
            [
            'name' => 'country',
            'label' => __('Country'),
            'title' => __('Country'),
            'required' => true,
            'text' => $this->getLayout()->createBlock(
                'Magedelight\Storepickup\Block\Adminhtml\Country'
            )->setData('field', $countryfield)->toHtml(),
                ]
        );

        $regionfield = ['name' => 'region_id',
            'data' => isset($data['region_id']) ? $data['region_id'] : '',
            'type' => 'region'
        ];
        $fieldset->addField(
            'region_id_container',
            'note',
            [
            'name' => 'region_id',
            'label' => __('Region'),
            'title' => __('Region'),
            'required' => true,
            'text' => $this->getLayout()->createBlock(
                'Magedelight\Storepickup\Block\Adminhtml\Region'
            )->setData('field', $regionfield)->toHtml(),
                ]
        );

        $statefield = ['name' => 'state',
            'state' => isset($data['state']) ? $data['state'] : '',
            'country_id' => isset($data['country']) ? $data['country'] : '',
            'region_id' => isset($data['region_id']) ? $data['region_id'] : '',
            'type' => 'state'
        ];
        $fieldset->addField(
            'state',
            'note',
            [
            'name' => 'country',
            'label' => __('State'),
            'title' => __('State'),
            'text' => $this->getLayout()->createBlock(
                'Magedelight\Storepickup\Block\Adminhtml\State'
            )->setData('field', $statefield)->toHtml(),
                ]
        );
        
        $fieldset->addField(
            'zipcode',
            'text',
            [
            'name' => 'zipcode',
            'label' => __('Zipcode'),
            'title' => __('Zipcode'),
            'required' => true,
            'class' => 'validate-zip',
                ]
        );
        $fieldset->addField(
            'phone_frontend_status',
            'select',
            [
            'name' => 'phone_frontend_status',
            'label' => __('Visible Contacts on Frontend'),
            'title' => __('Visible Contacts on Frontend'),
            'options' => $storelocator->getAvailableStatuses()
                ]
        );
        $field = ['name' => 'telephone',
            'data' => isset($data['telephone']) ? $data['telephone'] : '',
            'type' => 'telephone'
        ];
        $fieldset->addField(
            'telephone',
            'note',
            [
            'name' => 'telephone',
            'label' => __('Telephone'),
            'title' => __('Telephone'),
            'required' => true,
            'text' => $this->getLayout()->createBlock(
                'Magedelight\Storepickup\Block\Adminhtml\Telephone'
            )->setData('field', $field)->toHtml(),
                ]
        );
        $fieldset->addField(
            'telephonestyle',
            'note',
            [
            'text' => $this->getLayout()->createBlock(
                'Magedelight\Storepickup\Block\Adminhtml\Telestyle'
            )->setData('field', $field)->toHtml(),
                ]
        );
        $fieldset->addField(
            'longitude',
            'text',
            [
            'name' => 'longitude',
            'label' => __('Longitude'),
            'title' => __('Longitude'),
            'class' => 'validate-number',
            'required' => true,
                ]
        );
        $fieldset->addField(
            'latitude',
            'text',
            [
            'name' => 'latitude',
            'label' => __('Latitude'),
            'title' => __('Latitude'),
            'class' => 'validate-number',
            'required' => true,
            'wysiwyg' => false,
                ]
        );
        $fieldset->addField(
            'storeimage',
            'image',
            [
            'name' => 'storeimage',
            'label' => __('Store Image'),
            'title' => __('Store Image'),
            'after_element_html' => '<small>Allowed file types: jpg, jpeg, gif, png.</small>'
                ]
        );

        $maphtml = '<p class="note">*Drag & Drop the pin to locate store accurately on map.</p>
                    <div class="mapBlock">
                        <div id="map_canvas" style="height: 473px;">
                        </div>
                    </div>
                    <div id="showinfo">
                    </div>';
        $fieldset->addField('label', 'label', [
            'value' => '',
            'after_element_html' => $maphtml
        ]);
        //Hidden field for store id starts here
        if (!empty($switchedstoreid)) {
            $md_storeid = $switchedstoreid;
            $storelocator->setStoreId($md_storeid);
        } else {
            $md_storeid = $default_store_id;
        }
        
        $fieldset->addField(
            'store_id',
            'hidden',
            ['name' => 'store_id', 'value' => $md_storeid]
        );
        
        //Hidden field for store id ends here.
        if (!$storelocator->getId()) {
            $storelocator->setData('is_active', $isElementDisabled ? '1' : '0');
        }
        
        $form->addValues($storelocator->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Store Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
