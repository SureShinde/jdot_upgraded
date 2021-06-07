<?php
/**
 * @category    LCS
 * @package     Lcs_Cod
 * @author      AAlogics team <team@aalogics.com>
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Aalogics\Lcs\Model\System;

class OrderStatusOptions
{

    /**
     * @var \Magento\Sales\Model\Order\ConfigFactory
     */
    protected $salesOrderConfigFactory;

    public function __construct(
        \Magento\Sales\Model\Order\ConfigFactory $salesOrderConfigFactory
    ) {
        $this->salesOrderConfigFactory = $salesOrderConfigFactory;
    }
    /**
     * Get order status options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $returnArray = array();
        $returnArray[] = array('value' => '', 'label' => __('Default'));
        foreach ($this->salesOrderConfigFactory->create()->getStatuses() as $status => $statusLabel) {
            $returnArray[] = array('value' => $status, 'label' => __($statusLabel));
        }
        return $returnArray;
    }

}
