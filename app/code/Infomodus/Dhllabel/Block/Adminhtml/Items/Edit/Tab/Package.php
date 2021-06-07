<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

// @codingStandardsIgnoreFile

namespace Infomodus\Dhllabel\Block\Adminhtml\Items\Edit\Tab;


use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;


class Package extends Generic implements TabInterface
{
    /**
     * @var \Infomodus\Dhllabel\Model\Config\Boxes
     */
    protected $boxes;

    public function __construct(\Magento\Backend\Block\Template\Context $context,
                                \Magento\Framework\Registry $registry,
                                \Magento\Framework\Data\FormFactory $formFactory,
                                \Infomodus\Dhllabel\Model\Config\Boxes $boxes,
                                array $data = [])
    {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->boxes = $boxes;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Package Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Package Information');
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
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_infomodus_dhllabel_items');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('item_');
        if(!is_array($model['handy']->defPackageParams) || count($model['handy']->defPackageParams) == 0){
            $model['handy']->defPackageParams = [[]];
        }
        foreach ($model['handy']->defPackageParams as $key => $package) {
            $fieldset = $form->addFieldset('package_fieldset_' . $key . '_', ['legend' => __('Package') . ' '. ($key + 1)]);
            $fieldset->addField(
                'weight_' . $key . '_',
                'text',
                ['name' => 'package[weight][]',
                    'label' => __('Weight'),
                    'title' => __('Weight'),
                    'required' => true,
                    'value' => isset($package['weight'])?$package['weight']:null,
                ]
            );
            $fieldset->addField(
                'packweight_' . $key . '_',
                'text',
                ['name' => 'package[packweight][]',
                    'label' => __('Pack weight'),
                    'title' => __('Pack weight'),
                    'required' => false,
                    'value' => isset($package['packweight'])?$package['packweight']:null,
                    'class' => 'box-packweight'
                ]
            );
            $fieldset->addField(
                'default_box_' . $key . '_',
                'select',
                ['name' => 'package[box][]',
                    'label' => __('Box'),
                    'title' => __('Box'),
                    'required' => false,
                    'values' => $this->boxes->toOptionArray(),
                    'value' => isset($package['box']) ? $package['box'] : null,
                    'class' => 'box-selected'
                ]
            );
            $fieldset->addField(
                'depth_' . $key . '_',
                'text',
                ['name' => 'package[depth][]',
                    'label' => __('Depth'),
                    'title' => __('Depth'),
                    'required' => true,
                    'value' => isset($package['length'])?$package['length']:null,
                    'class' => 'box-length'
                ]
            );
            $fieldset->addField(
                'width_' . $key . '_',
                'text',
                ['name' => 'package[width][]',
                    'label' => __('Width'),
                    'title' => __('Width'),
                    'required' => true,
                    'value' => isset($package['width'])?$package['width']:null,
                    'class' => 'box-width'
                ]
            );
            $fieldset->addField(
                'height_' . $key . '_',
                'text',
                ['name' => 'package[height][]',
                    'label' => __('Height'),
                    'title' => __('Height'),
                    'required' => true,
                    'value' => isset($package['height'])?$package['height']:null,
                    'class' => 'box-height'
                ]
            );
        }
        /*$form->setValues($model->getData());*/
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
