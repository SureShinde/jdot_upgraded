<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

// @codingStandardsIgnoreFile

namespace Infomodus\Dhllabel\Block\Adminhtml\Items\Edit\Tab;


use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;


class Main extends Generic implements TabInterface
{
    protected $dhlmethod;
    protected $dhlpackagecode;
    protected $defaultaddress;
    protected $allCurrency;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Infomodus\Dhllabel\Model\Config\Dhlmethod $dhlmethod,
        \Infomodus\Dhllabel\Model\Config\Dhlpackagecode $dhlpackagecode,
        \Infomodus\Dhllabel\Model\Config\Defaultaddress $defaultaddress,
        \Magento\Config\Model\Config\Source\Locale\Currency\All $allCurrency,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->dhlmethod = $dhlmethod;
        $this->dhlpackagecode = $dhlpackagecode;
        $this->defaultaddress = $defaultaddress;
        $this->allCurrency = $allCurrency;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Main options');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Main options');
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
        $htmlIdPrefix = 'item_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $confParams = $model['handy']->defConfParams;
        $fieldset = $form->addFieldset('configuration_fieldset', ['legend' => __('Configuration options')]);
        $fieldset->addField(
            'dhlaccount',
            'select',
            ['name' => 'dhlaccount',
                'label' => __('Who pay for Shipment?'),
                'title' => __('Who pay for Shipment?'),
                'required' => true,
                'options' => $model['handy']->dhlAccounts,
                'value' => $confParams['dhlaccount'],
            ]
        );
        $fieldset->addField(
            'dhlaccount_duty',
            'select',
            ['name' => 'dhlaccount_duty',
                'label' => __('Who pay for Duty and Taxes?'),
                'title' => __('Who pay for Duty and Taxes?'),
                'required' => true,
                'options' => $model['handy']->dhlAccountsDuty,
                'value' => $confParams['dhlaccount_duty'],
            ]
        );
        if ($model['handy']->type == 'shipment') {
            $fieldset->addField(
                'serviceCode',
                'select',
                ['name' => 'serviceCode',
                    'label' => __('DHL shipping method'),
                    'title' => __('DHL shipping method'),
                    'required' => true,
                    'options' => $this->dhlmethod->getMethodsByRequest(json_decode($confParams['shipping_methods'], true)),
                    'value' => $confParams['serviceCode'],
                ]
            );
            $fieldset->addField(
                'shipping_methods',
                'hidden',
                ['name' => 'shipping_methods',
                    'required' => true,
                    'value' => $confParams['shipping_methods'],
                ]
            );
        } else {
            $fieldset->addField(
                'serviceCode',
                'select',
                ['name' => 'serviceCode',
                    'label' => __('DHL shipping method'),
                    'title' => __('DHL shipping method'),
                    'required' => true,
                    'options' => $this->dhlmethod->getMethodsByRequest(json_decode($confParams['return_methods'], true)),
                    'value' => $confParams['default_return_servicecode'],
                ]
            );
            $fieldset->addField(
                'shipping_methods',
                'hidden',
                ['name' => 'shipping_methods',
                    'required' => true,
                    'value' => $confParams['return_methods'],
                ]
            );
        }

        $fieldset->addField(
            'shipper_no',
            'select',
            ['name' => 'shipper_no',
                'label' => __('Shipper address'),
                'title' => __('Shipper address'),
                'required' => true,
                'options' => $this->defaultaddress->getAddresses(),
                'value' => $confParams['shipper_no'],
            ]
        );
        $fieldset->addField(
            'testing',
            'select',
            ['name' => 'testing',
                'label' => __('Test mode'),
                'title' => __('Test mode'),
                'required' => true,
                'options' => [__('No'), __('Yes')],
                'value' => $confParams['testing'],
            ]
        );
        if ($model['handy']->type == 'shipment' || $model['handy']->type == 'invert') {
            $fieldset->addField(
                'addtrack',
                'select',
                ['name' => 'addtrack',
                    'label' => __('Add tracking number automatically ?'),
                    'title' => __('Add tracking number automatically ?'),
                    'required' => false,
                    'options' => [__('No'), __('Yes')],
                    'value' => $confParams['addtrack'],
                ]
            );
        }
        $fieldset->addField(
            'shipmentdescription',
            'textarea',
            ['name' => 'shipmentdescription',
                'label' => __('Shipment Description'),
                'title' => __('Shipment Description'),
                'required' => false,
                'value' => $confParams['shipmentdescription'],
            ]
        );
        $fieldset->addField(
            'currencycode',
            'select',
            ['name' => 'currencycode',
                'label' => __('Currency code'),
                'title' => __('Currency code'),
                'required' => false,
                'values' => $this->allCurrency->toOptionArray(),
                'value' => $confParams['currencycode'],
            ]
        );
        if ($model['handy']->type != 'refund') {
            $fieldset->addField(
                'cod',
                'select',
                ['name' => 'cod',
                    'label' => __('COD'),
                    'title' => __('COD'),
                    'required' => false,
                    'options' => [__('No'), __('Yes')],
                    'value' => $confParams['cod'],
                ]
            );
        }
        $fieldset->addField(
            'codmonetaryvalue',
            'text',
            ['name' => 'codmonetaryvalue',
                'label' => __('Monetary value'),
                'title' => __('Monetary value'),
                'required' => false,
                'value' => round($confParams['codmonetaryvalue'], 2),
            ]
        );
        $fieldset->addField(
            'insured_monetary_value',
            'text',
            ['name' => 'insured_monetary_value',
                'label' => __('Insured Monetary value'),
                'title' => __('Insured Monetary value'),
                'required' => false,
                'value' => round($confParams['insured_monetary_value'], 2),
            ]
        );
        if ($model['handy']->type == 'shipment'/* && $model['handy']->shipment_id !== null*/) {
            $fieldset->addField(
                'default_return',
                'select',
                ['name' => 'default_return',
                    'label' => __('Create return label now'),
                    'title' => __('Create return label now'),
                    'required' => false,
                    'options' => [__('No'), __('Yes')],
                    'value' => $confParams['default_return'],
                ]
            );
            $fieldset->addField(
                'default_return_servicecode',
                'select',
                ['name' => 'default_return_servicecode',
                    'label' => __('DHL shipping method for return label'),
                    'title' => __('DHL shipping method for return label'),
                    'required' => false,
                    'options' => $this->dhlmethod->getMethodsByRequest(json_decode($confParams['return_methods'], true)),
                    'value' => $confParams['default_return_servicecode'],
                ],
                'default_return'
            );
            $fieldset->addField(
                'return_methods',
                'hidden',
                ['name' => 'return_methods',
                    'required' => true,
                    'value' => $confParams['return_methods'],
                ]
            );
            $this->setChild(
                'form_after',
                $this->getLayout()->createBlock(
                    'Magento\Backend\Block\Widget\Form\Element\Dependence'
                )->addFieldMap(
                    "{$htmlIdPrefix}default_return",
                    'default_return'
                )->addFieldMap(
                    "{$htmlIdPrefix}default_return_servicecode",
                    'default_return_servicecode'
                )->addFieldDependence(
                    'default_return_servicecode',
                    'default_return',
                    '1'
                )
            );
        }
        $fieldset->addField(
            'qvn',
            'select',
            ['name' => 'qvn',
                'label' => __('Notification'),
                'title' => __('Notification'),
                'required' => false,
                'options' => [__('No'), __('Yes')],
                'value' => $confParams['qvn'],
            ],
            'to'
        );
        $fieldset->addField(
            'qvn_message',
            'textarea',
            ['name' => 'qvn_message',
                'label' => __('Notification Message'),
                'title' => __('Notification Message'),
                'required' => false,
                'value' => $confParams['qvn_message'],
            ],
            'qvn'
        );
        $fieldset->addField(
            'qvn_email_shipper',
            'text',
            ['name' => 'qvn_email_shipper',
                'label' => __('Shipper Email'),
                'title' => __('Shipper Email'),
                'required' => false,
                'value' => $confParams['qvn_email_shipper'],
            ]
        );
        $fieldset->addField(
            'packagingtypecode',
            'select',
            ['name' => 'packagingtypecode',
                'label' => __('Packaging Type'),
                'title' => __('Packaging Type'),
                'required' => false,
                'values' => $this->dhlpackagecode->toOptionArray(),
                'value' => $confParams['packagingtypecode'],
            ]
        );
        $fieldset->addField(
            'reference_id',
            'text',
            ['name' => 'reference_id',
                'label' => __('Reference ID'),
                'title' => __('Reference ID'),
                'required' => false,
                'value' => $confParams['reference_id'],
            ]
        );
        $fieldset->addField(
            'declared_value',
            'text',
            ['name' => 'declared_value',
                'label' => __('Declared Value'),
                'title' => __('Declared Value'),
                'required' => false,
                'value' => round($confParams['declared_value'], 2),
            ]
        );
        if (isset($confParams['invoice_declared_value'])) {
            $fieldset->addField(
                'invoice_declared_value',
                'hidden',
                ['name' => 'invoice_declared_value',
                    'value' => round($confParams['invoice_declared_value'], 2),
                ]
            );
        }

        /*$form->setValues($model->getData());*/
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
