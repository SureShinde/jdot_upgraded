<?php
namespace Infomodus\Dhllabel\Model\Config;

class FrontShippingMethod extends \Infomodus\Dhllabel\Helper\Config implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray($isMultiSelect = false)
    {
        $option = [['value'=>'', 'label'=> __('--Please Select--')]];
        $_methods = $this->shippingConfig->getActiveCarriers();
        foreach ($_methods as $_carrierCode => $_carrier) {
            if ($_carrierCode !=="ups" && $_carrierCode !=="dhl" && $_carrierCode !=="usps"
                && $_method = $_carrier->getAllowedMethods()) {
                $_title = $_carrierCode;
                foreach ($_method as $_mcode => $_m) {
                    $_code = $_carrierCode . '_' . $_mcode;
                    $option[] = ['label' => "(".$_title.")  ". $_m, 'value' => $_code];
                }
            }
        }
        return $option;
    }
}
