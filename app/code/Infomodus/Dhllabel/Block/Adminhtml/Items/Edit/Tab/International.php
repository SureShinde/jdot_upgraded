<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

// @codingStandardsIgnoreFile

namespace Infomodus\Dhllabel\Block\Adminhtml\Items\Edit\Tab;


use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;


class International extends Generic implements TabInterface
{
    protected $termsOfTrade;
    /**
     * @var \Infomodus\Dhllabel\Model\Config\InvoiceType
     */
    private $invoiceType;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Infomodus\Dhllabel\Model\Config\TermsOfTrade $termsOfTrade,
        \Infomodus\Dhllabel\Model\Config\InvoiceType $invoiceType,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->termsOfTrade = $termsOfTrade;
        $this->invoiceType = $invoiceType;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('International Invoice');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('International Invoice');
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
        $fieldset = $form->addFieldset('international_fieldset', ['legend' => __('Configuration')]);

        $fieldset->addField(
            'terms_of_trade',
            'select',
            ['name' => 'terms_of_trade',
                'label' => __('Terms of trade'),
                'title' => __('Terms of trade'),
                'values' => $this->termsOfTrade->toOptionArray(),
                'value' => $confParams['terms_of_trade'],
            ]
        );
        $fieldset->addField(
            'invoice_type',
            'select',
            ['name' => 'invoice_type',
                'label' => __('Invoice Type'),
                'title' => __('Invoice Type'),
                'values' => $this->invoiceType->toOptionArray(),
                'value' => $confParams['invoice_type'],
            ]
        );
        $fieldset->addField(
            'shipper_vat_number',
            'text',
            ['name' => 'shipper_vat_number',
                'label' => __('Tax/VAT Number'),
                'title' => __('Tax/VAT Number'),
                'value' => $confParams['shipper_vat_number'],
            ]
        );
        $fieldset->addField(
            'eori_number',
            'text',
            ['name' => 'eori_number',
                'label' => __('EORI Number'),
                'title' => __('EORI Number'),
                'value' => $confParams['eori_number'],
            ]
        );
        $fieldset->addField(
            'place_of_incoterm',
            'text',
            ['name' => 'place_of_incoterm',
                'label' => __('Place Of Incoterm'),
                'title' => __('Place Of Incoterm'),
                'value' => $confParams['place_of_incoterm'],
            ]
        );

        $dependenceBlock = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence');

        if (isset($confParams['invoice_product']) && count($confParams['invoice_product']) > 0) {
            foreach ($confParams['invoice_product'] as $key => $product) {
                $fieldsetProducts = $form->addFieldset('invoice_product_fieldset' . $key, ['legend' => __('Product') . (" " . ($key + 1))]);
                $fieldsetProducts->addField(
                    'invoice_product-enabled' . $key,
                    'select',
                    ['name' => 'invoice_product[' . $key . '][enabled]',
                        'label' => __('Enabled'),
                        'title' => __('Enabled'),
                        'required' => true,
                        'options' => [__('No'), __('Yes')],
                        'value' => 1,
                    ]
                );
                $fieldsetProducts->addField(
                    'invoice_product-name_' . $key,
                    'text',
                    ['name' => 'invoice_product[' . $key . '][name]',
                        'label' => __('Name'),
                        'title' => __('Name'),
                        'required' => false,
                        'value' => $product['name'],
                    ]
                );
                $fieldsetProducts->addField(
                    'invoice_product-price' . $key,
                    'text',
                    ['name' => 'invoice_product[' . $key . '][price]',
                        'label' => __('Price'),
                        'title' => __('Price'),
                        'required' => false,
                        'value' => round($product['price'], 2),
                    ]
                );
                $fieldsetProducts->addField(
                    'invoice_product-qty' . $key,
                    'text',
                    ['name' => 'invoice_product[' . $key . '][qty]',
                        'label' => __('Quantity'),
                        'title' => __('Quantity'),
                        'required' => false,
                        'value' => round($product['qty'], 2),
                    ]
                );
                $fieldsetProducts->addField(
                    'invoice_product-weight' . $key,
                    'hidden',
                    ['name' => 'invoice_product[' . $key . '][weight]',
                        'required' => false,
                        'value' => $product['weight'],
                    ]
                );
                $fieldsetProducts->addField(
                    'invoice_product-id' . $key,
                    'hidden',
                    ['name' => 'invoice_product[' . $key . '][id]',
                        'required' => false,
                        'value' => $product['id'],
                    ]
                );
                $fieldsetProducts->addField(
                    'invoice_product-commodity_code' . $key,
                    'text',
                    ['name' => 'invoice_product[' . $key . '][commodity_code]',
                        'label' => __('Commodity code'),
                        'title' => __('Commodity code'),
                        'required' => false,
                        'value' => $product['commodity_code'],
                    ]
                );
                $fieldsetProducts->addField(
                    'invoice_product-country_of_manufacture' . $key,
                    'hidden',
                    ['name' => 'invoice_product[' . $key . '][country_of_manufacture]',
                        'required' => false,
                        'value' => $product['country_of_manufacture'],
                    ]
                );
                $fieldsetProducts->addField(
                    'invoice_product-country_of_manufacture_name' . $key,
                    'hidden',
                    ['name' => 'invoice_product[' . $key . '][country_of_manufacture_name]',
                        'required' => false,
                        'value' => $product['country_of_manufacture_name'],
                    ]
                );
                /*$fieldsetProducts->addField(
                    'invoice_product-commodity_type' . $key,
                    'text',
                    ['name' => 'invoice_product[' . $key . '][commodity_type]',
                        'label' => __('Commodity type'),
                        'title' => __('Commodity type'),
                        'required' => false,
                        'value' => $product['commodity_type']
                    ]
                );*/
                $fieldsetProducts->addField(
                    'invoice_product-dangerous_goods' . $key,
                    'select',
                    ['name' => 'invoice_product[' . $key . '][dangerous_goods]',
                        'label' => __('Dangerous goods'),
                        'title' => __('Dangerous goods'),
                        'required' => false,
                        'options' => [__('No'), __('Yes')],
                        'value' => $product['dangerous_goods'],
                    ]
                );

                $fieldsetProducts->addField(
                    'invoice_product-dg_attribute_content_id' . $key,
                    'text',
                    ['name' => 'invoice_product[' . $key . '][dg_attribute_content_id]',
                        'label' => __('Content ID of Dangerous goods'),
                        'title' => __('Content ID of Dangerous goods'),
                        'required' => true,
                        'value' => $product['dg_attribute_content_id'],
                    ]
                );

                $fieldsetProducts->addField(
                    'invoice_product-dg_attribute_label' . $key,
                    'text',
                    ['name' => 'invoice_product[' . $key . '][dg_attribute_label]',
                        'label' => __('Label of Dangerous goods'),
                        'title' => __('Label of Dangerous goods'),
                        'required' => false,
                        'value' => $product['dg_attribute_label'],
                    ]
                );
                $fieldsetProducts->addField(
                    'invoice_product-dg_attribute_uncode' . $key,
                    'text',
                    ['name' => 'invoice_product[' . $key . '][dg_attribute_uncode]',
                        'label' => __('UNCode of Dangerous goods'),
                        'title' => __('UNCode of Dangerous goods'),
                        'required' => false,
                        'value' => $product['dg_attribute_uncode'],
                    ]
                );

                $dependenceBlock->addFieldMap(
                    "{$htmlIdPrefix}invoice_product-dangerous_goods" . $key,
                    'invoice_product-dangerous_goods' . $key
                )->addFieldMap(
                    "{$htmlIdPrefix}invoice_product-dg_attribute_content_id" . $key,
                    'invoice_product-dg_attribute_content_id' . $key
                )->addFieldMap(
                    "{$htmlIdPrefix}invoice_product-dg_attribute_label" . $key,
                    'invoice_product-dg_attribute_label' . $key
                )->addFieldMap(
                    "{$htmlIdPrefix}invoice_product-dg_attribute_uncode" . $key,
                    'invoice_product-dg_attribute_uncode' . $key
                )->addFieldDependence(
                    'invoice_product-dg_attribute_content_id' . $key,
                    'invoice_product-dangerous_goods' . $key,
                    1
                )->addFieldDependence(
                    'invoice_product-dg_attribute_label' . $key,
                    'invoice_product-dangerous_goods' . $key,
                    1
                )->addFieldDependence(
                    'invoice_product-dg_attribute_uncode' . $key,
                    'invoice_product-dangerous_goods' . $key,
                    1
                );
            }
        }

        $this->setChild(
            'form_after',
            $dependenceBlock
        );

        /*$form->setValues($model->getData());*/
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
