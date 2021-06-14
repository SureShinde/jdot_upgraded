<?php

namespace RLTSquare\SMS\Helper\Api;

/**
 * Class SendMessage
 * @package RLTSquare\SMS\Helper\Api
 */
class SendMessage
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * CustomerRegistration constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return |null
     */
    public function getSessionId()
    {
        $url = $this->scopeConfig->getValue(
            'general/api_credentials/url_session',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $msisdn = $this->scopeConfig->getValue(
            'general/api_credentials/mobile_number',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $password = $this->scopeConfig->getValue(
            'general/api_credentials/password',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES
        );

        if (isset($url, $msisdn, $password)) {
            try {
                $ch = curl_init();

                $headers = array(
                    'Content-Type: application/json'
                );

                $url .= '?msisdn=' . $msisdn . '&password=' . $password;

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $response = curl_exec($ch);
                $responseNo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($responseNo != 200) {
                    $this->messageManager->addErrorMessage(
                        'Something went wrong while submitting the form. '
                        . 'Api call returned '
                        . $responseNo
                        . ' error.'
                    );
                    return null;
                }
                $response = simplexml_load_string($response);
                $response = json_decode(
                    json_encode(
                        $response,
                        true
                    )
                );

                if ($response->response == 'OK') {
                    return $response->data;
                } else {
                    return null;
                }

            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    'Something went wrong while submitting the form. '
                    . 'Api call returned '
                    . $responseNo
                    . ' error.'
                );
                return null;
            }
        } else {
            $this->messageManager->addErrorMessage(
                'Something went wrong while submitting the form. Invalid credentials error!'
            );
            return null;
        }
    }

    /**
     * @param $sessionId
     * @param $phoneNumber
     * @param $text
     * @return |null
     */
    public function sendMessage($sessionId, $phoneNumber, $text)
    {
        $url = $this->scopeConfig->getValue(
            'general/api_credentials/api_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        // + is for space at the end
        $url .= '?session_id=' . $sessionId . '&to=' . $phoneNumber . '&text=' . $text . '&mask=J.%20';

        if (isset($url, $phoneNumber, $text)) {
            try {
                $ch = curl_init();

                $headers = array(
                    'Content-Type: application/json'
                );
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $response = curl_exec($ch);
                $responseNo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($responseNo != 200) {
                    $this->messageManager->addErrorMessage(
                        'Something went wrong while submitting the form. '
                        . 'Api call returned '
                        . $responseNo
                        . ' error.'
                    );
                    return null;
                }

                $response = simplexml_load_string($response);
                $response = json_decode(
                    json_encode(
                        $response,
                        true
                    )
                );

                if ($response->response == 'OK') {
                    return $response->data;
                } else {
                    return null;
                }

            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    'Something went wrong while submitting the form. '
                    . 'Api call returned '
                    . $responseNo
                    . ' error.'
                );
                return null;
            }
        } else {
            $this->messageManager->addErrorMessage(
                'Something went wrong while submitting the form. Invalid credentials error!'
            );
            return null;
        }

    }
}
