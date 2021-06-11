<?php
namespace RLTSquare\ReturnOrder\Controller\Adminhtml\Mail;

use Magento\Framework\UrlInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
class Send extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Magento_Sales::order_statuses';
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var Filter
     */
    protected $filter;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;
    protected $redirectUrl = '*/*/';

    private $transportBuilder;
    private $encryptor;
    private $urlBuilder;

    /**
     * Preparation constructor.
     * @param Context $context
     * @param Filter $filter
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(Context $context,
                                Filter $filter,
                                \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
                                \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
                                TransportBuilder $transportBuilder,
                                \Magento\Framework\Encryption\EncryptorInterface $encryptor,
                                \Magento\Framework\Url $urlBuilder)
    {
        parent::__construct($context);
        $this->transportBuilder = $transportBuilder;
        $this->collectionFactory = $orderCollectionFactory;
        $this->orderRepository   = $orderRepository;
        $this->filter            = $filter;
        $this->encryptor = $encryptor;
        $this->urlBuilder = $urlBuilder;
    }
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            //mass action
            $countPreparationOrder = 0;
            /** @var \Magento\Sales\Model\Order $order */
            foreach ($collection->getItems() as $order) {
                $email = $order->getBillingAddress()->getEmail();

                $encryptedOrderId = $this->encryptor->encrypt($order->getId());

                $orderConfirmationLink = $this->urlBuilder->getUrl('returnorder/order/confirm', ['_query' => array('order_id' => $encryptedOrderId)]);
                $parseDataVars = new \Magento\Framework\DataObject();
                $parseDataVars->setData(array('link' => $orderConfirmationLink, 'customername' => $order->getShippingAddress()->getName(), 'ordernumber' => $order->getIncrementId()));
                $this->sendEmail(77,1,$parseDataVars,$email);
            }

            $this->messageManager->addSuccessMessage(__('Email for confirmation sent to users'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($this->filter->getComponentRefererUrl() ?: 'sales/order/index');
            return $resultRedirect;
        }
        catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath($this->redirectUrl);
        }
    }

    public function sendEmail($templateId =1, $storeId =null,$templateParams, $sendTo) //pass the value of custom Email template ID, store ID, and template parameter in this function
    {
        // Sender Name and Email Id
        $sender = [
            'name' => 'J. Junaid Jamshed',
            'email' => 'eshop@junaidjamshed.com',
        ];
        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId])
            ->setTemplateVars(['data'=> $templateParams])
            ->setFrom($sender)
            ->addTo($sendTo)
            ->getTransport();
        $transport->sendMessage();
    }
}