<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace RLTSquare\OrderState\Model\Order;

use Magento\Framework\Exception\LocalizedException;

/**
 * Order configuration model
 *
 * @api
 * @since 100.0.2
 */
class Config extends \Magento\Sales\Model\Order\Config
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\Collection
     */
    protected $collection;
    /**
     * Statuses per state array
     *
     * @var array
     */
    protected $stateStatuses;

    /**
     * @var array
     */
    private $statuses;

    /**
     * @var Status
     */
    protected $orderStatusFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory
     */
    protected $orderStatusCollectionFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var array
     */
    protected $maskStatusesMapping = [
        \Magento\Framework\App\Area::AREA_FRONTEND => [
            \Magento\Sales\Model\Order::STATUS_FRAUD => \Magento\Sales\Model\Order::STATE_PROCESSING,
            \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW => \Magento\Sales\Model\Order::STATE_PROCESSING
        ]
    ];


    /**
     * Order states getter
     *
     * @return array
     */
    public function getStates()
    {
        $states = [];
        foreach ($this->_getCollection() as $item) {
            if ($item->getState()) {
                $states[$item->getState()] = __($item->getData('label'));
            }
        }
        return $states;
    }



}
