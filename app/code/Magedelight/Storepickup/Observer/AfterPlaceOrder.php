<?php
namespace Magedelight\Storepickup\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use \Magento\Framework\Mail\Template\TransportBuilder;
use \Magento\Framework\Translate\Inline\StateInterface;
use Psr\Log\LoggerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magedelight\Storepickup\Model\StorelocatorFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Model\Order\Address\Renderer;

class AfterPlaceOrder implements ObserverInterface
{
    protected $transportBuilder;
    protected $_logLoggerInterface;
    protected $scopeConfig;
    protected $orderRepository;
     /**
     * Event manager
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;
    /**
     * @var Renderer
     */
    protected $addressRenderer;
    /**
     * @var \Magento\Payment\Helper\Data
     */
    private $paymentHelper;
    /**
     * Order Model
     *
     * @var \Magento\Sales\Model\Order $order
     */
    protected $order;
    protected $modelStorelocatorFactory;

    public function __construct(
    TransportBuilder $transportBuilder,
    LoggerInterface $logLoggerInterface,
    OrderRepositoryInterface $OrderRepositoryInterface,
    \Magento\Sales\Model\Order $order,
     StorelocatorFactory $modelStorelocatorFactory,
    ScopeConfigInterface $scopeConfig,
    ManagerInterface $eventManager,
    Renderer $addressRenderer,
    \Magento\Payment\Helper\Data $paymentHelper,
    \Magento\Sales\Model\Order\Email\Container\CreditmemoIdentity $identityContainer)
    {
        $this->transportBuilder = $transportBuilder;
        $this->_logLoggerInterface = $logLoggerInterface;
        $this->orderRepository = $OrderRepositoryInterface;
        $this->order = $order;
        $this->_modelstorelocatorFactory = $modelStorelocatorFactory;
        $this->scopeConfig = $scopeConfig;
        $this->eventManager = $eventManager;
        $this->addressRenderer = $addressRenderer;
        $this->paymentHelper = $paymentHelper;
        $this->identityContainer = $identityContainer;
    }


    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try
        {
            $adminemail = $this->scopeConfig->getValue('trans_email/ident_general/email',ScopeInterface::SCOPE_STORE);
            $adminname  = $this->scopeConfig->getValue('trans_email/ident_general/name',ScopeInterface::SCOPE_STORE);            
        
            $orderId = $observer->getEvent()->getOrderIds()[0];
            $order = $this->orderRepository->get($orderId);      
            if($order->getShippingMethod() == 'storepickup_storepickup')
            {
                $storeId = $order->getShippingAddress()->getMiddleName();
                $storelocatorModel = $this->_modelstorelocatorFactory->create();
                $storelocatorCollection = $storelocatorModel->getCollection();
                $storelocatorCollection->addFieldToFilter('storelocator_id', $storeId);
                $storelocatorCollection->addFieldToSelect('storeemail');
                $storelocatorData = $storelocatorCollection->getData();

                $storeEmail = $storelocatorData[0]['storeemail'];

                    $templateVars =array();
                    $emailTemplateVariables = array();
                    $myvar = $order;
                    $emailTempVariables['order'] = $myvar;
                    $emailTempVariables['storename'] = 'mystore';
                    $emailTempVariables['myvar1'] = 'sdsd';
                    $emailTempVariables['myvar2'] = 'ertetre';

                    $templateVars = array(
                        'store' => $order->getShippingAddress()->getFirstName(),
                        'message'   => 'We processed your order ID We will contact you soon in mail for the acknowledgement if you not receive mail within 4 hours please get help from support@xxx.com'
                    );
                    $transport = [
                            'order' => $order,
                            'billing' => $order->getBillingAddress(),
                            'shipping' => $order->getShippingAddress(),
                            'payment_html' => $this->getPaymentHtml($order),
                            'store' => $order->getStore(),
                            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
                            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
                        ];

                    $transport = new \Magento\Framework\DataObject($transport);

                    $this->eventManager->dispatch(
                        'email_order_set_template_vars_before',
                        ['sender' => $this, 'transport' => $transport]
                    );

                    $senderName = $adminname;
                    $recieveremail= $storeEmail;
                    $senderEmail = $adminemail;

                    $postObject = new \Magento\Framework\DataObject();
                    $postObject->setData($emailTempVariables);

                    $sender = [
                                'name' => $senderName,
                                'email' => $senderEmail,
                              ];

                    $transport = $this->transportBuilder->setTemplateIdentifier('sales_order_template')
                    ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])
                    ->setTemplateVars($transport->getData())
                    ->setFrom($sender)
                    ->addTo($recieveremail)
                    ->setReplyTo($senderEmail)            
                    ->getTransport();               
                    $transport->sendMessage();
            }
        } catch(\Exception $e){
            $this->_logLoggerInterface->debug($e->getMessage());
        }
    }
    
    /**
     * @param Order $order
     * @return string|null
     */
    protected function getFormattedShippingAddress($order)
    {
        return $order->getIsVirtual()
            ? null
            : $this->addressRenderer->format($order->getShippingAddress(), 'html');
    }

    /**
     * @param Order $order
     * @return string|null
     */
    protected function getFormattedBillingAddress($order)
    {
        return $this->addressRenderer->format($order->getBillingAddress(), 'html');
    }
    
    /**
     * Returns payment info block as HTML.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return string
     */
    private function getPaymentHtml(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $this->identityContainer->getStore()->getStoreId()
        );
    }
}