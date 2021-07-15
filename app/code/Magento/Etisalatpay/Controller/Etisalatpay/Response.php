<?php

namespace Magento\Etisalatpay\Controller\Etisalatpay;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use \Magento\Etisalatpay\Helper\Data ;

class Response extends \Magento\Etisalatpay\Controller\Etisalatpay implements CsrfAwareActionInterface
{

    protected $helperData;
    protected $_paymentPlugin;
    protected $_scopeConfig;
    protected $_session;
    protected $_order;
    protected $messageManager;
    protected $_redirect;
    protected $_orderId;
    protected $_storeManager;
    protected $_orderManagement;
    protected $_url;
    protected $moduleReader;
    protected  $orderSender;

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Etisalatpay\Helper\Data $helperData,
        \Magento\Etisalatpay\Model\Payment $paymentPlugin,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $session,
        \Magento\Sales\Model\Order $order,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Framework\Module\Dir\Reader $reader,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender

    ) {
        $this->helperData = $helperData;
        $this->_paymentPlugin   = $paymentPlugin;
        $this->_scopeConfig     = $scopeConfig;
        $this->_session         = $session;
        $this->_order 		    = $order;
        $this->_storeManager    = $storeManager;
        $this->_orderManagement = $orderManagement;
        $this->messageManager   = $context->getMessageManager();
        $this->_url             = $context->getUrl();
        $this->moduleReader     = $reader;
        $this->orderSender      = $orderSender;
        parent::__construct($context,
            $paymentPlugin,
            $scopeConfig,
            $session,$order,
            $storeManager,
            $orderManagement,
            $reader,
            $orderSender
        );

    }

    /*
     * Finalization call
     */
    public function execute()
    {

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/TransactionDetails.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $logger->info("--------Initialize the Transaction Finalization Process---------");


        $response = $this->getRequest()->getPostValue();

        $etisalat['TransactionID'] = $response['TransactionID'];

        $logger->info("TransactionID - " . $etisalat['TransactionID']);

        $result = $this->etisalatCurl($etisalat, 'Finalization');

        $response = json_decode($result, true);
        $orderId = $this->getRequest()->getParam('order_id');

        $logger->info("Order ID - " . $orderId);

        $logger->info("Response Code - " . $response['Transaction']['ResponseCode']);
        //$logger->info("Response Class - " . $response['Transaction']['ResponseClass']);
        $logger->info("Response Description - " . $response['Transaction']['ResponseDescription']);
        //$logger->info("Response Class Description - " . $response['Transaction']['ResponseClassDescription']);
        $logger->info("Amount - " . $response['Transaction']['Amount']['Value']);
        //$logger->info("Payer - " . $response['Transaction']['Payer']);
        //$logger->info("Card type - " . $response['Transaction']['CardType']);
        //$logger->info("Unique Id - " . $response['Transaction']['UniqueID']);

        $order = $this->_order->loadByIncrementId($orderId);

        if ($response['Transaction']['ResponseCode'] == 0 && $response['Transaction']['ApprovalCode'] !=null) {
            //success | proceed

            //load order status from config
            $passed_status = $this->_scopeConfig->getValue('payment/etisalatpay/payment_success_order_status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            //$message = 'Payment has been processed successfully';

            $message = "Payment has been processed successfully" . "\r\n" . "TransactionID: " . $etisalat['TransactionID'] . "\r\n" . "Response Code: " . $response['Transaction']['ResponseCode'] . "\r\n" . "ApprovalCode: " . $response['Transaction']['ApprovalCode'];

            //$this->_orderManagement->unHold($order->getId());
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)->setStatus('confirmed');
            $order->addStatusHistoryComment('Gateway has confirmed the payment.', 's_order_confirmed');
            $order->addStatusHistoryComment(nl2br($message));

            $order->save();
            $this->orderSender->send($order);

            $logger->info("--------Process Completed with success---------");

            return $this->_redirect('checkout/onepage/success');

        } else if ($response['Transaction']['ResponseCode'] == 14) {

            //payment error | failure

            $errorMessage = _(' Invalid Card Number <br/>
            Dear customer, please check if your card is active or for more details please contact your bank.<br/>
            If the issue persists you can call us or drop us an email at payment.eshop@junaidjamshed.com');

            //$this->_orderManagement->unHold($orderId);
            //$this->_orderManagement->cancel($orderId);

            $order->registerCancellation(nl2br($errorMessage))->save();
            $order->save();

            $this->_session->restoreQuote();

            $this->messageManager->addError(nl2br($errorMessage));

            $logger->info("--------Process Completed with failure---------");

            return $this->_redirect('checkout/cart');


        } else if ($response['Transaction']['ResponseCode'] == 15) {

            //payment error | failure

            $errorMessage = _('No Issuer <br/>
            Dear customer, please check if your card is active or for more details please contact your bank.<br/>
            If the issue persists you can call us or drop us an email at payment.eshop@junaidjamshed.com');

            //$this->_orderManagement->unHold($orderId);
            //$this->_orderManagement->cancel($orderId);

            $order->registerCancellation(nl2br($errorMessage))->save();
            $this->helperData->sendEmail($order);
            $order->save();

            $this->_session->restoreQuote();

            $this->messageManager->addError(nl2br($errorMessage));

            $logger->info("--------Process Completed with failure---------");

            return $this->_redirect('checkout/cart');


        } else if ($response['Transaction']['ResponseCode'] == 13) {

            //payment error | failure

            $errorMessage = _('Invalid Amount <br/>
            Dear customer, please check if your card is active or for more details please contact your bank.<br/>
            If the issue persists you can call us or drop us an email at payment.eshop@junaidjamshed.com');

            //$this->_orderManagement->unHold($orderId);
            //$this->_orderManagement->cancel($orderId);

            $order->registerCancellation(nl2br($errorMessage))->save();
            $order->save();

            $this->_session->restoreQuote();

            $this->messageManager->addError(nl2br($errorMessage));

            $logger->info("--------Process Completed with failure---------");

            return $this->_redirect('checkout/cart');


        } else if ($response['Transaction']['ResponseCode'] == 12) {

            //payment error | failure

            $errorMessage = _('Invalid Transaction <br/>
            Dear customer, please check if your card is active or for more details please contact your bank.<br/>
            If the issue persists you can call us or drop us an email at payment.eshop@junaidjamshed.com');

            //$this->_orderManagement->unHold($orderId);
            //$this->_orderManagement->cancel($orderId);

            $order->registerCancellation(nl2br($errorMessage))->save();
            $order->save();

            $this->_session->restoreQuote();

            $this->messageManager->addError(nl2br($errorMessage));

            $logger->info("--------Process Completed with failure---------");

            return $this->_redirect('checkout/cart');


        } else if ($response['Transaction']['ResponseCode'] == 6) {

            //payment error | failure

            $errorMessage = _('Error <br/>
            Dear customer, please check if your card is active or for more details please contact your bank.<br/>
            If the issue persists you can call us or drop us an email at payment.eshop@junaidjamshed.com');

            //$this->_orderManagement->unHold($orderId);
            //$this->_orderManagement->cancel($orderId);

            $order->registerCancellation(nl2br($errorMessage))->save();
            $order->save();

            $this->_session->restoreQuote();

            $this->messageManager->addError(nl2br($errorMessage));

            $logger->info("--------Process Completed with failure---------");

            return $this->_redirect('checkout/cart');


        } else if ($response['Transaction']['ResponseCode'] == 5) {

            //payment error | failure

            $errorMessage = _(' Do Not Honour <br/>
            Dear customer, please check if your card is active or for more details please contact your bank.<br/>
            If the issue persists you can call us or drop us an email at payment.eshop@junaidjamshed.com');

            //$this->_orderManagement->unHold($orderId);
            //$this->_orderManagement->cancel($orderId);

            $order->registerCancellation(nl2br($errorMessage))->save();
            $order->save();

            $this->_session->restoreQuote();

            $this->messageManager->addError(nl2br($errorMessage));

            $logger->info("--------Process Completed with failure---------");

            return $this->_redirect('checkout/cart');


        } else {

            //payment error | failure

            $errorMessage = _(' Payment Canceled by Customer  <br/>
            Dear customer, please check if your card is active or for more details please contact your bank.<br/>
            If the issue persists you can call us or drop us an email at payment.eshop@junaidjamshed.com');

            //$this->_orderManagement->unHold($orderId);
            //$this->_orderManagement->cancel($orderId);

            $order->registerCancellation(nl2br($errorMessage))->save();
            $order->save();

            $this->_session->restoreQuote();

            $this->messageManager->addError(nl2br($errorMessage));

            $logger->info("--------Process Completed with failure---------");

            return $this->_redirect('checkout/cart');


        }


    }
}
