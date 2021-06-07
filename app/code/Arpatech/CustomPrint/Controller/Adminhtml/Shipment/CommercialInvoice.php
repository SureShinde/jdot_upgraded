<?php
/**
 * Created by PhpStorm.
 * User: amber
 * Date: 26/04/17
 * Time: 5:52 PM
 */
namespace Arpatech\CustomPrint\Controller\Adminhtml\Shipment;

class CommercialInvoice extends \Magento\Backend\App\Action
{

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->_coreRegistry = $registry;
        $this->_resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }
    /**
     * init layout and set active for current menu
     *
     * @return Magestore_Shipment_Adminhtml_ShipmentController
     */
    public function execute()
    {
        $shipment_id = $this->getRequest()->getParam('shipment_id');
        if($shipment_id) {
            $shipment = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment')->load($shipment_id);
            $customer = $this->_objectManager->create('\Magento\Customer\Model\Customer')->load($shipment->getCustomerId());
            $order = $this->_objectManager->create('\Magento\Sales\Model\Order')->load($shipment->getOrderId());
            $priceHelper = $this->_objectManager->create('Magento\Framework\Pricing\Helper\Data'); // Instance of Pricing Helper
            $resultPage = $this->_resultPageFactory->create();
            $this->_coreRegistry->register('shipment', $shipment);
            $this->_coreRegistry->register('customer', $customer);
            $this->_coreRegistry->register('order', $order);
            $this->_coreRegistry->register('priceHelper', $priceHelper);
            return $resultPage;
        }
    }
}
