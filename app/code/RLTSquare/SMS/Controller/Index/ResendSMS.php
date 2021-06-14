<?php

namespace RLTSquare\SMS\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class ResendSMS
 * @package RLTSquare\SMS\Controller\Index
 */
class ResendSMS extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \RLTSquare\SMS\Helper\Api\SendMessage
     */
    private $smsSessionValue;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $pageFactory;

    /**
     * ResendSMS constructor.
     * @param Context $context
     * @param \RLTSquare\SMS\Helper\Api\SendMessage $smsSessionValue
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        \RLTSquare\SMS\Helper\Api\SendMessage $smsSessionValue,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->smsSessionValue = $smsSessionValue;
        $this->orderRepository = $orderRepository;
        $this->scopeConfig = $scopeConfig;
        $this->pageFactory = $pageFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(
            ResultFactory::TYPE_JSON
        );

        $orderId = $this->getRequest()->getParam('orderId');

        if (isset($orderId)) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderRepository->get($orderId);
        } else {
            $resultJson->setData([
                'resent' => '0'
            ]);
        }

        $smsStatus = $order->getEcsPhoneNumberStatus();
        $counter = $order->getPhoneCodeCounter();

        if (\RLTSquare\SMS\Helper\Constants::PHONE_STATUS_NOT_VERIFIED === $smsStatus
            && \RLTSquare\SMS\Helper\Constants::LIMIT > $counter
        ) {
            $phoneNumber = $order->getBillingAddress()->getTelephone();

            $phoneNumber = substr($phoneNumber, -10);

            if (strlen($phoneNumber) !== 10) {
                $resultJson->setData([
                    'resent' => '3'
                ]);
                return $resultJson;
            }

            $phoneNumber = \RLTSquare\SMS\Helper\Constants::COUNTRY_CODE . $phoneNumber;

            $smsCode = rand(1000, 9999);
            $text = $this->scopeConfig->getValue(
                'general/api_credentials/sms_text'
            );
            $text='Your+order+verification+code+is:+';
            $text = $text.$smsCode;
            try {
                $sessionId = $this->smsSessionValue->getSessionId();
                if (isset($sessionId)) {
                    $isSuccessMessage = $this->smsSessionValue->sendMessage(
                        $sessionId,
                        $phoneNumber,
                        $text
                    );

                    if (isset($isSuccessMessage)) {
                        $order->setPhoneCode($smsCode);
                        $order->setPhoneCodeCounter(++$counter);
                        $order->save();
                        $resultJson->setData([
                            'resent' => '1'
                        ]);
                        return $resultJson;
                    }
                    $resultJson->setData([
                        'resent' => '0'
                    ]);
                    return $resultJson;
                }
                $resultJson->setData([
                    'resent' => '0'
                ]);
                return $resultJson;
            } catch (\Exception $exception) {
                $resultJson->setData([
                    'resent' => '3'
                ]);
                return $resultJson;
            }
        } else {
            $resultJson->setData([
                'resent' => '3'
            ]);
            return $resultJson;
        }
    }
}
