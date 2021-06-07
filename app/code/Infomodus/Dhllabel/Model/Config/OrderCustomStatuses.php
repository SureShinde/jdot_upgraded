<?php
namespace Infomodus\Dhllabel\Model\Config;

class OrderCustomStatuses implements \Magento\Framework\Option\ArrayInterface
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

        $deprecatedStatuses = ['canceled' => 1, 'closed' => 1, 'complete' => 1, 'fraud' => 1, 'processing' => 1, 'pending' => 1];
        foreach ($orderStatusCollection as $orderStatus) {
            if(!isset($deprecatedStatuses[$orderStatus['status']])) {
                $status[] = [
                    'value' => $orderStatus['status'], 'label' => $orderStatus['label']
                ];
            }
        }
        
        return $status;
    }
}