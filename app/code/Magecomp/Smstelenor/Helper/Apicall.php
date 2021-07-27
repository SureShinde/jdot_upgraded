<?php 
namespace Magecomp\Smstelenor\Helper;
use Magento\Framework\Xml\Parser;
class Apicall extends \Magento\Framework\App\Helper\AbstractHelper
{
	const XML_TELENOR_SMS_API_USERNAME = 'sms/smsgatways/telenorusername';
    const XML_TELENOR_SMS_API_PASSWORD = 'sms/smsgatways/telenorpassword';
    const XML_TELENOR_SMS_SENDER = 'sms/smsgatways/telenorsmssender';
    const XML_TELENOR_SMS_APIURL = 'sms/smsgatways/telenorsmsapiurl';

	public function __construct(\Magento\Framework\App\Helper\Context $context)
	{
		parent::__construct($context);
	}

    public function getTitle() {
        return __("Telenor SMS");
    }

    public function getApiUrl()	{
        return $this->scopeConfig->getValue(
            self::XML_TELENOR_SMS_APIURL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getApiUsername(){
        return $this->scopeConfig->getValue(
            self::XML_TELENOR_SMS_API_USERNAME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getApiPassword(){
        return $this->scopeConfig->getValue(
            self::XML_TELENOR_SMS_API_PASSWORD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getApiSenderName(){
        return $this->scopeConfig->getValue(
            self::XML_TELENOR_SMS_SENDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function validateSmsConfig()
    {
        return $this->getApiUrl() && $this->getApiUsername() && $this->getApiPassword() && $this->getApiSenderName();
    }

    public function callApiUrl($mobilenumbers,$message)
    {
        try
        {
            $senderid = $this->getApiSenderName(); //Your senderid
            $url=$this->getApiUrl();
            $user=$this->getApiUsername();
            $password = $this->getApiPassword();

            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($mobilenumbers);
            $logger->info($message);

            $ch = curl_init();
            if (!$ch)
            {
                die("Couldn't initialize a cURL handle");
            }
            $sendurl = $url."auth.jsp?msisdn=$user&password=$password";
            curl_setopt($ch, CURLOPT_URL, $sendurl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $data = curl_exec($ch);
            curl_close($ch);

            $ch1 = curl_init();
            $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $data);

            $xml = new \SimpleXMLElement($data);
            $array = json_decode(json_encode((array)$xml), TRUE);
            $sessionId = $array['data'];
            $logger->info($array);
            $logger->info("Hello1");
            $logger->info($sessionId);
            $logger->info("Welcome1");
            $logger->info($url);

            $sendurl = $url."sendsms.jsp?session_id=$sessionId&to=$mobilenumbers&text=$message&mask=$senderid";
            $logger->info($sendurl);
            curl_setopt($ch1, CURLOPT_URL, $url."sendsms.jsp?");
            curl_setopt ($ch1, CURLOPT_POSTFIELDS,"session_id=$sessionId&to=$mobilenumbers&text=$message&mask=$senderid");
            curl_setopt($ch1, CURLOPT_POST, 1);
            curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);

            $curlresponse = curl_exec($ch1);
            $logger->info("Hello");
            $logger->info($curlresponse);
            $logger->info("Welcome");

            if(curl_errno($ch1))
            {
                $logger->info("Hello:".curl_errno($ch1));
                return false;
            }
            curl_close($ch1);

            return true;
        }
        catch (\Exception $e) {
            $logger->info("Hello catch :".$e->getMessage());
            return $e->getMessage();
        }
    }
}