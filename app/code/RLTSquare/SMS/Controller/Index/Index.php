<?php

namespace RLTSquare\SMS\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 * @package RLTSquare\SMS\Controller\Index
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \RLTSquare\SMS\Helper\Api\SendMessage
     */
    private $sendMessage;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $pageFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param \RLTSquare\SMS\Helper\Api\SendMessage $smsSessionValue
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        \RLTSquare\SMS\Helper\Api\SendMessage $smsSessionValue,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Math\Random $mathRandom
    ) {
        parent::__construct($context);
        $this->sendMessage = $smsSessionValue;
        $this->orderRepository = $orderRepository;
        $this->pageFactory = $pageFactory;
        $this->mathRandom = $mathRandom;
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
        $smsCode = $this->getRequest()->getParam('smsVerificationCode');

        if (isset($orderId, $smsCode)) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderRepository->get($orderId);
        } else {
            $resultJson->setData([
                'verified' => '0'
            ]);
            return $resultJson;
        }

        $phoneCode = $order->getPhoneCode();

        if ($order->getEcsPhoneNumberStatus() === \RLTSquare\SMS\Helper\Constants::PHONE_STATUS_NOT_VERIFIED) {
            if ($smsCode === $phoneCode) {
                try {
                    $order->setEcsPhoneNumberStatus(\RLTSquare\SMS\Helper\Constants::PHONE_STATUS_VERIFIED);
                    $order->setState("new")->setStatus("verified");
                    $counter = $order->getPhoneCodeCounter();
                    $counter += 1;
                    $order->setPhoneCodeCounter($counter);
                    $order->save();
                    $resultJson->setData([
                        'verified' => '1'
                    ]);
                    return $resultJson;
                } catch (\Exception $exception) {
                    $resultJson->setData([
                        'verified' => '0'
                    ]);
                    return $resultJson;
                }
            }
        }
        $resultJson->setData([
            'verified' => '0'
        ]);
        return $resultJson;
    }
}
