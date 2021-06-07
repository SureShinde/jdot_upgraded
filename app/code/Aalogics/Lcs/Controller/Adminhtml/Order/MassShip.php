<?php
namespace Aalogics\Lcs\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use \Aalogics\lcs\Model\OrderManager;
use \Aalogics\Lcs\Helper\Data;

class MassShip extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Aalogics_Lcs::massship';

    protected $_lcsOrderManager;
    
    protected $_lcsHelper;

     protected $_request;
    
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(Context $context, Filter $filter, 
    							CollectionFactory $collectionFactory,
    							\Aalogics\Lcs\Model\OrderManager $lcsModel,
    							\Aalogics\Lcs\Helper\Data $lcsHelper,
                                \Magento\Framework\App\Request\Http $request
)
    {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->_lcsOrderManager = $lcsModel;
        $this->_lcsHelper = $lcsHelper;
        $this->_request = $request;
    }

    /**
     * Ship via lcs selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
    	$this->_lcsHelper->debug('massAction Start');

        // $orderIds = $this->_getOrderIds();
    
        // $costcenter = $this->getRequest()->getParam('costcenter');
        $ordersArray = array();
        $shipOrders = array ();
        $alreadyShipped = array ();
        $orderAlreadyShipped = 0;
        $count = 0;

        if($this->_lcsHelper->isEnabled()){
        	
            foreach ($collection->getItems() as $order) {
        		if (!$order->canCancel()) {
        			continue;
        		}
        		$shipment = $order->getShipmentsCollection ()->getFirstItem ();
        		$shipmentIncrementId = $shipment->getIncrementId ();

                // 	$shipOrders [$count ++] = $orderIds [$orders];
        		
                if ($shipmentIncrementId == null) {
        			$shipOrders [] = $order;
                    $ordersArray[] = $order->getEntityId();
        		} else {
        			$alreadyShipped[] = $order;
        		}

        	}

        	$message = '';
        	if (count ( $alreadyShipped ) > 0) {
        		$message = __( "Shipment has already been created for the selected order(s) : " );
        	    for($x = 0; $x < count ( $alreadyShipped ); $x ++) {
        			$message .= $alreadyShipped[$x]->getIncrementId();	 
        			if (! $x == (count ( $alreadyShipped ) - 1))
            			$message .= ', ';
        		}

        	}
        	
            if(!empty($message)) {
            	$this->messageManager->addError($message);
            }
        	
            if($this->_lcsHelper->isShippingEnabled()){

                $this->_lcsHelper->debug('massAction invoiceandShipAll start');
                // Process Invoices + Shipments
                $result = $this->_lcsOrderManager->shipAll (
                        $shipOrders, 
                        $this->_lcsHelper->getAdminField ( 'lcs_inv_shipp_action/new_status' ),
                        $this->_lcsHelper->getAdminField ( 'lcs_inv_shipp_action/invoce_email' ),
                        $this->_lcsHelper->getAdminField ( 'lcs_inv_shipp_action/shipment_email' ),
                        $this->_getTrackingNumbers (),
                        $this->_getCarrierCodes ()
                        // ,$costcenter
                );

                // Add results to session
                $this->addResultsToSession ( $result );

            }else{
                $this->messageManager->addError('LCS Shippement Not Enabled');
            }
            
        }else{
            $this->messageManager->addError('LCS is Not Enabled');
        }

        // go back to the order overview page
        $orderErrors = $this->_redirectWithSelectionlcs ( $shipOrders, 'invoiceshipaction', $alreadyShipped );

        //      $resultRedirect = $this->resultRedirectFactory->create();
        //      $resultRedirect->setPath($this->getComponentRefererUrl());
        //      return $resultRedirect;
                
        //      $this->pdfdocsAction ( $orderIds );

    }
    
    
    
    protected function _redirectWithSelectionpdf($orderIds, $action) {
    	$keepSelection = Mage::helper ( 'aalcs' )->getStoreConfig ( $action . '/keep_order_selection' );
    	if ($keepSelection && is_array ( $orderIds ) && ! empty ( $orderIds )) {
    		$orderIds = implode ( ',', $orderIds );
    		$this->pdfdocsAction ();
    	} else {
    		// $this->_redirect ( 'adminhtml/sales_order/' );
            $this->_redirect ( 'sales/order/' );
    	}
    }
    protected function _redirectWithSelectionlcs($orderIds, $action, $alreadyShipped = array()) {
    	$keepSelection = $this->_lcsHelper->getAdminField ( $action . '/keep_order_selection' );
    	if ($keepSelection && is_array ( $orderIds ) && ! empty ( $orderIds )) {
    		$this->addResultsToSession ( $result, 'Invoiced and shipped' );
    		$this->pdfdocsAction ();
    	} else {
    		// $this->_redirect ( 'adminhtml/sales_order/' );
            $this->_redirect ( 'sales/order/' );
    	}
    }
    public function pdfdocsAction() {
    
    	$orderIds = $this->getRequest ()->getPost ('order_ids');
    	error_log('pdfdocsAction Order Ids'.print_r($orderIds, TRUE));
    	$flag = false;
    	if (! empty ( $orderIds )) {
    		foreach ( $orderIds as $orderId ) {
    			$invoices = Mage::getResourceModel ( 'sales/order_invoice_collection' )->setOrderFilter ( $orderId )->load ();
    			$shipments = Mage::getResourceModel ( 'sales/order_shipment_collection' )->setOrderFilter ( $orderId )->load ();
    			if ($shipments->getSize ()) {
    				$flag = true;
    					
    				if (! isset ( $pdf )) {
    
    					$pdf = Mage::getModel ( 'sales/order_pdf_shipment' )->getPdf ( $shipments );
    				} else {
    					$pages = Mage::getModel ( 'sales/order_pdf_shipment' )->getPdf ( $shipments );
    					$pdf->pages = array_merge ( $pdf->pages, $pages->pages );
    				}
    			}
    
    			$creditmemos = Mage::getResourceModel ( 'sales/order_creditmemo_collection' )->setOrderFilter ( $orderId )->load ();
    			if ($creditmemos->getSize ()) {
    				$flag = true;
    				if (! isset ( $pdf )) {
    					$pdf = Mage::getModel ( 'sales/order_pdf_creditmemo' )->getPdf ( $creditmemos );
    				} else {
    					$pages = Mage::getModel ( 'sales/order_pdf_creditmemo' )->getPdf ( $creditmemos );
    					$pdf->pages = array_merge ( $pdf->pages, $pages->pages );
    				}
    			}
    		}
    		if ($flag) {
    			Mage::log('in flag');
    			return $this->_prepareDownloadResponse ( 'docs' . Mage::getSingleton ( 'core/date' )->date ( 'Y-m-d_H-i-s' ) . '.pdf', $pdf->render(), 'application/pdf' );
    		} else {
    			Mage::log ( "false" );
    			$this->_getSession ()->addError ( $this->__ ( 'There are no printable documents related to selected orders.' ) );
    			$this->_redirect ( '*/*/' );
    		}
    	}
    
    	$this->_redirect ( '*/*/' );
    }
    
    /**
     * sorted order ids from POST
     *
     * @return mixed
     */
    protected function _getOrderIds()
    {
        // $orderIds = $this->getRequest()->getPost('order_ids');
    	$orderIds = $this->_request->getPost('selected');;
    	sort($orderIds);
    	return $orderIds;
    }
    
    /**
     * retrieve tracking numbers from POST
     * sort into array by order_id
     */
    protected function _getTrackingNumbers()
    {
    	$trackingNumbersSorted = array();
    	$trackingNumbersRaw = $this->getRequest()->getPost('tracking');
    	if (!$trackingNumbersRaw) {
    		return $trackingNumbersSorted;
    	}
    	$trackingNumbersRaw = explode(",", $trackingNumbersRaw);
    	foreach ($trackingNumbersRaw as $trackingNumberRaw) {
    		list($orderId, $number) = explode("|", $trackingNumberRaw);
    		$trackingNumbersSorted[$orderId] = $number;
    	}
    	return $trackingNumbersSorted;
    }
    
    /**
     * retrieve carrier codes from POST
     * sort into array by order_id
     */
    protected function _getCarrierCodes()
    {
    	$carrierCodesSorted = array();
    	$carrierCodesRaw = explode(",", $this->getRequest()->getPost('carrier'));
    	if (is_array($carrierCodesRaw)) {
    		foreach ($carrierCodesRaw as $carrierCodeRaw) {
    			if (!preg_match('/[a-z|]/', $carrierCodeRaw)) {
    				continue;
    			}
    			list($orderId, $code) = explode("|", $carrierCodeRaw);
    			$carrierCodesSorted[$orderId] = $code;
    		}
    	}
    	return $carrierCodesSorted;
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
}
