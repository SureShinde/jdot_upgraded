<?php
namespace RLTSquare\CancelReasonOnEditOrder\Plugin\Sales\Block\Adminhtml\Order;

use Magento\Sales\Block\Adminhtml\Order\View as OrderView;

class View
{
    public function beforeSetLayout(OrderView $subject)
    {
        $subject->addButton(
            'order_custom_button',
            [
                'label' => __('Quick Edit'),
                'class' => __('quick-edit-btn'),
                'id' => 'quick-edit-button',
                //'onclick' => 'setLocation(\'' . $subject->getUrl('module/controller/action') . '\')'
                'onclick' => 'jQuery("#editModal").modal("openModal")'
            ]
        );
    }
}
