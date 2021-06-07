<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */

namespace Infomodus\Dhllabel\Model\Config;
class ShippingMethods implements \Magento\Framework\Option\ArrayInterface
{
    protected $config;
    protected $scopeConfig;

    public function __construct(
        \Magento\Shipping\Model\Config $config,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
    }

    function toOptionArray($store = null)
    {
        $option = [];
        $_methods = $this->config->getActiveCarriers($store);
        foreach ($_methods as $_carrierCode => $_carrier) {
            if ($_carrierCode !== "ups" && $_carrierCode !== "dhlint" && $_carrierCode !== "usps" && $_method = $_carrier->getAllowedMethods()) {
                if (!$_title = $this->scopeConfig->getValue('carriers/' . $_carrierCode . '/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store)) {
                    $_title = $_carrierCode;
                }
                foreach ($_method as $_mcode => $_m) {
                    $_code = $_carrierCode . '_' . $_mcode;
                    $option[] = ['label' => "(" . $_title . ")  " . $_m, 'value' => $_code];
                }
            }
        }
        return $option;
    }

    function getShippingMethodsSimple($store = null)
    {
        $option = [];
        $_methods = $this->config->getActiveCarriers($store);
        foreach ($_methods as $_carrierCode => $_carrier) {
            if ($_carrierCode !== "ups" && $_carrierCode !== "dhlint" && $_carrierCode !== "usps" && $_method = $_carrier->getAllowedMethods()) {
                if (!$_title = $this->scopeConfig->getValue('carriers/' . $_carrierCode . '/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store)) {
                    $_title = $_carrierCode;
                }
                foreach ($_method as $_mcode => $_m) {
                    $_code = $_carrierCode . '_' . $_mcode;
                    $option[$_code] = "(" . $_title . ")  " . $_m;
                }
            }
        }
        return $option;
    }
}