<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace RLTSquare\OrderState\Controller\Adminhtml\Order\Shipment;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order\Shipment\Validation\QuantityValidator;

/**
 * Class Save
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save
{

    /**
     * @var \Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface
     */
    protected $shipmentValidator;

    /**
     * @var \Magento\Sales\Model\Order
     */

    protected $order;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader
     * @param \Magento\Shipping\Model\Shipping\LabelGenerator $labelGenerator
     * @param \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender
     * @param \Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface|null $shipmentValidator
     */

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Sales\Model\Order $order, \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader, \Magento\Shipping\Model\Shipping\LabelGenerator $labelGenerator, \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender, ?\Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface $shipmentValidator = null)
    {
        $this->order = $order;
        $this->shipmentValidator = $shipmentValidator ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface::class);
        parent::__construct($context, $shipmentLoader, $labelGenerator, $shipmentSender, $shipmentValidator);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->addErrorMessage(__('We can\'t save the shipment right now.'));
            return $resultRedirect->setPath('sales/order/index');
        }

        $data = $this->getRequest()->getParam('shipment');

        if (!empty($data['comment_text'])) {
            $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setCommentText($data['comment_text']);
        }

        $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];
        $responseAjax = new \Magento\Framework\DataObject();
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->order->load($orderId);
        if ($order->getPayment()->getMethod() == 'etisalatpay') {
            if ($order->getStatus() == 'capture') {

                try {
                    $this->shipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
                    $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
                    $this->shipmentLoader->setShipment($data);
                    $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
                    $shipment = $this->shipmentLoader->load();
                    if (!$shipment) {
                        return $this->resultFactory->create(ResultFactory::TYPE_FORWARD)->forward('noroute');
                    }

                    if (!empty($data['comment_text'])) {
                        $shipment->addComment(
                            $data['comment_text'],
                            isset($data['comment_customer_notify']),
                            isset($data['is_visible_on_front'])
                        );

                        $shipment->setCustomerNote($data['comment_text']);
                        $shipment->setCustomerNoteNotify(isset($data['comment_customer_notify']));
                    }
                    $validationResult = $this->shipmentValidator->validate($shipment, [QuantityValidator::class]);

                    if ($validationResult->hasMessages()) {
                        $this->messageManager->addErrorMessage(
                            __("Shipment Document Validation Error(s):\n" . implode("\n", $validationResult->getMessages()))
                        );
                        return $resultRedirect->setPath('*/*/new', ['order_id' => $this->getRequest()->getParam('order_id')]);
                    }
                    $shipment->register();

                    $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));

                    if ($isNeedCreateLabel) {
                        $this->labelGenerator->create($shipment, $this->_request);
                        $responseAjax->setOk(true);
                    }

                    $this->_saveShipment($shipment);

                    if (!empty($data['send_email'])) {
                        $this->shipmentSender->send($shipment);
                    }

                    $shipmentCreatedMessage = __('The shipment has been created.');
                    $labelCreatedMessage = __('You created the shipping label.');

                    $this->messageManager->addSuccessMessage(
                        $isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage : $shipmentCreatedMessage
                    );
                    $this->_objectManager->get(\Magento\Backend\Model\Session::class)->getCommentText(true);
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    if ($isNeedCreateLabel) {
                        $responseAjax->setError(true);
                        $responseAjax->setMessage($e->getMessage());
                    } else {
                        $this->messageManager->addErrorMessage($e->getMessage());
                        return $resultRedirect->setPath('*/*/new', ['order_id' => $this->getRequest()->getParam('order_id')]);
                    }
                } catch (\Exception $e) {
                    $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                    if ($isNeedCreateLabel) {
                        $responseAjax->setError(true);
                        $responseAjax->setMessage(__('An error occurred while creating shipping label.'));
                    } else {
                        $this->messageManager->addErrorMessage(__('Cannot save shipment.'));
                        return $resultRedirect->setPath('*/*/new', ['order_id' => $this->getRequest()->getParam('order_id')]);
                    }
                }
                if ($isNeedCreateLabel) {
                    return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setJsonData($responseAjax->toJson());
                }
                return $resultRedirect->setPath('sales/order/view', ['order_id' => $shipment->getOrderId()]);
            } else {
                $this->messageManager->addErrorMessage('Capture status required for shipping');
                return $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
            }
        } else {
            try {
                $this->shipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
                $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
                $this->shipmentLoader->setShipment($data);
                $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
                $shipment = $this->shipmentLoader->load();
                if (!$shipment) {
                    return $this->resultFactory->create(ResultFactory::TYPE_FORWARD)->forward('noroute');
                }

                if (!empty($data['comment_text'])) {
                    $shipment->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );

                    $shipment->setCustomerNote($data['comment_text']);
                    $shipment->setCustomerNoteNotify(isset($data['comment_customer_notify']));
                }
                $validationResult = $this->shipmentValidator->validate($shipment, [QuantityValidator::class]);

                if ($validationResult->hasMessages()) {
                    $this->messageManager->addErrorMessage(
                        __("Shipment Document Validation Error(s):\n" . implode("\n", $validationResult->getMessages()))
                    );
                    return $resultRedirect->setPath('*/*/new', ['order_id' => $this->getRequest()->getParam('order_id')]);
                }
                $shipment->register();

                $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));

                if ($isNeedCreateLabel) {
                    $this->labelGenerator->create($shipment, $this->_request);
                    $responseAjax->setOk(true);
                }

                $this->_saveShipment($shipment);

                if (!empty($data['send_email'])) {
                    $this->shipmentSender->send($shipment);
                }

                $shipmentCreatedMessage = __('The shipment has been created.');
                $labelCreatedMessage = __('You created the shipping label.');

                $this->messageManager->addSuccessMessage(
                    $isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage : $shipmentCreatedMessage
                );
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->getCommentText(true);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                if ($isNeedCreateLabel) {
                    $responseAjax->setError(true);
                    $responseAjax->setMessage($e->getMessage());
                } else {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    return $resultRedirect->setPath('*/*/new', ['order_id' => $this->getRequest()->getParam('order_id')]);
                }
            } catch (\Exception $e) {
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                if ($isNeedCreateLabel) {
                    $responseAjax->setError(true);
                    $responseAjax->setMessage(__('An error occurred while creating shipping label.'));
                } else {
                    $this->messageManager->addErrorMessage(__('Cannot save shipment.'));
                    return $resultRedirect->setPath('*/*/new', ['order_id' => $this->getRequest()->getParam('order_id')]);
                }
            }
            if ($isNeedCreateLabel) {
                return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setJsonData($responseAjax->toJson());
            }
            return $resultRedirect->setPath('sales/order/view', ['order_id' => $shipment->getOrderId()]);
        }

    }
}
