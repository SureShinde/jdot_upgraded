<?php

namespace RLTSquare\SMS\Block\Onepage;

use Magento\Framework\View\Element\Template;

/**
 * Class Success
 * @package RLTSquare\SMS\Block\Onepage
 */
class Success extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\SessionFactory
     */
    public $checkoutSessionFactory;

    /**
     * @var \RLTSquare\SMS\Helper\Api\SendMessage
     */
    public $sendSMS;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    public $orderRepository;

    /**
     * Success constructor.
     * @param Template\Context $context
     * @param \Magento\Checkout\Model\SessionFactory $checkoutSessionFactory
     * @param \RLTSquare\SMS\Helper\Api\SendMessage $sendSMS
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Checkout\Model\SessionFactory $checkoutSessionFactory,
        \RLTSquare\SMS\Helper\Api\SendMessage $sendSMS,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSessionFactory = $checkoutSessionFactory;
        $this->sendSMS = $sendSMS;
        $this->scopeConfig = $scopeConfig;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return bool
     */
    public function sendSMSToCustomer()
    {
        /** @var \Magento\Checkout\Model\Session $checkoutSession */
        $checkoutSession = $this->checkoutSessionFactory->create();

        $orderId = $checkoutSession->getLastRealOrder()->getId();

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($orderId);

        $phoneNumber = $order->getBillingAddress()->getTelephone();

        $phoneNumber = substr($phoneNumber, -10);

        if($order->getPayment()->getMethod()== 'cashondelivery'){
        if (strlen($phoneNumber) === 10) {

            $phoneNumber = \RLTSquare\SMS\Helper\Constants::COUNTRY_CODE . $phoneNumber;

            $IncrementId = $order->getIncrementId();

            $smsCode = rand(1000, 9999);
            $text = $this->scopeConfig->getValue(
                'general/api_credentials/sms_text'
            );
            $text = 'Your+order+verification+code+for+order+no.+';
            $text = $text . $IncrementId . '+is+' . $smsCode;

            try {
                $sessionId = $this->sendSMS->getSessionId();
                if (isset($sessionId)) {
                    $isSuccessMessage = $this->sendSMS->sendMessage(
                        $sessionId,
                        $phoneNumber,
                        $text
                    );

                    if (isset($isSuccessMessage)) {
                        $order->setPhoneCode($smsCode);
                        $counter = $order->getPhoneCodeCounter();
                        $order->setPhoneCodeCounter(++$counter);
                        $order->save();
                        return true;
                    }
                    return false;
                }
                return false;
            } catch (\Exception $exception) {
            }
            return false;

        } else {
            return false;
        }
    }
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        /** @var \Magento\Checkout\Model\Session $checkoutSession */
        $checkoutSession = $this->checkoutSessionFactory->create();

        $orderId = $checkoutSession->getLastRealOrder()->getId();

        return $orderId;
    }

    /**
     * @return string
     */
    public function getActionUrl()
    {
        return $this->getBaseUrl() . 'sms/index/index';
    }

    /**
     * @return string
     */
    public function getResendUrl()
    {
        return $this->getBaseUrl() . 'sms/index/resendSMS';
    }

    /**
     * @return mixed
     */
    public function isEnableDisable()
    {
        $isEnableDisable = $this->scopeConfig->getValue('general/api_credentials/isEnableDisable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $isEnableDisable;
    }
}
