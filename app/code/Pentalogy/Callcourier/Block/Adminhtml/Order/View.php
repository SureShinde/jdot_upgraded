<?php
namespace Pentalogy\Callcourier\Block\Adminhtml\Order;
class View {
    public function beforeSetLayout(\Magento\Sales\Block\Adminhtml\Order\View $view)
    {
        $message ='Are you sure you want to do this?';
        $url = '/mymodule/controller/action/id/' . $view->getOrderId();


        $view->addButton(
            'order_myaction',
            [
                'label' => __('My Action'),
                'class' => 'myclass',
                'onclick' => "confirmSetLocation('{$message}', '{$url}')"
            ]
        );


    }
}