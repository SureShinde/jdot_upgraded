<?php

namespace Infomodus\Dhllabel\Model\Config;

class OrderStatuses implements \Magento\Framework\Option\ArrayInterface
{
    protected $status;
    public function __construct(\Magento\Sales\Model\Order\Status $status)
    {
        $this->status = $status;
    }

    public function toOptionArray($isMultiSelect = false)
    {
        $orderStatusCollection = $this->status->getResourceCollection()->getData();
        $status = [
            ['value' => "", 'label' => '--Please Select--']
        ];
        foreach ($orderStatusCollection as $orderStatus) {
            $status[] = [
                'value' => $orderStatus['status'], 'label' => $orderStatus['label']
            ];
        }

        return $status;
    }
}