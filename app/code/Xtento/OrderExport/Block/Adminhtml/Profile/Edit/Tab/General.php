<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2016-04-18T18:22:12+00:00
 * File:          app/code/Xtento/OrderExport/Block/Adminhtml/Profile/Edit/Tab/General.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Block\Adminhtml\Profile\Edit\Tab;

class General extends \Xtento\OrderExport\Block\Adminhtml\Widget\Tab implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $yesNo;

    /**
     * @var \Xtento\OrderExport\Model\System\Config\Source\Export\Entity
     */
    protected $exportEntity;

    /**
     * General constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Config\Model\Config\Source\Yesno $yesNo
     * @param \Xtento\OrderExport\Model\System\Config\Source\Export\Entity $exportEntity
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\Yesno $yesNo,
        \Xtento\OrderExport\Model\System\Config\Source\Export\Entity $exportEntity,
        array $data = []
    ) {
        $this->yesNo = $yesNo;
        $this->exportEntity = $exportEntity;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function getFormMessages()
    {
        $formMessages = array();
        $model = $this->_coreRegistry->registry('orderexport_profile');
        if ($model->getId() && !$model->getEnabled()) {
            $formMessages[] = array('type' => 'warning', 'message' => __('This profile is disabled. No automatic exports will be made and the profile won\'t show up for manual exports.'));
        }
        return $formMessages;
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('orderexport_profile');
        // Set default values
        if (!$model->getId()) {
            $model->setEnabled(1);
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('General Configuration'),
            ]
        );

        if ($model->getId()) {
            $fieldset->addField(
                'profile_id',
                'hidden',
                [
                    'name' => 'profile_id',
                ]
            );
        }

        $fieldset->addField(
            'name',
            'text',
            [
                'label' => __('Name'),
                'name' => 'name',
                'required' => true,
            ]
        );

        if ($model->getId()) {
            $fieldset->addField(
                'enabled',
                'select',
                [
                    'label' => __('Enabled'),
                    'name' => 'enabled',
                    'values' => $this->yesNo->toOptionArray()
                ]
            );
        }

        $entity = $fieldset->addField(
            'entity',
            'select',
            [
                'label' => __('Export Type'),
                'name' => 'entity',
                'options' => $this->exportEntity->toOptionArray(),
                'required' => true,
                'note' => __(
                    'This setting can\'t be changed after creating the profile. Add a new profile for different export types.'
                )
            ]
        );

        if ($model->getId()) {
            $entity->setDisabled(true);
        }

        if (!$this->_coreRegistry->registry('orderexport_profile') || !$this->_coreRegistry->registry(
            'orderexport_profile'
        )->getId()
        ) {
            $fieldset->addField(
                'continue_button',
                'note',
                [
                    'text' => $this->getChildHtml('continue_button'),
                ]
            );
        }

        if ($this->_coreRegistry->registry('orderexport_profile') && $this->_coreRegistry->registry(
            'orderexport_profile'
        )->getId()
        ) {
            $fieldset = $form->addFieldset(
                'advanced_fieldset',
                [
                    'legend' => __('Export Settings'),
                    'class' => 'fieldset-wide',
                ]
            );

            $fieldset->addField(
                'save_files_local_copy',
                'select',
                [
                    'label' => __('Save local copies of exports'),
                    'name' => 'save_files_local_copy',
                    'values' => $this->yesNo->toOptionArray(),
                    'note' => __(
                        'If set to yes, local copies of the exported files will be saved in the ./var/export_bkp/ folder. If set to no, you won\'t be able to download old export files from the export/execution log.'
                    )
                ]
            );

            $fieldset->addField(
                'export_one_file_per_object',
                'select',
                [
                    'label' => __(
                        'Export each %1 separately',
                        $this->_coreRegistry->registry('orderexport_profile')->getEntity()
                    ),
                    'name' => 'export_one_file_per_object',
                    'values' => $this->yesNo->toOptionArray(),
                    'note' => __(
                        'If set to yes, each %1 exported would be saved in a separate file. This means, for every %2 you export, one file will be created, with just the one %3 in there. If set to no, one file will be created with all the exported %4s in there.',
                        $this->_coreRegistry->registry('orderexport_profile')->getEntity(),
                        $this->_coreRegistry->registry('orderexport_profile')->getEntity(),
                        $this->_coreRegistry->registry('orderexport_profile')->getEntity(),
                        $this->_coreRegistry->registry('orderexport_profile')->getEntity()
                    )
                ]
            );

            $fieldset->addField(
                'export_empty_files',
                'select',
                [
                    'label' => __('Export empty files'),
                    'name' => 'export_empty_files',
                    'values' => $this->yesNo->toOptionArray(),
                    'note' => __(
                        'If set to yes, every export will create a file. Even if 0 %1s have been exported, an empty export file will be created.',
                        $this->_coreRegistry->registry('orderexport_profile')->getEntity()
                    )
                ]
            );
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        $this->setChild(
            'continue_button',
            $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                ->setData(
                [
                    'label' => __('Continue'),
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ],
                    'class' => 'save'
                ]
            )
        );
        return parent::_prepareLayout();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('General Configuration');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('General Configuration');
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