<?php
/**
 * NOTICE OF LICENSE
 * You may not sell, distribute, sub-license, rent, lease or lend complete or portion of software to anyone.
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @package   RLTSquare_TcsShipping
 * @copyright Copyright (c) 2018 RLTSquare (https://www.rltsquare.com)
 * @contacts  support@rltsquare.com
 * @license  See the LICENSE.md file in module root directory
 */

namespace RLTSquare\CustomCouponRule\Helper;

/**
 * Class Data
 * @package RLTSquare\TcsShipping\Helper
 * @author Umar Chaudhry <umarch@rltsquare.com>
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $stockstate;
    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState
    ) {
        $this->_stockstate = $stockState;
        parent::__construct($context);
    }


    public function canApplyRule($quote){
        // Getting cart items and checking whether any item has special price or not
        $cartItems = $quote->getAllItems();
        $canApplyRule = true;
        foreach($cartItems as $item) {
            if ($item->getProduct()->getTypeId() != "simple") {
                $productTypeInstance = $item->getProduct()->getTypeInstance();
                $usedProducts = $productTypeInstance->getUsedProducts($item->getProduct());

                foreach ($usedProducts as $child) {
                    $stockStatus = $this->_stockstate->getStockQty($child->getId(), $child->getStore()->getWebsiteId());
                    if ($stockStatus >= 0) {
                        $specialPrice = $child->getFinalPrice();
                        $specialFromDate = strtotime($child->getData('special_from_date'));
                        $specialToDate = strtotime($child->getData('special_to_date'));
                        $now = strtotime(date("Y-m-d"));
                        if (!empty($specialPrice) && ($specialFromDate <= $now && $now <= $specialToDate)) {
                            $canApplyRule = false;
                            break;
                        }
                    }
                }
            } else {
                $price = $item->getProduct()->getPrice();
                $specialPrice = $item->getProduct()->getSpecialPrice();
                $specialFromDate = $item->getProduct()->getSpecialFromDate();
                $specialFromDate = strtotime($specialFromDate);
                $specialToDate = $item->getProduct()->getSpecialToDate();
                $specialToDate = strtotime($specialToDate);
                $now = strtotime(date("Y-m-d"));

                if (!empty($specialPrice) && ($specialFromDate <= $now && $now <= $specialToDate)) {
                    $canApplyRule = false;
                }
            }
        }
        return $canApplyRule;
    }

}