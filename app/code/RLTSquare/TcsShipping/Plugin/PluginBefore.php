<?php

namespace RLTSquare\TcsShipping\Plugin;

class PluginBefore
{
    protected $_tcsHelper;
    protected $_order;

    public function __construct(
        \RLTSquare\TcsShipping\Helper\Data $tcsHelper,
        \Magento\Sales\Model\Order $order
    )
    {
        $this->_tcsHelper = $tcsHelper;
        $this->_order = $order;
    }

    public function beforePushButtons(
        \Magento\Backend\Block\Widget\Button\Toolbar\Interceptor $subject,
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    ) {

        $this->_request = $context->getRequest();
        if ($this->_tcsHelper->isEnabled() && $this->_request->getFullActionName() == 'sales_order_view') {

            $order_id = $this->_request->getParams();

            if(isset($order_id['order_id'])){
               $current_order = $this->_order->load($order_id['order_id']); 
               if($current_order->canShip() && $current_order->getShippingAddress()->getCountryId() == 'PK'){
                    $buttonList->add(
                        'send_tcs_ship',
                        [
                            'label'   => __('TCS Ship'),
                            'onclick' => 'jQuery("#tcsModal").modal("openModal")',
                        ]
                    );
               }
            }

        }

    }

}
