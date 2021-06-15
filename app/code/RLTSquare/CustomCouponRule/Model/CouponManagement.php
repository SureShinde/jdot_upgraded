<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace RLTSquare\CustomCouponRule\Model;

use \Magento\Quote\Api\CouponManagementInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Coupon management object.
 */
class CouponManagement extends \Magento\Quote\Model\CouponManagement
{

    protected $data;

    /**
     * Constructs a coupon read service object.
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository Quote repository.
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \RLTSquare\CustomCouponRule\Helper\Data $data
    ) {
        $this->data = $data;
        parent::__construct($quoteRepository);
    }



    /**
     * {@inheritdoc}
     */
    public function set($cartId, $couponCode)
    {
        $couponCode = trim($couponCode);
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }
        if (!$quote->getStoreId()) {
            throw new NoSuchEntityException(__('Cart isn\'t assigned to correct store'));
        }
        $quote->getShippingAddress()->setCollectShippingRates(true);

        $canApplyRule = true;
        try {
            if($this->data->canApplyRule($quote)) {
                $canApplyRule = false;
                $quote->setCouponCode($couponCode);
                $this->quoteRepository->save($quote->collectTotals());
            }
        } catch (\Exception $e) {
            if(!$canApplyRule) {
                throw new CouldNotSaveException(__('Could not apply coupon code'));
            }
            else{

            }
        }
        if($canApplyRule){
            throw new CouldNotSaveException(__('Your order contains one or more products already having discount'));
        }
        else if ($quote->getCouponCode() != $couponCode) {
            throw new NoSuchEntityException(__('Coupon code is not valid'));
        }
        return true;
    }
}
