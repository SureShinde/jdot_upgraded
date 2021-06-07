<?php
/**
 * Copyright © 2015 Customer Paradigm. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CustomerParadigm\OrderComments\Controller\Index;

class Save extends \Magento\Framework\App\Action\Action
{
    protected $_customerRepositoryInterface;

    protected $date;
    protected $timezone;

    const XML_PATH_EMAIL_RECIPIENT = 'design/email/send_email';
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;



    /** @var \Magento\Sales\Model\Order\Status\HistoryFactory $historyFactory */
    protected $historyFactory;
    /** @var \Magento\Sales\Model\OrderFactory $orderFactory */
    protected $orderFactory;

    /**
     * @param \Magento\Sales\Model\Order\Status\HistoryFactory $historyFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        \Magento\Sales\Model\Order\Status\HistoryFactory $historyFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Escaper $escaper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->historyFactory = $historyFactory;
        $this->orderFactory = $orderFactory;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_escaper = $escaper;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->date = $date;
        $this->timezone = $timezone;
    }

    /**
     * @param \Magento\Checkout\Model\PaymentInformationManagement $subject
     * @param \Closure $proceed
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface $billingAddress
     *
     * @return int $orderId
     */
    public function execute()
    {
        /** @param string $comment */



        $orderId = $this->getRequest()->getParam('order_id');
        $customer_request = strip_tags($this->getRequest()->getParam('comment-code'));
        $comment = NULL;
        // get JSON post data
        //$request_body = file_get_contents('php://input');
        // decode JSON post data into array
        //$data = json_decode($request_body, true);
        // get order comments
        if (isset ($_POST['comment-code'])) {
            // make sure there is a comment to save
            if ($_POST['comment-code']) {
                // remove any HTML tags
                $comment = strip_tags ($_POST['comment-code']);
                $comment = $comment;
            }
        }

        // run parent method and capture int $orderId
        //$orderId = $proceed($cartId, $paymentMethod, $billingAddress);



        // if $comments
        if ($comment) {
            /** @param \Magento\Sales\Model\OrderFactory $order */
            $order = $this->orderFactory->create()->load($orderId);
            // make sure $order exists
            if ($order->getData('entity_id')) {
                /** @param string $status */
                $status = $order->getData('status');
                /** @param \Magento\Sales\Model\Order\Status\HistoryFactory $history */
                $history = $this->historyFactory->create();
                // set comment history data
                $history->setData('comment', $comment);
                $history->setData('parent_id', $orderId);
                $history->setData('is_visible_on_front', 1);
                $history->setData('is_customer_notified', 1);
                $history->setData('entity_name', 'order');
                $history->setData('status', $status);
                $history->setData('order_from', 'frontend');
                $history->save();
            }
        }
        //$to       = $this->scopeConfig->getValue('trans_email/ident_sales/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        //$subject  = "Customer Order Comment for Order #".$order->getIncrementId();
        //$message  = "<h1 style='font-size:13px; font-weight:normal; line-height:22px; margin:0 0 11px 0;'>Hello,</h1><p>You just recieved below customer comment for order #".$order->getIncrementId()."</p><strong>Customer Order Comment</strong><br/>".nl2br($customer_request);
        //$header   = "MIME-Version: 1.0\r\n";
        //$header  .= "Content-type: text/html; charset: utf8\r\n";

        //mail($to, $subject, $message, $header);




        $this->inlineTranslation->suspend();
        try {
            $adminemail = ($this->scopeConfig->getValue('trans_email/ident_sales/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

            $sender = [
                'name' => $this->_escaper->escapeHtml($adminemail),
                'email' => $this->_escaper->escapeHtml($adminemail)
            ];

            $transport = $this->_transportBuilder
                ->setTemplateIdentifier('send_email_email_template') // this code we have mentioned in the email_templates.xml
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // this is using frontend area to get the template file
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars(['data' => $order, 'history' => $history, 'adminemail' => $adminemail])
                ->setFrom($sender)


                ->addTo('salikgadit@uigmts.com','Salik')
                ->addTo(['faheem@junaidjamshed.com' => 'Faheem','yamnamukry@junaidjamshed.com' => 'Yamna', 'ecommercejjcs@gmail.com' => 'EcommerceJJ'])
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();

            $this->messageManager->addSuccessMessage('Order comment saved and email sent to admin.');
            $this->_redirect('sales/order/view/order_id/'.$orderId.'');


            return ;

        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addErrorMessage(
                __('We can\'t process your request right now. Sorry, that\'s all we know.'.$e->getMessage())
            );
            $this->_redirect('sales/order/view/order_id/'.$orderId.'');
            return;
        }
    }
}
