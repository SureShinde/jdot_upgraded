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

namespace RLTSquare\TcsShipping\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Psr\Log\LoggerInterface;

/**
 * Class TcsShipping
 * @package RLTSquare\TcsShipping\Model\Carrier
 * @author Umar Chaudhry <umarch@rltsquare.com>
 */
class TcsShipping extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'tcsshipping';

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $_request;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    private $_webApiRequest;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     */
    protected $_trackFactory;

    /**
     * @var \Magento\Shipping\Model\Tracking\Result\StatusFactory
     */
    protected $_trackStatusFactory;

    /**
     * TcsShipping constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Webapi\Rest\Request $webApiRequest
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Webapi\Rest\Request $webApiRequest,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        array $data = []
    )
    {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_jsonHelper = $jsonHelper;
        $this->_request = $request;
        $this->_webApiRequest = $webApiRequest;
        $this->_trackFactory = $trackFactory;
        $this->_trackStatusFactory = $trackStatusFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->getCarrierCode() => __($this->getConfigData('name'))];
    }

    /**
     * @return bool
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result
     */
    public function collectRates(RateRequest $request)
    {
        $paymentMethod = null;
        $cartWeight = ceil($request->getPackageWeight());
        $cartTotalLimit = $this->getConfigData('cartamountlimit');
        $cartTotal = $request->getPackageValueWithDiscount();
        $pricePerKg = 0;

        if (!$this->isActive()) {
            return false;
        }

        //in case if params are being sent after payment method selection
        try {
            $paymentMethod = $this->_jsonHelper->jsonDecode($this->_request->getContent())['rltsPaymentMethod'];
        } catch (\Exception $e) {

        }

        //in case payment method was saved but user went back to shipping page
        if (null == $paymentMethod)
            if (null != $this->_checkoutSession->getQuote()->getPayment()->getMethod())
                $paymentMethod = $this->_checkoutSession->getQuote()->getPayment()->getMethod();

        //in case order was placed and we didn't have payment info
        if (null == $paymentMethod)
            try {
                $paymentMethod = $this->_webApiRequest->getBodyParams()['paymentMethod']['method'];
            } catch (\Exception $e) {
            }

        if ($paymentMethod)
            switch ($paymentMethod) {
                case 'cashondelivery':
                    $pricePerKg = $this->getConfigData('cashondeliverypriceperkg');
                    break;
                case 'banktransfer':
                    $pricePerKg = $this->getConfigData('banktransferpriceperkg');
                    break;
                case 'ubl':
                    $pricePerKg = $this->getConfigData('onlinepaymentpriceperkg');
                    break;
                case 'etisalatpay':
                    $pricePerKg = $this->getConfigData('onlinepaymentpriceperkg');
                    break;
            }

        if ($cartTotal <= $cartTotalLimit)
            $shippingPrice = $pricePerKg * $cartWeight;
        else
            $shippingPrice = 0;

        $result = $this->_rateResultFactory->create();

        $method = $this->_rateMethodFactory->create();

        $method->setCarrier($this->getCarrierCode());
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->getCarrierCode());
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);

        $result->append($method);

        return $result;
    }


    /**
     * Get tracking
     *
     * @param string|string[] $trackings
     * @return Result
     */
    public function getTracking($trackings)
    {
        $result = $this->_trackFactory->create();

        $tracking = $this->_trackStatusFactory->create();

        $tracking->setCarrier($this->_code);
        $tracking->setCarrierTitle($this->getConfigData('title'));
        //$tracking->setUrl('http://www.tcscouriers.com/pk/Tracking/Default.aspx?TrackBy=ReferenceNumberHome&trackNo=' . $trackings);

        $tracking->setTracking($trackings);

        $result->append($tracking);

        return $tracking;
    }

    public function getTrackingInfo($trackings)
    {
        $result = $this->_trackFactory->create();

        $tracking = $this->_trackStatusFactory->create();

        $tracking->setCarrier($this->_code);
        $tracking->setCarrierTitle($this->getConfigData('title'));
        $tracking->setUrl('http://www.tcscouriers.com/pk/Tracking/Default.aspx?TrackBy=ReferenceNumberHome&trackNo=' . $trackings);

        $tracking->setTracking($trackings);

        $result->append($tracking);

        return $tracking;
    }
}