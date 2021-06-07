<?php
namespace Aalogics\Lcs\Block\Checkout;

use Magento\Directory\Helper\Data as DirectoryHelper;

class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{

    /**
     * @var DirectoryHelper
     */
    protected $directoryHelper;

    protected $_request;

    protected $_stateOption;

    /**
     * @var \Aalogics\Lcs\Helper\Data
     */
    protected $_helper;

    public function __construct(
        \Aalogics\Lcs\Model\Config\Source\CityOptions $cityOption,
        DirectoryHelper $directoryHelper,
        \Aalogics\Lcs\Helper\Data $helper
    ) {
        $this->directoryHelper = $directoryHelper;
        $this->_cityOption     = $cityOption;
        $this->_helper = $helper;
    }

    /**
     * @param array $result
     * @return array
     */
    public function process($result)
    {
        if($this->_helper->isCitiesEnabled()){
            $region = $this->_cityOption->getCities();
            $regionOptions[] = ['label' => 'Please Select..', 'value' => ''];
            foreach ($region as $field) {
                $regionOptions[] = ['label' => $field['default_name'], 'value' => $field['default_name']];
            }

            if ($result['components']['checkout']['children']['steps']
                ['children']['shipping-step']['children']['shippingAddress']) {

                $result['components']['checkout']['children']['steps']
                ['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['city_id'] = [
                    'component' => 'Magento_Ui/js/form/element/select',
                    'config'    => [
                        'customScope'       => 'shippingAddress',
                        'template'          => 'ui/form/field',
                        'elementTmpl'       => 'Aalogics_Lcs/select',
                        'id'                => 'drop-down',
                        'additionalClasses' => 'city-drop-down',
                    ],
                    'dataScope' => 'shippingAddress.city',
                    'label'     => 'City',
                    'provider'  => 'checkoutProvider',
                    'visible'   => true,
                    'sortOrder' => 115,
                    'id'        => 'city-drop-down',
                    'options'   => $regionOptions,
                ];

                $result['components']['checkout']['children']['steps']
                ['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['city'] = [
                    'component'  => 'Magento_Ui/js/form/element/abstract',
                    'config'     => [
                        // customScope is used to group elements within a single form (e.g. they can be validated separately)
                        'template'          => 'ui/form/field',
                        'elementTmpl'       => 'ui/form/element/input',
                        'additionalClasses' => 'city-input-box',
                    ],
                    'dataScope'  => 'shippingAddress.city',
                    'label'      => 'City',
                    'provider'   => 'checkoutProvider',
                    'sortOrder'  => 114,
                    'validation' => [
                        'required-entry' => true,
                    ],
                    'options'    => [],
                    'visible'    => false,
                ];

            }   
        }
        
        return $result;

    }

}
