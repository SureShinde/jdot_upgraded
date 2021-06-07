<?php

namespace Infomodus\Dhllabel\Block\Adminhtml\Widget\Items\Grid\Column\Renderer;

class AddNewLabelLinks extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public $config;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Infomodus\Dhllabel\Helper\Config $config,
        array $data = []
    ) {
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    public function setColumn($column)
    {
        parent::setColumn($column);
        return $this;
    }

    /**
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $shipment_id = null;
        if ($row->getShipmentId() > 0) {
            $shipment_id = $row->getShipmentId();
        }
        return $row->getTrackingnumber() .
            '<br> <a href="' .
            $this->getUrl('infomodus_dhllabel/items/edit',
                ['order_id' => $row->getOrderId(), 'direction' => 'shipment', 'shipment_id' => $shipment_id])
            . '">' . __('Add new DHL Shipping Label') . '</a>
        <br> <a href="' .
            $this->getUrl('infomodus_dhllabel/items/edit',
                ['order_id' => $row->getOrderId(), 'direction' => 'refund', 'shipment_id' => $shipment_id])
            . '">' . __('Add new DHL Return Label') . '</a>
        <br> <a href="' .
            $this->getUrl('infomodus_dhllabel/items/edit',
                ['order_id' => $row->getOrderId(), 'direction' => 'invert', 'shipment_id' => $shipment_id])
            . '">' . __('Add new DHL Invert Label') . '</a>
        ';
    }
}
