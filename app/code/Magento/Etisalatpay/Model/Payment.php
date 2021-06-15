<?php

namespace Magento\Etisalatpay\Model;

class Payment extends \Magento\Payment\Model\Method\AbstractMethod
{
	const METHOD_CODE = 'etisalatpay';

	protected $_code  = self::METHOD_CODE;

	/**
     * Payment Method feature
     *
     * @var bool
     */
	protected $_isGateway = true;

	/**
     * Payment Method feature
     *
     * @var bool
     */
	protected $_canOrder = true;

	/**
     * Payment Method feature
     *
     * @var bool
     */
	protected $_canAuthorize = true;

	/**
     * Payment Method feature
     *
     * @var bool
     */
	protected $_canCapture = true;

	/**
     * Payment Method feature
     *
     * @var bool
     */
	protected $_canCapturePartial = true;

	/**
     * Payment Method feature
     *
     * @var bool
     */
	protected $_canCaptureOnce = true;

	/**
     * Payment Method feature
     *
     * @var bool
     */
	protected $_canRefund = true;

	/**
     * Payment Method feature
     *
     * @var bool
     */
	protected $_canUseInternal = true;

	/**
     * Payment Method feature
     *
     * @var bool
     */
	protected $_canUseCheckout = true;

	/**
     * Payment Method feature
     *
     * @var bool
     */
	protected $_isInitializeNeeded = false;

	/**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canFetchTransactionInfo = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canReviewPayment = false;

    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentData;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Logger
     */
    protected $logger;	

	protected $_countryFactory;

	//protected $_canRefundInvoicePartial = true;
	//sprotected $_minAmount = null;
	//protected $_maxAmount = null;
	//protected $_supportedCurrencyCodes = array('USD');
	//protected $_debugReplacePrivateDataKeys = ['number', 'exp_month', 'exp_year', 'cvc'];

	// public function __construct(
	// 	\Magento\Framework\Model\Context $context,
	// 	\Magento\Framework\Registry $registry,
	// 	\Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
	// 	\Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
	// 	\Magento\Payment\Helper\Data $paymentData,
	// 	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
	// 	\Magento\Payment\Model\Method\Logger $logger,
	// 	\Magento\Framework\Module\ModuleListInterface $moduleList,
	// 	\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
	// 	\Magento\Directory\Model\CountryFactory $countryFactory,
	// 	array $data = array()
	// ) {
	// 	parent::__construct(
	// 		$context,
	// 		$registry,
	// 		$extensionFactory,
	// 		$customAttributeFactory,
	// 		$paymentData,
	// 		$scopeConfig,
	// 		$logger,
	// 		$moduleList,
	// 		$localeDate,
	// 		null,
	// 		null,
	// 		$data
	// 	);

	// 	$this->_countryFactory = $countryFactory;
	// 	//$this->_minAmount = $this->getConfigData('min_order_total');
	// 	//$this->_maxAmount = $this->getConfigData('max_order_total');
	// }

	// public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
	// {
	// 	if (!$this->canAuthorize()) {
	// 		throw new \Magento\Framework\Exception\LocalizedException(__('The authorize action is not available.'));
	// 	}
	// 	return $this;
	// }

	// public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
	// {

	// 	$order = $payment->getOrder();

	// 	$billing = $order->getBillingAddress();

	// 	if (!$this->canCapture()) {
	// 		throw new \Magento\Framework\Exception\LocalizedException(__('The capture action is not available.'));
	// 	}
	// 	return $this;
	// }
}