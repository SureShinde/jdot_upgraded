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

class Contactinfo extends GenericForm implements TabInterface
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     *
     * @param Country $countryOptions
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        Country $countryOptions,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->countryOptions = $countryOptions;
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


        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $data = $storelocator->getData();
        $form->setHtmlIdPrefix('storelocator_');
        $form->setFieldNameSuffix('storelocator');


        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
            'legend' => __('Contact Information'),
            'class' => 'fieldset-wide'
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

        $form->addValues($storelocator->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     * @return string
     */
    public function getTabLabel()
    {
        return __('Storelocator Information');
    }

    /**
     * Prepare title for tab
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
}
