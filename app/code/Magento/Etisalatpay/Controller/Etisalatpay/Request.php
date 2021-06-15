<?php 

namespace  Magento\Etisalatpay\Controller\Etisalatpay;

class Request extends \Magento\Etisalatpay\Controller\Etisalatpay
{
	/*
	 * Registration call
	 */
	public function execute()
	{
		$orderId = $this->_session->getLastRealOrderId();
		
		if (!isset($orderId) || !$orderId) {
			$message = 'Invalid order ID, please try again later';
			return $this->_redirect('checkout/cart');
		}

		$order = $this->_order->loadByIncrementId($orderId);
        $items = $order->getItems();
        $applicableSku = [
            'PM101473-100-999-M',
            '15000008-100-NIL',
            'PL089628-100-999-L',
            'PM089626-100-999-M',
            '02034612-100-999',
            '02031081-100-999',
            '02033671-100-999',
            '02032644-100-999'
        ];
        foreach ($items as $item) {
            $productSku= $item->getSku();
            if (in_array($productSku, $applicableSku)) {
                $order->setOrderEngrave('ENGRAVING');
                $order->save();
            }
        }
        $order->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT)->setStatus('pending_payment');
        $order->save();

		// prepare registration call for etisalat
		$etisalat['Channel']  	= 'Web';
		$etisalat['Currency']  	= 'PKR';
		
		$etisalat['Amount']  	= $order->getBaseGrandTotal();
		$etisalat['OrderID']  	= $orderId;
		$etisalat['OrderName']  = 'JDot';
        $queryParams = [
            'order_id' => $orderId, // value for parameter
        ];
        $etisalat['ReturnPath'] = $this->_url->getUrl('etisalatpay/etisalatpay/response',['_current' => true,'_use_rewrite' => true, '_query' => $queryParams]);
		$etisalat['OrderInfo']  = $order->getId(); //O -
		$etisalat['TransactionHint'] = 'CPT:N;VCC:Y'; //O -

		//$etisalat['Store']    = ''; //O -
		//$etisalat['Terminal'] = ''; //O -
		//$etisalat['Language'] = "en"; //O -
		//$etisalat['version']  = "2.0"; //O -

		$result = $this->etisalatCurl($etisalat, 'Registration');

		$response = json_decode($result, true);

		if( $response['Transaction']['ResponseCode'] != 0 ) {

			//payment error | failure
			$errorMessage = _('Payment canceled by customer');

			//$this->_orderManagement->unHold($orderId);
			//$this->_orderManagement->cancel($orderId);

			$order->registerCancellation($errorMessage)->save();
			$order->save();

			$this->_session->restoreQuote();

			$this->messageManager->addError($errorMessage);

			return $this->_redirect('checkout/cart');

		} else {

			//success | proceed
			echo '<center>You are being re-directed to Payment Gateway.</b></center><form method="post" id="etisalat-redirect-form" name="redirect" action="'.$response['Transaction']['PaymentPage'].'"><input type="Hidden" name="TransactionID" value="'.$response['Transaction']['TransactionID'].'"/></form><script type="text/javascript">document.redirect.submit();</script>';
			exit(0);
		}
	}
}