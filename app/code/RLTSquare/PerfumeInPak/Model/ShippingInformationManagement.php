<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace RLTSquare\PerfumeInPak\Model;

use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Psr\Log\LoggerInterface as Logger;
use Magento\Quote\Model\QuoteAddressValidator;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Model\ShippingAssignmentFactory;
use Magento\Quote\Model\ShippingFactory;
use Magento\Framework\App\ObjectManager;

class ShippingInformationManagement extends \Magento\Checkout\Model\ShippingInformationManagement
{
    /**
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var PaymentDetailsFactory
     */
    protected $paymentDetailsFactory;

    /**
     * @var \Magento\Quote\Api\CartTotalRepositoryInterface
     */
    protected $cartTotalsRepository;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var QuoteAddressValidator
     * @deprecated 100.2.0
     */
    protected $addressValidator;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     * @deprecated 100.2.0
     */
    protected $addressRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @deprecated 100.2.0
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     * @deprecated 100.2.0
     */
    protected $totalsCollector;

    /**
     * @var \Magento\Quote\Api\Data\CartExtensionFactory
     */
    private $cartExtensionFactory;

    /**
     * @var \Magento\Quote\Model\ShippingAssignmentFactory
     */
    protected $shippingAssignmentFactory;

    /**
     * @var \Magento\Quote\Model\ShippingFactory
     */
    private $shippingFactory;

    protected $_messageManager;
    protected $_productCollectionFactory;

    public function __construct(
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Checkout\Model\PaymentDetailsFactory $paymentDetailsFactory,
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        QuoteAddressValidator $addressValidator,
        Logger $logger,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        CartExtensionFactory $cartExtensionFactory = null,
        ShippingAssignmentFactory $shippingAssignmentFactory = null,
        ShippingFactory $shippingFactory = null,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    )
    {
        parent::__construct($paymentMethodManagement, $paymentDetailsFactory, $cartTotalsRepository, $quoteRepository, $addressValidator, $logger, $addressRepository, $scopeConfig, $totalsCollector, $cartExtensionFactory, $shippingAssignmentFactory, $shippingFactory);
        $this->paymentDetailsFactory = $paymentDetailsFactory;
        $this->_messageManager = $messageManager;
        $this->_productCollectionFactory = $productCollectionFactory;
    }

    /**
     * {@inheritDoc}
     * @throws NoSuchEntityException
     * @throws InputException
     * @throws StateException
     */
    public function saveAddressInformation(
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ):PaymentDetailsInterface {
        $address = $addressInformation->getShippingAddress();
        $billingAddress = $addressInformation->getBillingAddress();
        $country=$address->getCountryId();
        $carrierCode = $addressInformation->getShippingCarrierCode();
        $methodCode = $addressInformation->getShippingMethodCode();

        if (!$address->getCustomerAddressId()) {
            $address->setCustomerAddressId(null);
        }

        if ($billingAddress && !$billingAddress->getCustomerAddressId()) {
            $billingAddress->setCustomerAddressId(null);
        }

        if (!$address->getCountryId()) {
            throw new StateException(__('Shipping address is not set'));
        }
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $isKetoInUS=true;
        foreach($quote->getAllItems() as $item){
            $product = $item->getProduct();
            $collection = $this->_productCollectionFactory->create();
            $collection->addAttributeToSelect('perfume_is_saleable');
            $collection->addAttributeToFilter('entity_id', $product->getId());
            $collection->load();
            $detailData=$collection->getItems();
            $saleableValue=reset($detailData)->getData('perfume_is_saleable');
                if($saleableValue==1 && $country !='PK' ){
                $isKetoInUS=false;
            }
        }
        $address->setLimitCarrier($carrierCode);
        $quote = $this->prepareShippingAssignment($quote, $address, $carrierCode . '_' . $methodCode);
        $this->validateQuote($quote);
        $quote->setIsMultiShipping(false);
        if ($billingAddress) {
            $quote->setBillingAddress($billingAddress);
        }
        try {
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new InputException(__('Unable to save shipping information. Please check input data.'));
        }

        $shippingAddress = $quote->getShippingAddress();

        if (!$shippingAddress->getShippingRateByCode($shippingAddress->getShippingMethod())) {
            throw new NoSuchEntityException(
                __('Carrier with such method not found: %1, %2', $carrierCode, $methodCode)
            );
        }

        /** @var \Magento\Checkout\Api\Data\PaymentDetailsInterface $paymentDetails */
        $paymentDetails = $this->paymentDetailsFactory->create();
        $paymentDetails->setPaymentMethods($this->paymentMethodManagement->getList($cartId));
        $paymentDetails->setTotals($this->cartTotalsRepository->get($cartId));
        if($isKetoInUS==true) {
            return $paymentDetails;
        }
        else{
            throw new StateException(__('Perfume can\'t shipped outside the PK .'));
        }
    }

    /**
     * Validate quote
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @throws InputException
     * @throws NoSuchEntityException
     * @return void
     */
    protected function validateQuote(\Magento\Quote\Model\Quote $quote):void
    {
        if (0 == $quote->getItemsCount()) {
            throw new InputException(__('Shipping method is not applicable for empty cart'));
        }
    }

    /**
     * Prepare shipping assignment.
     *
     * @param CartInterface $quote
     * @param AddressInterface $address
     * @param string $method
     * @return CartInterface
     */
    private function prepareShippingAssignment(CartInterface $quote, AddressInterface $address, $method)
    {
        $cartExtension = $quote->getExtensionAttributes();
        if ($cartExtension === null) {
            $cartExtension = $this->cartExtensionFactory->create();
        }

        $shippingAssignments = $cartExtension->getShippingAssignments();
        if (empty($shippingAssignments)) {
            $shippingAssignment = $this->shippingAssignmentFactory->create();
        } else {
            $shippingAssignment = $shippingAssignments[0];
        }

        $shipping = $shippingAssignment->getShipping();
        if ($shipping === null) {
            $shipping = $this->shippingFactory->create();
        }

        $shipping->setAddress($address);
        $shipping->setMethod($method);
        $shippingAssignment->setShipping($shipping);
        $cartExtension->setShippingAssignments([$shippingAssignment]);
        return $quote->setExtensionAttributes($cartExtension);
    }
}
