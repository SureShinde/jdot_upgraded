<?php

namespace Magento\Etisalatpay\Controller;

use Magento\Framework\UrlInterface;
use Magento\Framework\Module\Dir;

abstract class Etisalatpay extends \Magento\Framework\App\Action\Action {

    protected $_paymentPlugin;
    protected $_scopeConfig;
    protected $_session;
    protected $_order;
    protected $messageManager;
    protected $_redirect;
    protected $_orderId;
    protected $_storeManager;
    protected $_orderManagement;
    protected $_url;
    protected $moduleReader;
    protected  $orderSender;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Etisalatpay\Model\Payment $paymentPlugin,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $session,
        \Magento\Sales\Model\Order $order,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Framework\Module\Dir\Reader $reader,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
    ){
        $this->_paymentPlugin   = $paymentPlugin;
        $this->_scopeConfig     = $scopeConfig;
        $this->_session         = $session;
        $this->_order 		    = $order;
        $this->_storeManager    = $storeManager;
        $this->_orderManagement = $orderManagement;
        $this->messageManager   = $context->getMessageManager();
        $this->_url             = $context->getUrl();
        $this->moduleReader     = $reader;
        $this->orderSender      = $orderSender;
        parent::__construct($context);
    }

    public function etisalatCurl($body, $type)
    {
        $config	= $this->getEtisalatConfig();

        $body['Customer'] = $config['merchant'];
        $body['UserName'] = "JJ_FaheemA";
        $body['Password'] = "f!g|IBR2bBIuY#";

        if( $type == 'Registration' ) {

            $format['Registration'] = $body;
            $json_body = json_encode($format);

        } elseif ( $type == 'Finalization' ) {

            $format['Finalization'] = $body;
            $json_body = json_encode($format);
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(

            CURLOPT_PORT 			=> "2443",
            CURLOPT_URL 			=> $config['url'],
            CURLOPT_RETURNTRANSFER 	=> true,
            CURLOPT_ENCODING 		=> "",
            CURLOPT_MAXREDIRS 		=> 10,
            CURLOPT_TIMEOUT 		=> 30,
            CURLOPT_CUSTOMREQUEST 	=> "POST",
            CURLOPT_POSTFIELDS 		=> $json_body,
            CURLOPT_CAINFO 			=> $config['ca'],
            //CURLOPT_SSLCERT 		=> $config['cert'],
            //CURLOPT_SSLCERTPASSWD 	=> $config['pass'],
            CURLOPT_HTTPHEADER 		=> array(
                "accept: text/xml-standard-api",
                "cache-control: no-cache",
                "content-type: application/json",
                "useragent: Freshserv"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getEtisalatConfig()
    {
        $config = array();

        $environment = $this->_scopeConfig->getValue('payment/etisalatpay/environment', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $certificates_path = $this->moduleReader->getModuleDir('', 'Magento_Etisalatpay');

        if( $environment == 'test' ) {

            $config['url'] = $this->_scopeConfig->getValue('payment/etisalatpay/test_etisalat_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $config['merchant'] = $this->_scopeConfig->getValue('payment/etisalatpay/test_merchant_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $config['ca'] 	= $certificates_path.'/'.'certificates'.'/'.'test'.'/'.'ca.crt';

            //$config['cert'] = $certificates_path.'/'.'certificates'.'/'.'test'.'/'.'Demo_Merchant_April_2023.pem';
            //$config['pass'] = 'Comtrust';

        } else {

            $config['url'] = $this->_scopeConfig->getValue('payment/etisalatpay/etisalat_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $config['merchant'] = $this->_scopeConfig->getValue('payment/etisalatpay/merchant_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $config['ca'] 	= $certificates_path.'/'.'certificates'.'/'.'production'.'/'.'ca.crt';

//			$config['cert'] = $certificates_path.'/'.'certificates'.'/'.'production'.'/'.'cert.pem';
//			$config['pass'] = 'JJ@2019';

        }
        return $config;
    }
}
