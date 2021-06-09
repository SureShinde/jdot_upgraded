<?php

namespace RLTSquare\CurrencyConverter\Model\Currency\Import;

/**
 * Class CurrencyConverterApi
 * @package RLTSquare\CurrencyConverter\Model\Currency\Import
 */
class CurrencyConverterApi extends \Magento\Directory\Model\Currency\Import\CurrencyConverterApi
{
    /**
     * @var string
     */
    const CURRENCY_CONVERTER_URL = 'http://free.currencyconverterapi.com/api/v3/convert?q={{CURRENCY_FROM}}_{{CURRENCY_TO}}&compact=ultra'; //@codingStandardsIgnoreLine

    /**
     * Http Client Factory
     *
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    private $httpClientFactory;

    /**
     * Core scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * CurrencyConverterApi constructor.
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     */
    public function __construct(
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
    ) {
        parent::__construct($currencyFactory, $scopeConfig, $httpClientFactory);
        $this->scopeConfig = $scopeConfig;
        $this->httpClientFactory = $httpClientFactory;
    }

    /**
     * @return array
     */
    public function fetchRates()
    {
        $data = [];
        $currencies = $this->_getCurrencyCodes();
        $defaultCurrencies = $this->_getDefaultCurrencyCodes();

        foreach ($defaultCurrencies as $currencyFrom) {
            if (!isset($data[$currencyFrom])) {
                $data[$currencyFrom] = [];
            }
            $data = $this->convertBatch($data, $currencyFrom, $currencies);
            ksort($data[$currencyFrom]);
        }
        return $data;
    }

    /**
     * @param array $data
     * @param string $currencyFrom
     * @param array $currenciesTo
     * @return array
     */
    private function convertBatch($data, $currencyFrom, $currenciesTo)
    {
        $freeApiKey = $this->scopeConfig->getValue(
            'currency/converter/api',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        foreach ($currenciesTo as $to) {
            set_time_limit(0);
            try {
                $url = str_replace('{{CURRENCY_FROM}}', $currencyFrom, self::CURRENCY_CONVERTER_URL);
                $url = str_replace('{{CURRENCY_TO}}', $to, $url);
                if (null !== $freeApiKey) {
                    $url .= '&apiKey=' . $freeApiKey;
                }
                $response = $this->getServiceResponse($url);
                if ($currencyFrom == $to) {
                    $data[$currencyFrom][$to] = $this->_numberFormat(1);
                } else {
                    if (empty($response)) {
                        $this->_messages[] = __('We can\'t retrieve a rate from %1 for %2.', $url, $to);
                        $data[$currencyFrom][$to] = null;
                    } else {
                        $data[$currencyFrom][$to] = $this->_numberFormat(
                            (double)$response[$currencyFrom . '_' . $to]
                        );
                    }
                }
            } finally {
                ini_restore('max_execution_time');
            }
        }

        return $data;
    }

    /**
     * @param string $url
     * @param int $retry
     * @return array|mixed
     */
    private function getServiceResponse($url, $retry = 0)
    {
        /** @var \Magento\Framework\HTTP\ZendClient $httpClient */
        $httpClient = $this->httpClientFactory->create();
        $response = [];

        try {
            $jsonResponse = $httpClient->setUri(
                $url
            )->setConfig(
                [
                    'timeout' => $this->scopeConfig->getValue(
                        'currency/currencyconverterapi/timeout',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    ),
                ]
            )->request(
                'GET'
            )->getBody();

            $response = json_decode($jsonResponse, true);
        } catch (\Exception $e) {
            if ($retry == 0) {
                $response = $this->getServiceResponse($url, 1);
            }
        }
        return $response;
    }
}
