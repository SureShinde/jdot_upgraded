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

namespace Magedelight\Storepickup\Block\Adminhtml\Storeholiday\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magedelight\Storepickup\Model\Source\Country;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magedelight\Storepickup\Helper\Data as Storehelper;

class Storeholiday extends GenericForm implements TabInterface
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
        Country $countryOptions,
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
        $this->countryOptions = $countryOptions;
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
        $storeholiday = $this->_coreRegistry->registry('magedelight_storelocator_storeholiday');
        $switchedstoreid = $this->getRequest()->getParam('store');
        $default_store_id = $this->storeHelper->_getDefaultStoreId();
        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $data = $storeholiday->getData();
        $form->setHtmlIdPrefix('storeholiday_');
        $form->setFieldNameSuffix('storeholiday');
     
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
            'legend' => __('Holiday Information'),
            'class' => 'fieldset-wide'
                ]
        );

        if ($storeholiday->getId()) {
            $fieldset->addField(
                'holiday_id',
                'hidden',
                [
                'name' => 'holiday_id'
                    ]
            );
            $fieldset->addField(
                'holiday_parent_id',
                'hidden',
                ['name' => 'holiday_parent_id', 'value' => $storeholiday->getHolidayParentId()]
            );
        }

        $fieldset->addField(
            'holiday_name',
            'text',
            [
            'name' => 'holiday_name',
            'label' => __('Holiday Name'),
            'title' => __('Holiday Name'),
            'required' => true,
                ]
        );

        $fieldset->addField(
            'all_store',
            'select',
            [
            'label' => __('Store'),
            'title' => __('Store'),
            'name' => 'all_store',
            'required' => true,
            'options' => ['0' => 'All Store', '1' => 'Select Multiple Store'],
            'selected' => (isset($data['holiday_applied_stores']) == 0) ? 0 : 1,
            'disabled' => $isElementDisabled,
                ],
            'to'
        );

        $fieldset->addField(
            'holiday_applied_stores',
            'multiselect',
            [
            'label' => __('Allow Store'),
            'title' => __('Allow Store'),
            'name' => 'holiday_applied_stores',
            'values' => $storeholiday->getAvailableStores(),
            'required' => true,
            'display' => 'none'
                ],
            'all_store'
        );
        // write this before  this line $this->setForm($form);
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                "storeholiday_all_store",
                'all_store'
            )
                        ->addFieldMap(
                            "storeholiday_holiday_applied_stores",
                            'holiday_applied_stores'
                        )
                        ->addFieldDependence(
                            'holiday_applied_stores',
                            'all_store',
                            '1'
                        )
        );

        $fieldset->addField(
            'is_active',
            'select',
            [
            'label' => __('Status'),
            'title' => __('Status'),
            'name' => 'is_active',
            'required' => true,
            'options' => $storeholiday->getAvailableStatuses(),
            'disabled' => $isElementDisabled,
                ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(
            \IntlDateFormatter::SHORT
        );

        $fieldset->addField(
            'holiday_date_from',
            'date',
            [
            'name' => 'holiday_date_from',
            'label' => __('From Date'),
            'date_format' => $dateFormat,
            'disabled' => $isElementDisabled,
            'class' => 'validate-date validate-date-range date-range-holiday_date-from'
                ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(
            \IntlDateFormatter::SHORT
        );

        $fieldset->addField(
            'holiday_date_to',
            'date',
            [
            'name' => 'holiday_date_to',
            'label' => __('To Date'),
            'date_format' => $dateFormat,
            'disabled' => $isElementDisabled,
            'class' => 'validate-date validate-date-range date-range-holiday_date-to'
                ]
        );
        $fieldset->addField(
            'holiday_comment',
            'editor',
            [
            'label' => __('Comment'),
            'title' => __('Comment'),
            'name' => 'holiday_comment',
            'style' => 'height:12em',
            'config' => $this->wysiwygConfig->getConfig()
                ]
        );

        $fieldset->addField(
            'is_repetitive',
            'checkbox',
            [
            'label' => __('Yearly Repetitive'),
            'title' => __('Yearly Repetitive'),
            'name' => 'is_repetitive',
            'checked' => (isset($data['is_repetitive']) && ($data['is_repetitive']==1)) ? true : false,
            'onclick' => 'this.value = this.checked ? 1 : 0;'
                ]
        );
        if (!empty($switchedstoreid)) {
            $md_storeid = $switchedstoreid;
            $storeholiday->setStoreId($md_storeid);
        } else {
            $md_storeid = $default_store_id;
        }
        
        $fieldset->addField(
            'store_id',
            'hidden',
            ['name' => 'store_id', 'value' => $md_storeid]
        );

        if (!$storeholiday->getId()) {
            $storeholiday->setData('is_active', $isElementDisabled ? '1' : '0');
        }

        if ($storeholiday->getId()) {
            if (isset($data['holiday_applied_stores'])) {
                $storeholiday->setData('all_store', ($data['holiday_applied_stores'] == '0') ? '0' : '1');
            }
        }

        $form->addValues($storeholiday->getData());
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
        return __('Holiday Information');
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
