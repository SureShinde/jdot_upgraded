<?php
namespace Pentalogy\Callcourier\Plugin\Widget;

use Magento\Backend\Block\Widget\Context as Contextt;

class Context
{
    public function afterGetButtonList(
        Contextt $subject,
        $buttonList
    )
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->get('Magento\Framework\App\Action\Context')->getRequest();
        if($request->getFullActionName() == 'sales_order_view'){
            $buttonList->add(
                'custom_button',
                [
                    'label' => __('Book Shipment'),
                    'onclick' => '',
                    'class' => 'ship',
                    'id'=>"create_shipment",
                    'title'=>"Create Shipment",
                    'type'=>"button",'class'=>"action-default scalable edit primary create-cc-shipmment",
                    'data-ui-id'=>"sales-order-edit-order-edit-button"
                ]
            );
        }

        return $buttonList;
    }

    public function getCustomUrl()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $urlManager = $objectManager->get('Magento\Framework\Url');
        return $urlManager->getUrl('sales/*/custom');
    }
}