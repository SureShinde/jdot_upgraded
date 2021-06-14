<?php

namespace RLTSquare\SMS\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class MassSms
 * @package RLTSquare\SMS\Controller\Adminhtml\Index
 */
class MassSms extends Action
{
    /**
     * @var \RLTSquare\SMS\Helper\APi\SendMessage
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
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * MassSms constructor.
     * @param Context $context
     * @param \RLTSquare\SMS\Helper\APi\SendMessage $sendSMS
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        \RLTSquare\SMS\Helper\APi\SendMessage $sendSMS,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->sendSMS = $sendSMS;
        $this->scopeConfig = $scopeConfig;
        $this->orderRepository = $orderRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $orderIds = $this->_request->getPost('selected');
        sort($orderIds);
        $flag = false;
        $counter = 0;

        foreach ($orderIds as $orderId) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderRepository->get($orderId);

            $phoneNumber = $order->getBillingAddress()->getTelephone();
            $firstName = $order->getBillingAddress()->getFirstname();
            $lastName = $order->getBillingAddress()->getLastname();
            $phoneNumber = substr($phoneNumber, -10);
            $IncrementId = $order->getIncrementId();

            if($order->getPayment()->getMethod()== 'cashondelivery'){
                if (strlen($phoneNumber) === 10) {

                    $phoneNumber = \RLTSquare\SMS\Helper\Constants::COUNTRY_CODE . $phoneNumber;
                    $text = $this->scopeConfig->getValue(
                        'general/api_credentials/sms_text'
                    );
                    $confirmationUrl = $this->storeManager->getStore()->getBaseUrl()."sms/index/confirm?orderId=".$orderId;
                    $text = 'Dear+'. $firstName . '+' . $lastName. ',+Your+J.+order+is+placed+for+order+no.+';
                    $text = $text . $IncrementId . '.+Kindly+click+this+to+verify+your+order+'.$confirmationUrl;

                    try {
                        $sessionId = $this->sendSMS->getSessionId();
                        if (isset($sessionId)) {
                            $isSuccessMessage = $this->sendSMS->sendMessage(
                                $sessionId,
                                $phoneNumber,
                                $text
                            );
                        }
                    } catch (\Exception $exception) {

                    }
                } else {
                    $this->messageManager->addError('Phone number of order is not valid.');
                }
            }
        }

        $this->messageManager->addSuccess('SMS sent to selected orders orders.');
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}