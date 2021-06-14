<?php

namespace RLTSquare\SMS\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SavePhoneStatusToOrder
 * @package RLTSquare\SMS\Observer
 */
class SavePhoneStatusToOrder implements ObserverInterface
{
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $observer->getOrder();
            if (isset($order)) {
                $order->setEcsPhoneNumberStatus(
                    \RLTSquare\SMS\Helper\Constants::PHONE_STATUS_NOT_VERIFIED
                );
                $order->setPhoneCode('0');
                $order->setPhoneCodeCounter(0);
            }
        } catch (\Exception $exception) {
        }
    }
}
