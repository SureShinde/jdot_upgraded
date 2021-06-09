<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace RLTSquare\CancelReasonOnEditOrder\Controller\Adminhtml\Order\Edit;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

class Start extends \Magento\Sales\Controller\Adminhtml\Order\Edit\Start implements HttpPostActionInterface
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::actions_edit';

    /**
     * Start edit order initialization
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */

    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory
    )
    {
        $this->orderFactory = $orderFactory;
        parent::__construct($context, $productHelper, $escaper, $resultPageFactory, $resultForwardFactory);
    }

    public function execute()
    {
        $this->_getSession()->clearStorage();
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->_objectManager->create(\Magento\Sales\Model\Order::class)->load($orderId);
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // saving post parameters in database
        $post = $this->getRequest()->getParams();
        $route_name = $this->getRequest()->getRouteName();
        $moduleName = $this->getRequest()->getModuleName();
        $controller_name = $this->getRequest()->getControllerName();

        if ($route_name == "cancelreasononeditorder") {
            if (isset($post)) {
                $order_edit_reason = $post['reason_edit_order'];
                $order_edit_description = $post['coment_edit_order'];

                $order_id = $post['order_id']; //for updating the record
                $order = $this->orderFactory->create();
                if ($order_id) {
                    $order->load($order_id);
                }
                try {
                    $order->setOrderEditReason($order_edit_reason);
                    $order->setOrderEditDescription($order_edit_description);
                    $order->save();
                    $this->messageManager->addSuccessMessage(__('Data has been saved.'));
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\RuntimeException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the data.'));
                }
            }
        }

        try {
            if ($order->getId()) {
                $this->_getSession()->setUseOldShippingMethod(true);
                $this->_getOrderCreateModel()->initFromOrder($order);
                $resultRedirect->setPath('sales/*');
            } else {
                $resultRedirect->setPath('sales/order/');
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
            $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
        }
        return $resultRedirect;
    }

}
