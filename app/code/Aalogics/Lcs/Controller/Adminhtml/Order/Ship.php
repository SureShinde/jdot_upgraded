<?php

/**
 * Copyright © Aalogics Ltd. All rights reserved.
 *
 * @package    Aalogics _Dropship
 * @copyright  Copyright © Aalogics Ltd (http://www.aalogics.com)
 */

namespace Aalogics\Lcs\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;

class Ship extends \Magento\Framework\App\Action\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aalogics_Lcs::ship';

    protected $_lcsOrderManager;

    protected $_lcsHelper;
    protected $orderRepository;
    protected $_resultJsonFactory;
    protected $_coreRegistry = null;

    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Aalogics\Lcs\Model\OrderManager $lcsModel,
        \Aalogics\Lcs\Helper\Data $lcsHelper,
        OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_lcsOrderManager   = $lcsModel;
        $this->_lcsHelper         = $lcsHelper;
        $this->orderRepository    = $orderRepository;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Hold order
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $resultJson = $this->_resultJsonFactory->create();

        $city = $this->getRequest()->getParam('city_field');

        $shipOrders     = array();
        $alreadyShipped = array();

        if ($this->_lcsHelper->isEnabled()) {
            $order = $this->_initOrder();

            if ($order) {

                $shipment            = $order->getShipmentsCollection()->getFirstItem();
                $shipmentIncrementId = $shipment->getIncrementId();

                // Redirect to Order View
                $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getId()]);

                $redirectUrl = $this->_url->getUrl('sales/order/view', ['order_id' => $order->getId()]);

                if ($shipmentIncrementId == null) {
                    $shipOrders[] = $order;
                } else {
                    $alreadyShipped[] = $order;
                }

                $message = '';
                if (count($alreadyShipped) > 0) {
                    $message = __("Shipment has already been created for the selected order(s) : ");
                    for ($x = 0; $x < count($alreadyShipped); $x++) {
                        $message .= $alreadyShipped[$x]->getIncrementId();
                        if (!$x == (count($alreadyShipped) - 1)) {
                            $message .= ', ';
                        }

                    }

                }

                if (!empty($message)) {
                    $this->messageManager->addError($message);
                    return $resultJson->setData([
                        'returnUrl' => $redirectUrl,
                        'message'   => $message,
                    ]);
                }

                if ($this->_lcsHelper->isShippingEnabled()) {

                    $this->_lcsHelper->debug('massAction invoiceandShipAll start');
                    if ($order->getPayment()->getMethod() == 'etisalatpay') {
                        if ($order->getStatus() == 'capture') {
                            // Process Shipments
                            $result = $this->_lcsOrderManager->invoiceAndShipAll(
                                $shipOrders,
                                $this->_lcsHelper->getAdminField('lcs_inv_shipp_action/new_status'),
                                $this->_lcsHelper->getAdminField('lcs_inv_shipp_action/invoce_email'),
                                $this->_lcsHelper->getAdminField('lcs_inv_shipp_action/shipment_email'),
                                [],
                                [],
                                null,
                                $city
                            );
                            $this->addResultsToSession($result);

                            return $resultJson->setData([
                                'returnUrl' => $redirectUrl,
                                'message'   => $result,
                            ]);
                        }else{
                            $this->messageManager->addErrorMessage('Capture Status required for Shipment');
                        }
                    }else
                    {
                        $result = $this->_lcsOrderManager->invoiceAndShipAll(
                            $shipOrders,
                            $this->_lcsHelper->getAdminField('lcs_inv_shipp_action/new_status'),
                            $this->_lcsHelper->getAdminField('lcs_inv_shipp_action/invoce_email'),
                            $this->_lcsHelper->getAdminField('lcs_inv_shipp_action/shipment_email'),
                            [],
                            [],
                            null,
                            $city
                        );
                        $this->addResultsToSession($result);

                        return $resultJson->setData([
                            'returnUrl' => $redirectUrl,
                            'message'   => $result,
                        ]);
                    }

                    // Add results to session


                } else {
                    $this->messageManager->addError('LCS Shippement Not Enabled');

                    return $resultJson->setData([
                        'returnUrl' => $redirectUrl,
                        'message'   => 'LCS Shippement Not Enabled',
                    ]);
                }

                // return $resultRedirect;
            }

        } else {
            $this->messageManager->addError('LCS is Not Enabled');
            return $resultJson->setData([
                'returnUrl' => $redirectUrl,
                'message'   => 'LCS is Not Enabled',
            ]);
        }

        $resultRedirect->setPath('sales/*/');
        return $resultJson->setData([
            'returnUrl' => $redirectUrl,
            'message'   => '',
        ]);
        // return $resultRedirect;
    }

    /**
     * add both error and success message to admin session
     *
     * @param $result
     * @param $successMessage
     */
    public function addResultsToSession($result)
    {
        if (!empty($result['errors'])) {
            $this->messageManager->addError(implode('<br/>', $result['errors']));
        }
        if (!empty($result['successes'])) {
            $this->messageManager->addSuccess(implode(',', $result['successes']));
        }
    }

    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');

        try {
            $order = $this->orderRepository->get($id);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addError(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        } catch (InputException $e) {
            $this->messageManager->addError(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        $this->_coreRegistry->register('sales_order', $order);
        $this->_coreRegistry->register('current_order', $order);
        return $order;
    }

}
