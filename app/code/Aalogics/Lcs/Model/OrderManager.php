<?php
namespace Aalogics\Lcs\Model;

use Magento\Framework\Event\ObserverInterface;
use \Aalogics\Lcs\Helper\Data;
/**
 * @author     Aalogics
 * @package    Aalogics_Lcs
 * @copyright  Copyright (c) Aalogics
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class OrderManager{

	/**
	 * @var EventManager
	 */
	private $eventManager;
	
	protected $_helper;
	
    protected $_transactions;

    protected $_invoiceSer;

    protected $_invoiceSender;
	
	public function __construct(
		\Magento\Framework\Event\Manager $eventManager,
		\Aalogics\Lcs\Helper\Data $lcsHelper,
        \Magento\Framework\DB\TransactionFactory $transactions,
        \Magento\Sales\Model\Service\InvoiceService $invoiceSer,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
	){
		
		$this->_helper = $lcsHelper;
		$this->eventManager = $eventManager;
        $this->_transactions = $transactions;
        $this->_invoiceSer = $invoiceSer;
        $this->_invoiceSender = $invoiceSender;
	}
	

    /**
     * Invoice and ship all selected orders
     *
     * @param        $orderIds
     * @param string $newOrderStatus
     * @param bool   $sendInvoiceEmail
     * @param bool   $sendShippingEmail
     * @param array  $allTrackingNrs
     * @param array  $allCarrierCodes
     *
     * @return array
     */
    public function invoiceAndShipAll(
        $orders, $newOrderStatus = '', $sendInvoiceEmail = false, $sendShippingEmail = false, $allTrackingNrs = array(), $allCarrierCodes = array(), $costcenter = NULL
    ) {
        $this->_helper->debug('invoiceAndShipAll Start');
        $successes = array();
        $errors = array();

        if (is_array($orders) && !empty($orders)) {
            if (count($orders) > 0) {
                foreach ($orders as $order) {

                    $orderIncrementId = $order->getIncrementId();
                    
                    // $trackingNrs = $this->getTrackingNrsForOrder($order->getId(), $allTrackingNrs);
                    // $carrierCode = $this->getCarrierForOrder($order->getId(), $allCarrierCodes);
                    
                    try {
                        
                        $orderCanInvoice = $order->canInvoice();
                        $orderCanShip = $order->canShip();


                        /******** Create Order Shipment *********/ 
						if($orderCanShip) {
							$this->_helper->debug('invoiceAndShipAll Ship Start');
							$observerShip = $this->eventManager->dispatch('aalcs_lcs_ship',['order'=>$order]);
                        }

                        /****** Create Order Invoice ******/
                        if($orderCanInvoice) {
                            $observerInvoice = $this->eventManager->dispatch('aalcs_lcs_invoice',['order'=>$order]);
                        }

						$successes[] = $orderIncrementId. ": Invoiced and Shipped";

                    } catch (\Exception $e) {
                        $errors[] = $orderIncrementId . ": " . $e->getMessage();
                        $this->_helper->debug('Exception: ',$e->getMessage());
                    }
                }
            }
        }
        return array('errors' => $errors, 'successes' => $successes);
    }
    
    /**
     * Invoice and ship all selected orders
     *
     * @param        $orderIds
     * @param string $newOrderStatus
     * @param bool   $sendInvoiceEmail
     * @param bool   $sendShippingEmail
     * @param array  $allTrackingNrs
     * @param array  $allCarrierCodes
     *
     * @return array
     */
    public function shipAll(
    		$orders, $newOrderStatus = '', $sendInvoiceEmail = false, $sendShippingEmail = false, $allTrackingNrs = array(), $allCarrierCodes = array(), $costcenter = NULL, $city = NULL
    ) {
    	$this->_helper->debug('shipAll Start');
    	$successes = array();
    	$errors = array();
    
    	if (is_array($orders) && !empty($orders)) {
    		if (count($orders) > 0) {
    			foreach ($orders as $order) {
    
    				$orderIncrementId = $order->getIncrementId();
    
    				// $trackingNrs = $this->getTrackingNrsForOrder($order->getId(), $allTrackingNrs);
    				// $carrierCode = $this->getCarrierForOrder($order->getId(), $allCarrierCodes);
    
    				try {
    
    					$orderCanShip = $order->canShip();
    
    					/******** Create Order Shipment *********/
    					if($orderCanShip) {
    						$this->_helper->debug('Ship Start');
    						$this->eventManager->dispatch('aalcs_lcs_ship',['order'=>$order,'destCity'=>$city]);
    					}
    
    					$successes[] = $orderIncrementId. ": Shipped";
    
    				} catch (\Exception $e) {
    					$errors[] = $orderIncrementId . ": " . $e->getMessage();
    					$this->_helper->debug('Exception: ',$e->getMessage());
    				}
    			}
    		}
    	}
    	return array('errors' => $errors, 'successes' => $successes);
    }


    /**
     * @param $orderId
     * @param $carrierCodes
     *
     * @return mixed
     */
    public function getCarrierForOrder($orderId, $carrierCodes)
    {
        if (isset($carrierCodes[$orderId])) {
            return $carrierCodes[$orderId];
        } else {
            return $this->_helper->getAdminField('lcs_cod/preselectedcarrier');
        }
    }

    /**
     * @param $orderId
     * @param $trackingNumbers
     *
     * @return array|bool
     */
    public function getTrackingNrsForOrder($orderId, $trackingNumbers)
    {
        if (!empty($trackingNumbers[$orderId])) {
            return explode(';', $trackingNumbers[$orderId]);
        }
        return false;
    }


    /**
     * Save all objects together in one transaction
     *
     * @param $objects
     *
     * @throws \Exception
     * @throws bool
     */
    protected function _saveAsTransaction($objects)
    {
        $transaction = $this->_transactions->create();
        foreach ($objects as $object) {
            $transaction->addObject($object);
        }
        $transaction->save();
    }

    /**
     * Invoice all open items for a given order
     *
     * @param \Magento\Sales\Model\Order $order
     * @param bool                   $newStatus
     * @param bool                   $email
     *
     * @return \Mage_Sales_Model_Order_Invoice
     */
    public function invoice(\Magento\Sales\Model\Order $order, $newStatus = false, $email = false)
    {

        $invoice = $this->_invoiceSer->prepareInvoice($order);
        
        if (!$invoice->getTotalQty()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Cannot create an invoice without products.'));
        }
        
        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
        
        $invoice->register();
        
        $order->setIsInProcess(true);

        $this->_saveAsTransaction(array($order, $invoice));

        if (!$newStatus) {
            $order->addStatusToHistory(false, __('Created Invoice'), $email);
        }

        if ($newStatus) {
            $this->changeOrderStatus(
                $order, $newStatus, __('Created Invoice'), $email
            );
        }

        return $invoice;
    }

    
    /**
     * Update order to new status, optional comment
     *
     * @param \Magento\Sales\Model\Order $order
     * @param                        $status
     * @param string                 $comment
     * @param bool                   $hasEmailBeenSent
     *
     * @throws \Exception
     */
    public function changeOrderStatus(\Magento\Sales\Model\Order $order, $status, $comment = '', $hasEmailBeenSent = false)
    {
        $order->addStatusToHistory($status, $comment, $hasEmailBeenSent);
        $order->save();
    }


}
