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

namespace Magedelight\Storepickup\Block\Adminhtml\Tag\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magedelight\Storepickup\Helper\Data as Storehelper;

class Tag extends GenericForm implements TabInterface
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
        $tag = $this->_coreRegistry->registry('magedelight_store_tag');
        $switchedstoreid = $this->getRequest()->getParam('store');
       
        $default_store_id = $this->storeHelper->_getDefaultStoreId();
        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $data = $tag->getData();
        $form->setHtmlIdPrefix('tag_');
        $form->setFieldNameSuffix('tag_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
            'legend' => __('Tag Information'),
            'class' => 'fieldset-wide'
                ]
        );
        if ($tag->getTagId()) {
            $fieldset->addField(
                'tag_id',
                'hidden',
                [
                'name' => 'tag_id'
                    ]
            );
            $fieldset->addField(
                'tag_parent_id',
                'hidden',
                ['name' => 'tag_parent_id', 'value' => $tag->getTagParentId()]
            );
        }

        $fieldset->addField(
            'tag_name',
            'text',
            [
            'name' => 'tag_name',
            'label' => __('Tag Name'),
            'title' => __('Tag Name'),
            'required' => true,
                ]
        );
        
        $fieldset->addField(
            'tag_description',
            'editor',
            [
            'label' => __('Description'),
            'title' => __('Description'),
            'name' => 'tag_description',
            'style' => 'height:12em',
            'config' => $this->wysiwygConfig->getConfig()
                ]
        );

        if ($tag->getTagId()) {
            $imageRequired ="";
            $fieldset->addType('image', 'Magedelight\Storepickup\Block\Adminhtml\Storelocator\Helper\Image');
        } else {
            $imageRequired = "<script type=\"text/javascript\">var d = document.getElementById(\"tag_tag_icon\");d .className += \" required-entry _required\";</script>";
        }
        
        $fieldset->addField(
            'tag_icon',
            'image',
            [
            'name' => 'tag_icon',
            'label' => __('Tag Image'),
            'title' => __('Tag Image'),
            'required' => true,
            'after_element_html' => '<small>Allowed file types: jpg, jpeg, gif, png.</small>',
                ]
        )->setAfterElementHtml($imageRequired);


        $fieldset->addField(
            'is_active',
            'select',
            [
            'label' => __('Status'),
            'title' => __('Status'),
            'name' => 'is_active',
            'required' => true,
            'options' => $tag->getAvailableStatuses(),
            'disabled' => $isElementDisabled,
                ]
        );
        //Hidden field for store id starts here
        if (!empty($switchedstoreid)) {
            $md_storeid = $switchedstoreid;
            $tag->setStoreId($md_storeid);
        } else {
            $md_storeid = $default_store_id;
        }
        
        $fieldset->addField(
            'store_id',
            'hidden',
            ['name' => 'store_id', 'value' => $md_storeid]
        );
        
        //Hidden field for store id ends here.
        if (!$tag->getId()) {
            $tag->setData('is_active', $isElementDisabled ? '1' : '0');
        }

        $form->addValues($tag->getData());
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
        return __('General Information');
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
