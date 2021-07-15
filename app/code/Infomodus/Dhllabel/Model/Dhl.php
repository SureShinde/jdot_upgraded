<?php

namespace Infomodus\Dhllabel\Model;

class Dhl
{
    public $packages = [];
    public $weightUnits;
    public $packageWeight;

    public $packageType;

    public $includeDimensions;
    public $unitOfMeasurement;
    public $depth;
    public $width;
    public $height;

    public $shipperName;
    public $shipperPhoneNumber;
    public $shipperAddressLine1;
    public $shipperAddressLine2;
    public $shipperAddressLine3;
    public $shipperCity;
    public $shipperStateProvinceName;
    public $shipperStateProvinceCode;
    public $shipperPostalCode;
    public $shipperCountryCode;
    public $shipperCountryName;
    public $shipmentDescription;
    public $shipperAttentionName;

    public $shiptoCompanyName;
    public $shiptoAttentionName;
    public $shiptoPhoneNumber;
    public $shiptoAddressLine1;
    public $shiptoAddressLine2;
    public $shiptoAddressLine3;
    public $shiptoCity;
    public $shiptoStateProvinceName;
    public $shiptoStateProvinceCode;
    public $shiptoPostalCode;
    public $shiptoCountryCode;
    public $shiptoCountryName;
    public $shiptoVatid;

    public $serviceCode;
    public $serviceLocalCode;
    public $ReferenceId;
    public $declaredValue;
    public $print_type;
    public $print_type_format;
    public $print_type_dpi;
    public $print_type_refund;
    public $print_type_format_refund;

    /*
    public $shipmentDigest;

    public $trackingNumber;
    public $shipmentIdentificationNumber;
    public $graphicImage;
    public $htmlImage;
*/
    public $codYesNo;
    public $currencyCode;
    public $codMonetaryValue;
    public $codOrderId;
    public $insuredMonetaryValue = 0;
    public $testing;
    public $qvn = 0;
    public $qvn_email_message = '';
    public $qvn_email_shipto = '';

    public $dhlAccount = 0;
    public $accountData;
    public $dhlAccountDuty = 0;
    public $accountDataDuty;
    public $shipperId;

    public $invoiceProducts = [];
    public $termsOfTrade = null;
    public $placeOfIncoterm = null;
    public $invoiceType = 'CMI';
    public $eoriNumber = null;
    public $requestArchiveDoc = 1;
    public $shipperEIN = null;
    public $consigneeEIN = null;

    protected $request;
    protected $_conf;
    public $handy;
    private $date;
    protected $_currencyFactory;
    protected $orderRepository;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Infomodus\Dhllabel\Helper\Config $config,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Sales\Model\OrderRepository $orderRepository
    )
    {
        $this->_conf = $config;
        $this->_conf->createMediaFolders();
        $this->date = $date;
        $this->_currencyFactory = $currencyFactory;
        $this->orderRepository = $orderRepository;
    }


    public function getShip($type, $storeId = null)
    {
        $path_xml = $this->_conf->getBaseDir('media') . 'dhllabel/test_xml/';

        $request = new \Infomodus\Dhllabel\Model\Src\Request\ShipmentRequest(
            $this->_conf->getStoreConfig('dhllabel/credentials/userid', $storeId),
            $this->_conf->getStoreConfig('dhllabel/credentials/password', $storeId),
            $this->date
        );

        $europeCountries = explode(",", $this->_conf->getStoreConfig('general/country/eu_countries', $storeId));

        $destWeightUnit = $this->_conf->getWeightUnitByCountry($this->shiptoCountryCode);
        $weingUnitKoef = 1;
        switch ($destWeightUnit) {
            case 'KG':
                $destWeightUnit = 'K';
                break;
            case 'LB':
                $destWeightUnit = 'L';
                break;
        }

        if ($destWeightUnit != $this->weightUnits) {
            if ($destWeightUnit == 'L') {
                $weingUnitKoef = 2.2046;
            } else {
                $weingUnitKoef = 1 / 2.2046;
            }
        }

        $destDimentionUnit = $this->_conf->getDimensionUnitByCountry($this->shiptoCountryCode);
        switch ($destDimentionUnit) {
            case 'CM':
                $destDimentionUnit = 'C';
                break;
            case 'IN':
                $destDimentionUnit = 'I';
                break;
        }

        $dimentionUnitKoef = 1;
        if ($destWeightUnit != $this->weightUnits) {
            if ($destWeightUnit == 'C') {
                $dimentionUnitKoef = 2.54;
            } else {
                $dimentionUnitKoef = 1 / 2.54;
            }
        }

        /* Multipackages */
        $pieces = [];
        if ($this->packages) {
            foreach ($this->packages as $pv) {
                $pieces1 = [];
                if (isset($pv['height']) || isset($pv['width']) || isset($pv['depth'])) {
                    $pieces1['height'] = round((float)$pv['height'] * $dimentionUnitKoef, 0);
                    $pieces1['width'] = round((float)$pv['width'] * $dimentionUnitKoef, 0);
                    $pieces1['depth'] = round((float)$pv['depth'] * $dimentionUnitKoef, 0);
                }

                $packweight = array_key_exists('packweight', $pv) ?
                    (float)str_replace(',', '.', $pv['packweight']) * $weingUnitKoef : 0;
                $weight = array_key_exists('weight', $pv) ?
                    (float)str_replace(',', '.', $pv['weight']) * $weingUnitKoef : 0;
                $pieces1['weight'] = round(($weight + (is_numeric($packweight) ? $packweight : 0)), 3);
                $pieces[] = $pieces1;
            }
        }

        /* END Multipackages */

        $account = $this->accountData;
        $accountNumber = null;
        if ($this->accountData != "S" && $this->accountData != "R" && $this->accountData) {
            $account = "T";
            $accountNumber = $this->accountData->getAccountnumber();
        }

        if (strlen($this->shiptoPhoneNumber) == 0) {
            $this->shiptoPhoneNumber = $this->shipperPhoneNumber;
        }

        $accountDuty = $this->accountDataDuty;
        $accountNumberDuty = null;
        if ($this->accountDataDuty != "S" && $this->accountDataDuty != "R" && $this->accountDataDuty) {
            $accountDuty = "T";
            $accountNumberDuty = $this->accountDataDuty->getAccountnumber();
        }

        $configMethod = new \Infomodus\Dhllabel\Model\Config\Dhlmethod;
        $ndc = $configMethod->getContentTypeByMethod($this->serviceCode);

        $isDutiable = null;
        if ($ndc == 'NONDOC' && $this->shiptoCountryCode != $this->shipperCountryCode) {
            $request->buildDutiable(
                $this->declaredValue, $this->currencyCode,
                $this->shipperEIN,
                $this->consigneeEIN,
                $this->termsOfTrade
            );
            $isDutiable = true;
        }

        $dutyPaymentType = null;
        $dutyAccountNumber = null;
        if ($isDutiable == true) {
            $dutyPaymentType = $accountDuty;
            $dutyAccountNumber = $accountNumberDuty;
            if ($dutyPaymentType != 'R' && (!in_array($this->shiptoCountryCode, $europeCountries) || !in_array($this->shipperCountryCode, $europeCountries))) {
                $request->buildSpecialService('DD');
            }
        }

        $request->buildBilling(
            $this->_conf->getStoreConfig('dhllabel/credentials/shippernumber', $storeId),
            $account, $accountNumber, $dutyPaymentType, $dutyAccountNumber)
            ->setRegionCode(
                $this->_conf->getStoreConfig('dhllabel/shipping/regioncode', $storeId))
            ->setLabelImageFormat($this->print_type);
        $request->setRequestArchiveDoc($this->requestArchiveDoc);
        /* Ship to */
        if ($type == 'shipment') {
            $request->buildConsignee($this->shiptoCompanyName,
                $this->shiptoAddressLine1,
                $this->shiptoAddressLine2,
                $this->shiptoAddressLine3,
                $this->shiptoCity,
                $this->shiptoPostalCode,
                $this->shiptoCountryCode,
                $this->shiptoCountryName,
                $this->shiptoAttentionName,
                $this->shiptoPhoneNumber,
                $this->shiptoStateProvinceName,
                $this->shiptoStateProvinceCode,
                $this->qvn_email_shipto,
                $this->shiptoVatid
            );
        } else {
            $request->buildConsignee($this->shipperName,
                $this->shipperAddressLine1,
                $this->shipperAddressLine2,
                $this->shipperAddressLine3,
                $this->shipperCity,
                $this->shipperPostalCode,
                $this->shipperCountryCode,
                $this->shipperCountryName,
                $this->shipperAttentionName,
                $this->shipperPhoneNumber,
                $this->shipperStateProvinceName,
                $this->shipperStateProvinceCode
            );
        }

        /* END Ship to */

        $insuredAmount = null;
        $isCODItaly = null;
        if ($this->codYesNo == 1) {
            $order_id = $this->orderRepository->get($this->codOrderId)->getIncrementId();
            $isCODItaly = $order_id . '#' . round((float)$this->codMonetaryValue, 2) . '#'
                . round($this->codMonetaryValue, 2);
            if ($this->shipperCountryCode == 'IT' || ($this->shipperCountryCode == 'AE' && $this->shiptoCountryCode == 'SA')) {
                $tempCurrency = $this->currencyCode;
                $tempMonetaryValue = $this->codMonetaryValue;
                /*if ($this->shiptoCountryCode == 'SA') {
                    $tempCurrency = 'SAR';
                    $responseCurrencyCode = $this->mappingCurrencyCode($tempCurrency);
                    if ($responseCurrencyCode) {
                        $this->allowedCurrencies = $this->_currencyFactory->create()->getConfigAllowCurrencies();
                        if (in_array($responseCurrencyCode, $this->allowedCurrencies)) {
                            $tempMonetaryValue = (double)$tempMonetaryValue * $this->_getBaseCurrencyKoef($this->currencyCode, $responseCurrencyCode);
                        }
                    }

                }*/
                $request->buildSpecialService('KB', round((float)$tempMonetaryValue, 2), $tempCurrency);
                $isCODItaly = '&lt;' . round($this->codMonetaryValue, 2) . '&gt;&lt;' . $this->currencyCode . '&gt;&lt;Y&gt;&lt;&gt;';
            }

            $request->buildReference("COD", 'CU');
            $this->shipmentDescription = "COD_" . round((float)$this->codMonetaryValue, 2);
        } else {
            if ($this->ReferenceId != '') {
                $request->buildReference($this->ReferenceId);
            }
        }

        if ($this->_conf->getStoreConfig('dhllabel/ratepayment/insured', $storeId) == 1 && $this->insuredMonetaryValue > 0) {
            $insuredAmount = $this->insuredMonetaryValue;
            $request->buildSpecialService('II', $insuredAmount, $this->currencyCode);

        }

        $request->buildShipmentDetails($pieces, $this->serviceCode, $this->serviceLocalCode, $this->date,
            $this->shipmentDescription, $this->currencyCode, $destWeightUnit, $destDimentionUnit,
            $this->packageType, $isDutiable, $isCODItaly);

        /* Shipper */
        if ($type == 'shipment') {
            $request->buildShipper(
                $this->_conf->getStoreConfig('dhllabel/credentials/shippernumber', $storeId),
                $this->shipperName,
                $this->shipperAddressLine1,
                $this->shipperAddressLine2,
                $this->shipperAddressLine3,
                $this->shipperCity,
                $this->shipperPostalCode,
                $this->shipperCountryCode,
                $this->shipperCountryName,
                $this->shipperEIN,
                $this->shipperAttentionName,
                $this->shipperPhoneNumber,
                $this->shipperStateProvinceName,
                $this->shipperStateProvinceCode,
                $this->eoriNumber
            );
        } else {
            $request->buildShipper(
                $this->_conf->getStoreConfig('dhllabel/credentials/shippernumber', $storeId),
                $this->shiptoCompanyName,
                $this->shiptoAddressLine1,
                $this->shiptoAddressLine2,
                $this->shiptoAddressLine3,
                $this->shiptoCity,
                $this->shiptoPostalCode,
                $this->shiptoCountryCode,
                $this->shiptoCountryName,
                $this->shipperEIN,
                $this->shiptoAttentionName,
                $this->shiptoPhoneNumber,
                $this->shiptoStateProvinceName,
                $this->shiptoStateProvinceCode,
                $this->eoriNumber
            );
        }

        /* END Shipper */

        if ($this->qvn == 1) {
            $request->buildNotification($this->qvn_email_shipto, $this->qvn_email_message);
        }

        $request->buildLabelFormat($this->print_type_format, $this->print_type_dpi);

        if ($ndc == 'NONDOC' && ($this->shipperCountryCode != $this->shiptoCountryCode || ($this->shiptoCountryCode == 'GB' && substr($this->shiptoPostalCode, 0, 2) == 'BT'))
            && $this->_conf->getStoreConfig('dhllabel/paperless/type', $storeId) == 1
        ) {
            $order = $this->orderRepository->get($this->codOrderId);
            $request->buildSpecialService('WY');
            $request->setEProcShip('N');
            /*$request->buildDocImages('CIN', base64_encode($this->invoicePdf), 'PDF');*/
            $request->setUseDHLInvoice("Y");
            $request->setDHLInvoiceLanguageCode("en");
            $request->setDHLInvoiceType($this->invoiceType);
            $signatureImage = null;
            $signatureName = null;
            $signatureTitle = null;
            if ($this->_conf->getStoreConfig('dhllabel/paperless/signature', $storeId) != "") {
                $signatureImage = base64_encode(file_get_contents($this->_conf->getBaseDir('media') . 'dhllabel/' . $this->_conf->getStoreConfig('dhllabel/paperless/signature', $storeId)));
            }
            if ($this->_conf->getStoreConfig('dhllabel/paperless/signature_name', $storeId) != "") {
                $signatureName = $this->_conf->getStoreConfig('dhllabel/paperless/signature_name', $storeId);
            }
            if ($this->_conf->getStoreConfig('dhllabel/paperless/signature_title', $storeId) != "") {
                $signatureTitle = $this->_conf->getStoreConfig('dhllabel/paperless/signature_title', $storeId);
            }

            $request->buildExportDeclaration(
                $this->invoiceProducts,
                $this->weightUnits,
                $order->getIncrementId(),
                date("Y-m-d", strtotime($order->getCreatedAt())),
                ($type == 'shipment' ? $this->shiptoCompanyName : $this->shipperName),
                ($type == 'shipment' ? $this->shiptoAttentionName : $this->shipperAttentionName),
                ($type == 'shipment' ? $this->shiptoAddressLine1 : $this->shipperAddressLine1),
                ($type == 'shipment' ? $this->shiptoCity : $this->shipperCity),
                ($type == 'shipment' ? $this->shiptoPostalCode : $this->shipperPostalCode),
                ($type == 'shipment' ? $this->shiptoCountryCode : $this->shipperCountryCode),
                ($type == 'shipment' ? $this->shiptoCountryName : $this->shipperCountryName),
                ($type == 'shipment' ? $this->shiptoPhoneNumber : $this->shipperPhoneNumber),
                $this->shipperEIN,
                $this->termsOfTrade,
                $signatureImage,
                $signatureName,
                $signatureTitle,
                'P',
                $this->placeOfIncoterm
            );

        }

        $request->buildDangerousGoods($this->invoiceProducts, $this->_conf->getWeightUnitByCountry($this->shiptoCountryCode), $weingUnitKoef);

        $response = $request->send($this->testing);

        file_put_contents($path_xml . "ShipDirectRequest.xml", $request->responce);

        file_put_contents($path_xml . "ShipDirectResponse.xml", $response);

        return ['request' => $request->responce, 'response' => $response];
    }

    public function getShipFrom($type, $storeId = null)
    {
        if ($this->shipperCountryCode != $this->shiptoCountryCode && $this->_conf->getStoreConfig('dhllabel/return/shipperid_international', $storeId) != "") {
            $returnShipperId = $this->_conf->getStoreConfig('dhllabel/return/shipperid_international', $storeId);
        } else {
            $returnShipperId = $this->_conf->getStoreConfig('dhllabel/return/shipperid', $storeId);
        }

        if (!$returnShipperId) {
            $this->shipperId = $this->_conf->getStoreConfig('dhllabel/credentials/shippernumber', $storeId);
        } else {
            $this->shipperId = $returnShipperId;
        }

        $path_xml = $this->_conf->getBaseDir('media') . 'dhllabel/test_xml/';

        $request = new \Infomodus\Dhllabel\Model\Src\Request\ShipmentRequest(
            $this->_conf->getStoreConfig('dhllabel/credentials/userid', $storeId),
            $this->_conf->getStoreConfig('dhllabel/credentials/password', $storeId),
            $this->date
        );

        $request->setRequestArchiveDoc($this->requestArchiveDoc);

        $europeCountries = explode(",", $this->_conf->getStoreConfig('general/country/eu_countries', $storeId));

        $destWeightUnit = $this->_conf->getWeightUnitByCountry($this->shipperCountryCode);
        $weingUnitKoef = 1;
        switch ($destWeightUnit) {
            case 'KG':
                $destWeightUnit = 'K';
                break;
            case 'LB':
                $destWeightUnit = 'L';
                break;
        }

        if ($destWeightUnit != $this->weightUnits) {
            if ($destWeightUnit == 'L') {
                $weingUnitKoef = 2.2046;
            } else {
                $weingUnitKoef = 1 / 2.2046;
            }
        }

        $destDimentionUnit = $this->_conf->getDimensionUnitByCountry($this->shipperCountryCode);
        switch ($destDimentionUnit) {
            case 'CM':
                $destDimentionUnit = 'C';
                break;
            case 'IN':
                $destDimentionUnit = 'I';
                break;
        }

        $dimentionUnitKoef = 1;
        if ($destWeightUnit != $this->weightUnits) {
            if ($destWeightUnit == 'C') {
                $dimentionUnitKoef = 2.54;
            } else {
                $dimentionUnitKoef = 1 / 2.54;
            }
        }

        /* Multipackages */
        $pieces = [];
        if ($this->packages) {
            foreach ($this->packages as $pv) {
                $pieces1 = [];
                if (isset($pv['height']) || isset($pv['width']) || isset($pv['depth'])) {
                    $pieces1['height'] = round((float)$pv['height'] * $dimentionUnitKoef, 0);
                    $pieces1['width'] = round((float)$pv['width'] * $dimentionUnitKoef, 0);
                    $pieces1['depth'] = round((float)$pv['depth'] * $dimentionUnitKoef, 0);
                }

                $packweight = array_key_exists('packweight', $pv) ?
                    (float)str_replace(',', '.', $pv['packweight']) * $weingUnitKoef : 0;
                $weight = array_key_exists('weight', $pv) ?
                    (float)str_replace(',', '.', $pv['weight']) * $weingUnitKoef : 0;
                $pieces1['weight'] = round(($weight + (is_numeric($packweight) ? $packweight : 0)), 3);
                $pieces[] = $pieces1;
            }
        }

        /* END Multipackages */

        $account = $this->accountData;
        $accountNumber = null;
        if (!$returnShipperId && ($this->accountData != "S" && $this->accountData != "R" && $this->accountData)) {
            $account = "T";
            $accountNumber = $this->accountData->getAccountnumber();
        } else if ($returnShipperId) {
            $account = "R";
            $accountNumber = $this->shipperId;
        }

        if (strlen($this->shiptoPhoneNumber) == 0) {
            $this->shiptoPhoneNumber = $this->shipperPhoneNumber;
        }

        $accountDuty = $this->accountDataDuty;
        $accountNumberDuty = null;
        if (!$returnShipperId && ($this->accountDataDuty != "S" && $this->accountDataDuty != "R") && $this->accountDataDuty) {
            $accountDuty = "T";
            $accountNumberDuty = $this->accountDataDuty->getAccountnumber();
        } else if ($returnShipperId) {
            $accountDuty = "R";
            $accountNumberDuty = $this->shipperId;
        }

        $configMethod = new \Infomodus\Dhllabel\Model\Config\Dhlmethod;
        $ndc = $configMethod->getContentTypeByMethod($this->serviceCode);

        $isDutiable = null;
        if ($ndc == 'NONDOC' /*&& $this->shiptoCountryCode != $this->shipperCountryCode*/) {
            $request->buildDutiable(
                $this->declaredValue, $this->currencyCode,
                $this->shipperEIN,
                $this->consigneeEIN,
                $this->termsOfTrade
            );
            $isDutiable = true;
        }

        $dutyPaymentType = null;
        $dutyAccountNumber = null;
        if ($isDutiable == true) {
            $dutyPaymentType = $accountDuty;
            $dutyAccountNumber = $accountNumberDuty;
            if ($dutyPaymentType != 'R' && (!in_array($this->shiptoCountryCode, $europeCountries) || !in_array($this->shipperCountryCode, $europeCountries))) {
                $request->buildSpecialService('DD');
            }
        }

        $request->buildBilling(
            $this->shipperId,
            $account, $accountNumber, $dutyPaymentType, $dutyAccountNumber)
            ->setRegionCode($this->_conf->getStoreConfig('dhllabel/shipping/regioncode', $storeId))
            ->setLabelImageFormat($this->print_type_refund)
            /* Ship to */
            ->buildConsignee($this->shipperName,
                $this->shipperAddressLine1,
                $this->shipperAddressLine2,
                $this->shipperAddressLine3,
                $this->shipperCity,
                $this->shipperPostalCode,
                $this->shipperCountryCode,
                $this->shipperCountryName,
                $this->shipperAttentionName,
                $this->shipperPhoneNumber,
                $this->shipperStateProvinceName,
                $this->shipperStateProvinceCode
            );
        /* END Ship to */

        /*if ($this->codYesNo == 1) {
            if ($this->shipperCountryCode == 'IT' || ($this->shipperCountryCode == 'AE' && $this->shiptoCountryCode == 'SA')) {
                $tempCurrency = $this->currencyCode;
                $tempMonetaryValue = $this->codMonetaryValue;
                if($this->shiptoCountryCode == 'SA'){
                    $tempCurrency = 'SAR';
                    $responseCurrencyCode = $this->mappingCurrencyCode($tempCurrency);
                    if ($responseCurrencyCode) {
                        $this->allowedCurrencies = $this->_currencyFactory->create()->getConfigAllowCurrencies();
                        if (in_array($responseCurrencyCode, $this->allowedCurrencies)) {
                            $tempMonetaryValue = (double)$tempMonetaryValue * $this->_getBaseCurrencyKoef($this->currencyCode, $responseCurrencyCode);
                        }
                    }
                }
                $request->buildSpecialService('KB', round($tempMonetaryValue, 2), $tempCurrency);
            } else {
                $order_id = $this->orderRepository->get($this->codOrderId)->getIncrementId();
                $request->buildReference($order_id . '#' . round($this->codMonetaryValue, 2) . '#'
                    . round($this->codMonetaryValue, 2));
                $this->shipmentDescription = "COD_" . round($this->codMonetaryValue, 2);
            }
        }*/

        if ($this->ReferenceId != '') {
            $request->buildReference($this->ReferenceId);
        }

        $insuredAmount = null;
        if ($this->_conf->getStoreConfig('dhllabel/ratepayment/insured', $storeId) == 1 && $this->insuredMonetaryValue > 0) {
            $insuredAmount = $this->insuredMonetaryValue;
            $request->buildSpecialService('II', $insuredAmount, $this->currencyCode);

        }

        $request->buildShipmentDetails($pieces, $this->serviceCode, $this->serviceLocalCode, $this->date,
            $this->shipmentDescription, $this->currencyCode, $destWeightUnit, $destDimentionUnit, $this->packageType, $isDutiable/*, $insuredAmount*/)
            /* Shipper */
            ->buildShipper(
                $this->shipperId,
                $this->shiptoCompanyName,
                $this->shiptoAddressLine1,
                $this->shiptoAddressLine2,
                $this->shiptoAddressLine3,
                $this->shiptoCity,
                $this->shiptoPostalCode,
                $this->shiptoCountryCode,
                $this->shiptoCountryName,
                null,
                $this->shiptoAttentionName,
                $this->shiptoPhoneNumber,
                $this->shiptoStateProvinceName,
                $this->shiptoStateProvinceCode
            );
        /* END Shipper */

        if ($this->qvn == 1) {
            $request->buildNotification($this->qvn_email_shipto, $this->qvn_email_message);
        }

        $request->buildLabelFormat($this->print_type_format_refund, $this->print_type_dpi);

        if ($this->_conf->getStoreConfig('dhllabel/return/return_time_old') != '') {
            $request->buildSpecialService($this->_conf->getStoreConfig('dhllabel/return/return_time_old'));
        }

        $request->buildDangerousGoods($this->invoiceProducts, $this->_conf->getWeightUnitByCountry($this->shipperCountryCode), $weingUnitKoef);

        $response = $request->send($this->testing);

        file_put_contents($path_xml . "ShipReturnRequest.xml", $request->responce);

        file_put_contents($path_xml . "ShipReturnResponse.xml", $response);

        return ['request' => $request->responce, 'response' => $response];
    }

    public function getShipPrice($isDutiable = false, $storeId = null)
    {
        /*if(($isDutiable === true && $this->shiptoCountryCode == $this->shipperCountryCode)
        || ($isDutiable === false && $this->shiptoCountryCode != $this->shipperCountryCode)
        ){return false;}*/

        $request = new \Infomodus\Dhllabel\Model\Src\Request\GetQuoteRequest(
            $this->_conf->getStoreConfig('dhllabel/credentials/userid', $storeId),
            $this->_conf->getStoreConfig('dhllabel/credentials/password', $storeId),
            $this->date
        );

        $destWeightUnit = $this->_conf->getWeightUnitByCountry($this->shiptoCountryCode);
        $weingUnitKoef = 1;
        switch ($destWeightUnit) {
            case 'KG':
                $destWeightUnit = 'K';
                break;
            case 'LB':
                $destWeightUnit = 'L';
                break;
        }

        if ($destWeightUnit != $this->weightUnits) {
            if ($destWeightUnit == 'L') {
                $weingUnitKoef = 2.2046;
            } else {
                $weingUnitKoef = 1 / 2.2046;
            }
        }

        $destDimentionUnit = $this->_conf->getDimensionUnitByCountry($this->shiptoCountryCode);
        switch ($destDimentionUnit) {
            case 'CM':
                $destDimentionUnit = 'C';
                break;
            case 'IN':
                $destDimentionUnit = 'I';
                break;
        }

        $dimentionUnitKoef = 1;
        if ($destWeightUnit != $this->weightUnits) {
            if ($destDimentionUnit == 'C') {
                $dimentionUnitKoef = 2.54;
            } else {
                $dimentionUnitKoef = 1 / 2.54;
            }
        }

        /* Multipackages */
        $pieces = [];
        if ($this->packages && is_array($this->packages)) {
            foreach ($this->packages as $pv) {
                $pieces1 = [];
                if (isset($pv['height']) || isset($pv['width']) || isset($pv['depth'])) {
                    $pieces1['height'] = round((float)$pv['height'] * $dimentionUnitKoef, 0);
                    $pieces1['width'] = round((float)$pv['width'] * $dimentionUnitKoef, 0);
                    $pieces1['depth'] = round((float)$pv['depth'] * $dimentionUnitKoef, 0);
                }

                $packweight = array_key_exists('packweight', $pv) ?
                    (float)str_replace(',', '.', $pv['packweight']) * $weingUnitKoef : 0;
                $weight = array_key_exists('weight', $pv) ?
                    (float)str_replace(',', '.', $pv['weight']) * $weingUnitKoef : 0;
                $pieces1['weight'] = round(($weight + (is_numeric($packweight) ? $packweight : 0)), 3);
                $pieces[] = $pieces1;
            }
        }

        /* END Multipackages */

        /*$eu_countries = $this->_handy->_conf->getStoreConfig('general/country/eu_countries');
        $eu_countries_array = explode(',', $eu_countries);
        $isDutiable = ($this->shiptoCountryCode != $this->shipperCountryCode
        && (!in_array($this->shiptoCountryCode, $eu_countries_array)
        || in_array($this->shipperCountryCodee, $eu_countries_array))) ? true : false;*/
        $request->buildFrom($this->shipperCountryCode, $this->shipperPostalCode, $this->shipperCity)
            ->buildBkgDetails($this->shipperCountryCode, $this->date,
                $pieces, 'PT10H21M', ($destDimentionUnit == 'C' ? 'CM' : 'IN'),
                ($destWeightUnit == 'K' ? 'KG' : 'LB'), $isDutiable,
                $this->_conf->getStoreConfig('dhllabel/credentials/shippernumber', $storeId))
            ->buildTo($this->shiptoCountryCode, $this->shiptoPostalCode, $this->shiptoCity);
        if ($isDutiable) {
            $request->buildDutiable($this->declaredValue, $this->currencyCode);
        }

        $requestData = $request->send($this->testing);
        $path_xml = $this->_conf->getBaseDir('media') . 'dhllabel/test_xml/';
        $dopName = '';
        if ($isDutiable == true) {
            $dopName = 'withDutiable';
        }

        file_put_contents($path_xml . "CapabilityRequest" . $dopName . ".xml", $request->responce);

        file_put_contents($path_xml . "CapabilityResponse" . $dopName . ".xml", $requestData);

        $response = new \Infomodus\Dhllabel\Model\Src\Response\GetQuoteResponse($requestData);
        return $response->getPrices();
    }

    public function getReturnPrice($isDutiable = false, $storeId = null)
    {
        if ($this->shipperCountryCode != $this->shiptoCountryCode && $this->_conf->getStoreConfig('dhllabel/return/shipperid_international', $storeId) != "") {
            $returnShipperId = $this->_conf->getStoreConfig('dhllabel/return/shipperid_international', $storeId);
        } else {
            $returnShipperId = $this->_conf->getStoreConfig('dhllabel/return/shipperid', $storeId);
        }

        if (!$returnShipperId) {
            $this->shipperId = $this->_conf->getStoreConfig('dhllabel/credentials/shippernumber', $storeId);
        } else {
            $this->shipperId = $returnShipperId;
        }

        $request = new \Infomodus\Dhllabel\Model\Src\Request\GetQuoteRequest(
            $this->_conf->getStoreConfig('dhllabel/credentials/userid', $storeId),
            $this->_conf->getStoreConfig('dhllabel/credentials/password', $storeId),
            $this->date
        );

        $destWeightUnit = $this->_conf->getWeightUnitByCountry($this->shipperCountryCode);
        $weingUnitKoef = 1;
        switch ($destWeightUnit) {
            case 'KG':
                $destWeightUnit = 'K';
                break;
            case 'LB':
                $destWeightUnit = 'L';
                break;
        }

        if ($destWeightUnit != $this->weightUnits) {
            if ($destWeightUnit == 'L') {
                $weingUnitKoef = 2.2046;
            } else {
                $weingUnitKoef = 1 / 2.2046;
            }
        }

        $destDimentionUnit = $this->_conf->getDimensionUnitByCountry($this->shipperCountryCode);
        switch ($destDimentionUnit) {
            case 'CM':
                $destDimentionUnit = 'C';
                break;
            case 'IN':
                $destDimentionUnit = 'I';
                break;
        }

        $dimentionUnitKoef = 1;
        if ($destWeightUnit != $this->weightUnits) {
            if ($destWeightUnit == 'C') {
                $dimentionUnitKoef = 2.54;
            } else {
                $dimentionUnitKoef = 1 / 2.54;
            }
        }

        /* Multipackages */
        $pieces = [];
        if ($this->packages) {
            foreach ($this->packages as $pv) {
                $pieces1 = [];
                if (isset($pv['height']) || isset($pv['width']) || isset($pv['depth'])) {
                    $pieces1['height'] = round((float)$pv['height'] * $dimentionUnitKoef, 0);
                    $pieces1['width'] = round((float)$pv['width'] * $dimentionUnitKoef, 0);
                    $pieces1['depth'] = round((float)$pv['depth'] * $dimentionUnitKoef, 0);
                }

                $packweight = array_key_exists('packweight', $pv) ?
                    (float)str_replace(',', '.', $pv['packweight']) * $weingUnitKoef : 0;
                $weight = array_key_exists('weight', $pv) ?
                    (float)str_replace(',', '.', $pv['weight']) * $weingUnitKoef : 0;
                $pieces1['weight'] = round(($weight + (is_numeric($packweight) ? $packweight : 0)), 3);
                $pieces[] = $pieces1;
            }
        }

        /* END Multipackages */
        /*$eu_countries = $this->_handy->_conf->getStoreConfig('general/country/eu_countries');
        $eu_countries_array = explode(',', $eu_countries);
        $isDutiable = ($this->shiptoCountryCode != $this->shipperCountryCode
        && (!in_array($this->shiptoCountryCode, $eu_countries_array)
        || in_array($this->shipperCountryCodee, $eu_countries_array))) ? true : false;*/
        $request->buildFrom($this->shiptoCountryCode, $this->shiptoPostalCode, $this->shiptoCity)
            ->buildBkgDetails($this->shiptoCountryCode, $this->date,
                $pieces, 'PT10H21M', ($destDimentionUnit == 'C' ? 'CM' : 'IN'),
                ($destWeightUnit == 'K' ? 'KG' : 'LB'), $isDutiable,
                $this->shipperId)
            ->buildTo($this->shipperCountryCode, $this->shipperPostalCode, $this->shipperCity);
        if ($isDutiable) {
            $request->buildDutiable($this->declaredValue, $this->currencyCode);
        }

        $requestData = $request->send($this->testing);
        $path_xml = $this->_conf->getBaseDir('media') . 'dhllabel/test_xml/';
        $dopName = '';
        if ($isDutiable == true) {
            $dopName = 'withDutiable';
        }

        file_put_contents($path_xml . "CapabilityReturnRequest" . $dopName . ".xml", $request->responce);

        file_put_contents($path_xml . "CapabilityReturnResponse" . $dopName . ".xml", $requestData);

        $response = new \Infomodus\Dhllabel\Model\Src\Response\GetQuoteResponse($requestData);
        return $response->getPrices();
    }

    public function deleteLabel()
    {
        return true;
    }

    protected function _getBaseCurrencyKoef($from, $to)
    {

        $this->_baseCurrencyRate = $this->_currencyFactory->create()->load(
            $to
        )->getAnyRate(
            $from
        );

        return $this->_baseCurrencyRate;
    }

    private function mappingCurrencyCode($code)
    {
        /*$currencyMapping = [
            'RMB' => 'CNY',
            'CNH' => 'CNY'
        ];

        return isset($currencyMapping[$code]) ? $currencyMapping[$code] : $code;*/
        return $code;
    }
}
