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

namespace RLTSquare\TcsShipping\Controller\Checkout;

use Magento\Framework\App\Action\Context;

/**
 * Class Totals
 * @package RLTSquare\TcsShipping\Controller\Checkout
 * @author Umar Chaudhry <umarch@rltsquare.com>
 */
class Totals extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJson;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * Totals constructor.
     * @param Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJson
     */
    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJson
    )
    {
        parent::__construct($context);
        $this->_checkoutSession = $checkoutSession;
        $this->_jsonHelper = $jsonHelper;
        $this->_resultJson = $resultJson;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $response = [
            'errors' => false,
            'message' => 'Re-calculate successful.'
        ];
        try {
            $selectedShippingMethod = $this->_checkoutSession->getQuote()->getShippingAddress()->getShippingMethod();
            if ($selectedShippingMethod == "tcsshipping_tcsshipping") {
                $this->_checkoutSession->getQuote()->getShippingAddress()
                    ->setCollectShippingRates(true)
                    ->collectShippingRates()->save();
                $this->_checkoutSession->getQuote()->collectTotals()->save();
            }
        } catch (\Exception $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        }

        $resultJson = $this->_resultJson->create();
        return $resultJson->setData($response);
    }
}