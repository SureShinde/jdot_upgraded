<?php
namespace Arpatech\TempFixConfigurableProduct\Plugin;

class FinalPricePlugin
{
    public function beforeSetTemplate(\Magento\Catalog\Pricing\Render\FinalPriceBox $subject, $template)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $enable=true;//$objectManager->create('MyVendor\MyModule\Helper\Data')->chkIsModuleEnable();
        if ($enable) {
            if ($template == 'Magento_ConfigurableProduct::product/price/final_price.phtml') {
                return ['Arpatech_TempFixConfigurableProduct::product/price/final_price.phtml'];
            }
            else
            {
                return [$template];
            }
        } else {
            return[$template];
        }
    }
}