<?php

namespace Aalogics\Lcs\Plugin;

class PluginBefore
{
    protected $_lcsHelper;
    protected $_order;

    public function __construct(
        \Aalogics\Lcs\Helper\Data $lcsHelper,
        \Magento\Sales\Model\Order $order
    )
    {
        $this->_lcsHelper = $lcsHelper;
        $this->_order = $order;
    }

    public function beforePushButtons(
        \Magento\Backend\Block\Widget\Button\Toolbar\Interceptor $subject,
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    ) {

        $this->_request = $context->getRequest();

        if ($this->_lcsHelper->isEnabled() && $this->_lcsHelper->isShippingEnabled() && $this->_request->getFullActionName() == 'sales_order_view') {

            $order_id = $this->_request->getParams();

            if(isset($order_id['order_id'])){
               $current_order = $this->_order->load($order_id['order_id']); 
               if($current_order->canShip()){
                    $buttonList->add(
                        'send_lcs_ship',
                        [
                            'label'   => __('LCS Ship'),
                            'onclick' => 'jQuery("#shipModal").modal("openModal")',
                        ]
                    );
               }
            }

        }

    }

}
