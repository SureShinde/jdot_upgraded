<?php
/**
 * Copyright  © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Arpatech\CityDropdown\Block\Checkout;

use Magento\Checkout\Helper\Data;
use Magento\Framework\App\ObjectManager;
class LayoutProcessor extends \Magento\Checkout\Block\Checkout\LayoutProcessor
{
    /**
     * @var \Magento\Customer\Model\AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;

    /**
     * @var \Magento\Ui\Component\Form\AttributeMapper
     */
    protected $attributeMapper;

    /**
     * @var AttributeMerger
     */
    protected $merger;

    /**
     * @var \Magento\Customer\Model\Options
     */
    private $options;

    /**
     * @var Data
     */
    private $checkoutDataHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $shippingConfig;

    /**
     * @param \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param \Magento\Ui\Component\Form\AttributeMapper $attributeMapper
     * @param AttributeMerger $merger
     */
    public function __construct(
        \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider,
        \Magento\Ui\Component\Form\AttributeMapper $attributeMapper,
        \Magento\Checkout\Block\Checkout\AttributeMerger $merger,
        \Magento\Customer\Model\Options $options,
        \Magento\Checkout\Helper\Data $checkoutDataHelper,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
    ) {
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->attributeMapper = $attributeMapper;
        $this->merger = $merger;
        $this->options = $options;
        $this->checkoutDataHelper = $checkoutDataHelper;
        $this->shippingConfig = $shippingConfig;
        $this->storeManager = $storeManagerInterface;
        parent::__construct($attributeMetadataDataProvider,$attributeMapper,$merger, $options, $checkoutDataHelper, $shippingConfig, $storeManagerInterface);
    }

    public function process(
        $jsLayout
    ) {
        $attributesToConvert = [
            'prefix' => [$this->getOptions(), 'getNamePrefixOptions'],
            'suffix' => [$this->getOptions(), 'getNameSuffixOptions'],
        ];

        $elements = $this->getAddressAttributes();
        $elements = $this->convertElementsToSelect($elements, $attributesToConvert);
        // The following code is a workaround for custom address attributes
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']
        )) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children'] = $this->processPaymentChildrenComponents(
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children'],
                $elements
            );
        }

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']
        )) {
            $fields = $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'] = $this->merger->merge(
                $elements,
                'checkoutProvider',
                'shippingAddress',
                $fields
            );
        }

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['street']['children'][0]['validation']['max_text_length'] = 120;

        return $jsLayout;
    }
    /**
     * @deprecated
     * @return \Magento\Customer\Model\Options
     */
    private function getOptions()
    {
        if (!is_object($this->options)) {
            $this->options = ObjectManager::getInstance()->get(\Magento\Customer\Model\Options::class);
        }
        return $this->options;
    }
    /**
     * @return array
     */
    private function getAddressAttributes()
    {
        /** @var \Magento\Eav\Api\Data\AttributeInterface[] $attributes */
        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer_address',
            'customer_register_address'
        );

        $elements = [];
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            if ($attribute->getIsUserDefined()) {
                continue;
            }
            $elements[$code] = $this->attributeMapper->map($attribute);
            if (isset($elements[$code]['label'])) {
                $label = $elements[$code]['label'];
                $elements[$code]['label'] = __($label);
            }
        }
        return $elements;
    }
    /**
     * Convert elements(like prefix and suffix) from inputs to selects when necessary
     *
     * @param array $elements address attributes
     * @param array $attributesToConvert fields and their callbacks
     * @return array
     */
    private function convertElementsToSelect($elements, $attributesToConvert)
    {
        $codes = array_keys($attributesToConvert);
        foreach (array_keys($elements) as $code) {
            if (!in_array($code, $codes)) {
                continue;
            }
            $options = call_user_func($attributesToConvert[$code]);
            if (!is_array($options)) {
                continue;
            }
            $elements[$code]['dataType'] = 'select';
            $elements[$code]['formElement'] = 'select';

            foreach ($options as $key => $value) {
                $elements[$code]['options'][] = [
                    'value' => $key,
                    'label' => $value,
                ];
            }
        }

        return $elements;
    }
    /**
     * Appends billing address form component to payment layout
     *
     * @param array $paymentLayout
     * @param array $elements
     *
     * @return array
     */
    private function processPaymentChildrenComponents(
        array $paymentLayout,
        array $elements
    ) {
        if (!isset($paymentLayout['payments-list']['children'])) {
            $paymentLayout['payments-list']['children'] = [];
        }

        if (!isset($paymentLayout['afterMethods']['children'])) {
            $paymentLayout['afterMethods']['children'] = [];
        }

        // if billing address should be displayed on Payment method or page
        if ($this->getCheckoutDataHelper()->isDisplayBillingOnPaymentMethodAvailable()) {
            $paymentLayout['payments-list']['children'] = array_merge_recursive(
                $paymentLayout['payments-list']['children'],
                $this->processPaymentConfiguration(
                    $paymentLayout['renders']['children'],
                    $elements
                )
            );
        } else {
            $component['billing-address-form'] = $this->getBillingAddressComponent(
                'shared',
                $elements
            );

            $paymentLayout['afterMethods']['children'] = array_merge_recursive(
                $component,
                $paymentLayout['afterMethods']['children']
            );
        }

        return $paymentLayout;
    }

    /**
     * Inject billing address component into every payment component
     *
     * @param array $configuration list of payment components
     * @param array $elements attributes that must be displayed in address form
     * @return array
     */
    private function processPaymentConfiguration(array &$configuration, array $elements)
    {
        $output = [];
        foreach ($configuration as $paymentGroup => $groupConfig) {
            foreach ($groupConfig['methods'] as $paymentCode => $paymentComponent) {
                if (empty($paymentComponent['isBillingAddressRequired'])) {
                    continue;
                }

                $output[$paymentCode . '-form'] = $this->getBillingAddressComponent(
                    $paymentCode,
                    $elements
                );
            }
            unset($configuration[$paymentGroup]['methods']);
        }

        return $output;
    }

    /**
     * Gets billing address component details
     *
     * @param string $paymentCode
     * @param array  $elements
     *
     * @return array
     */
    private function getBillingAddressComponent($paymentCode, $elements)
    {
        return [
            'component' => 'Magento_Checkout/js/view/billing-address',
            'displayArea' => 'billing-address-form-' . $paymentCode,
            'provider' => 'checkoutProvider',
            'deps' => 'checkoutProvider',
            'dataScopePrefix' => 'billingAddress' . $paymentCode,
            'sortOrder' => 1,
            'children' => [
                'form-fields' => [
                    'component' => 'uiComponent',
                    'displayArea' => 'additional-fieldsets',
                    'children' => $this->merger->merge(
                        $elements,
                        'checkoutProvider',
                        'billingAddress' . $paymentCode,
                        [
                            'firstname' => [
                                'sortOrder' => 1,
                            ],
                            'lastname' => [
                                'sortOrder' => 2,
                            ],
                            'street'=>[
                                'sortOrder'=> 199,
                            ],
                            'country_id' => [
                                'sortOrder' => 200,
                            ],
                            'region' => [
                                'sortOrder' => 201,
                                'visible' => false,
                            ],
                            'region_id' => [
                                'sortOrder' => 201,
                                'component' => 'Magento_Ui/js/form/element/region',
                                'config' => [
                                    'template' => 'ui/form/field',
                                    'elementTmpl' => 'ui/form/element/select',
                                    'customEntry' => 'billingAddress' . $paymentCode . '.region',
                                ],
                                'validation' => [
                                    'required-entry' => true,
                                ],
                                'filterBy' => [
                                    'target' => '${ $.provider }:${ $.parentScope }.country_id',
                                    'field' => 'country_id',
                                ],
                            ],
                            'city' => [
                                'sortOrder' => 202,
//                                'displayArea' => 'after_region_id',
                                'visible' => false,
                            ],
                            'city' => [
                                'sortOrder' => 202,
                                'displayArea' => 'after_country_id',
                                'component' => 'Arpatech_CityDropdown/js/form/element/city',
                                'config' => [
                                    'template' => 'Arpatech_CityDropdown/form/field',
                                    'elementTmpl' => 'Arpatech_CityDropdown/form/element/select',
                                    'customEntry' => 'shippingAddress.city',
                                ],
                                'validation' => [
                                    'required-entry' => true,
                                ],
                                'filterBy' => [
                                    'target' => '${ $.provider }:${ $.parentScope }.country_id',
                                    'field' => 'country_id',
                                ],
                            ],
                            'postcode' => [
                                'sortOrder' => 203,
                                'component' => 'Magento_Ui/js/form/element/post-code',
                                'validation' => [
                                    'required-entry' => true,
                                ],
                            ],
                            'company' => [
                                'visible' => false,
                                'validation' => [
                                    'min_text_length' => 0,
                                ],
                            ],
                            'fax' => [
                                'visible' => false,
                                'validation' => [
                                    'min_text_length' => 0,
                                ],
                            ],
                            'telephone' => [
                                'sortOrder' => 204,
                                'config' => [
                                    'tooltip' => [
                                        'description' => __('For delivery questions.'),
                                    ],
                                ],
                            ],

                        ]
                    ),
                ],
            ],
        ];
    }

    /**
     * Get checkout data helper instance
     *
     * @return Data
     * @deprecated
     */
    private function getCheckoutDataHelper()
    {
        if (!$this->checkoutDataHelper) {
            $this->checkoutDataHelper =
                ObjectManager::getInstance()->get(Data::class);
        }

        return $this->checkoutDataHelper;
    }
}
