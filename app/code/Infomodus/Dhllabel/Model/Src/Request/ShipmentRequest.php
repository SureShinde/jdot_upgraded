<?php

/**
 * @author    Danail Kyosev <ddkyosev@gmail.com>
 * @copyright 2014, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */

namespace Infomodus\Dhllabel\Model\Src\Request;
use Infomodus\Dhllabel\Model\Src\Request\Partials\LineItemWeight;

class ShipmentRequest extends AbstractRequest
{
    protected $required = [
        'RegionCode' => 'EU',
        'LanguageCode' => 'EN',
        /*'PiecesEnabled' => 'Y',*/
        'Billing' => null,
        'Consignee' => null,
        'Dutiable' => null,
        'UseDHLInvoice' => null,
        'DHLInvoiceLanguageCode' => null,
        'DHLInvoiceType' => null,
        'ExportDeclaration' => null,
        'Reference' => null,
        'ShipmentDetails' => null,
        'Shipper' => null,
        'SpecialService' => null,
        'Notification' => null,
        'EProcShip' => null,
        'DocImages' => null,
        'LabelImageFormat' => 'PDF',
        'RequestArchiveDoc' => 'Y',
        'Label' => null,
        'DGs' => null,
    ];

    public function setSpecialService($specialService, $chargeValue = null, $currencyCode = null)
    {
        if (!is_array($this->required['SpecialService'])) {
            $this->required['SpecialService'] = [];
        }
        $arr = ['SpecialServiceType' => $specialService];
        if($chargeValue !== null && $currencyCode !== null){
            $arr['ChargeValue'] = $chargeValue;
            $arr['CurrencyCode'] = $currencyCode;
        }
        $this->required['SpecialService'][] = $arr;
        return $this;
    }

    public function setDangerousGoods(\Infomodus\Dhllabel\Model\Src\Request\Partials\DangerousGoods $dangerousGoods)
    {
        $this->required['DGs'] = $dangerousGoods;
        //print_r($this->required); exit;
        return $this;
    }

    protected function buildRoot()
    {
        $root = $this->xml->createElementNS("http://www.dhl.com", 'req:ShipmentRequest');
        $root->setAttributeNS(
            'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:schemaLocation',
            'http://www.dhl.com ship-val-global-req.xsd'
        );
        $root->setAttribute('schemaVersion', '10.0');

        $this->currentRoot = $this->xml->appendChild($root);

        return $this;
    }

    protected function buildRequestType()
    {
        // No request type for shipment
        return $this;
    }

    public function buildDocImages($type, $image, $imageFormat)
    {
        $docImage = new \Infomodus\Dhllabel\Model\Src\Request\Partials\DocImages();
        $docImage->setType($type);
        $docImage->setImage($image);
        $docImage->setImageFormat($imageFormat);

        return $this->setDocImage($docImage);
    }

    public function setDocImage(\Infomodus\Dhllabel\Model\Src\Request\Partials\DocImages $docImage)
    {
        $this->required['DocImages'] = $docImage;

        return $this;
    }

    /**
     * @param string $regionCode Indicates the shipment to be routed to the specific region eCom backend.
     *                           Valid values are AP, EU and AM.
     */
    public function setRegionCode($regionCode)
    {
        $this->required['RegionCode'] = $regionCode;
        return $this;
    }

    public function setUseDHLInvoice($useDHLInvoice)
    {
        $this->required['UseDHLInvoice'] = $useDHLInvoice;
        return $this;
    }

    public function setDHLInvoiceLanguageCode($dHLInvoiceLanguageCode)
    {
        $this->required['DHLInvoiceLanguageCode'] = $dHLInvoiceLanguageCode;
        return $this;
    }

    public function setDHLInvoiceType($DHLInvoiceType)
    {
        $this->required['DHLInvoiceType'] = $DHLInvoiceType;
        return $this;
    }

    public function setLabelImageFormat($labelImageFormat)
    {
        $this->required['LabelImageFormat'] = $labelImageFormat;

        return $this;
    }

    /**
     * @param string $languageCode ISO language code used by the requestor
     */
    public function setLanguageCode($languageCode)
    {
        $this->required['LanguageCode'] = $languageCode;

        return $this;
    }

    /**
     * @param Partials\Billing $billing Billing information of the shipment
     */
    public function setBilling(\Infomodus\Dhllabel\Model\Src\Request\Partials\Billing $billing)
    {
        $this->required['Billing'] = $billing;
        return $this;
    }

    public function setExportDeclaration(\Infomodus\Dhllabel\Model\Src\Request\Partials\ExportDeclaration $exportDeclaration)
    {
        $this->required['ExportDeclaration'] = $exportDeclaration;
        return $this;
    }

    /**
     * @param Partials\Consignee $consignee Shipment receiver information
     */
    public function setConsignee(\Infomodus\Dhllabel\Model\Src\Request\Partials\Consignee $consignee)
    {
        $this->required['Consignee'] = $consignee;

        return $this;
    }

    public function setPlace(\Infomodus\Dhllabel\Model\Src\Request\Partials\Place $place)
    {
        $this->required['Place'] = $place;

        return $this;
    }

    public function setLabel(\Infomodus\Dhllabel\Model\Src\Request\Partials\Label $format)
    {
        $this->required['Label'] = $format;

        return $this;
    }

    public function setReference(\Infomodus\Dhllabel\Model\Src\Request\Partials\Reference $reference)
    {
        $this->required['Reference'] = $reference;

        return $this;
    }

    public function setDutiable(\Infomodus\Dhllabel\Model\Src\Request\Partials\Dutiable $dutiable)
    {
        $this->required['Dutiable'] = $dutiable;

        return $this;
    }

    /**
     * @param Partials\ShipmentDetails $shipmentDetails Shipment details
     */
    public function setShipmentDetails(\Infomodus\Dhllabel\Model\Src\Request\Partials\ShipmentDetails $shipmentDetails)
    {
        $this->required['ShipmentDetails'] = $shipmentDetails;
        return $this;
    }

    /**
     * @param Partials\Shipper $shipper Shipper information
     */
    public function setShipper(\Infomodus\Dhllabel\Model\Src\Request\Partials\Shipper $shipper)
    {
        $this->required['Shipper'] = $shipper;

        return $this;
    }

    public function setNotification(\Infomodus\Dhllabel\Model\Src\Request\Partials\Notification $notification)
    {
        $this->required['Notification'] = $notification;

        return $this;
    }

    public function setEProcShip($eProcShip)
    {
        $this->required['EProcShip'] = $eProcShip;

        return $this;
    }

    public function buildBilling($shipperAccountNumber, $shippingPaymentType, $billingAccountNumber = null, $dutyPaymentType = null, $dutyAccountNumber = null)
    {
        $billing = new \Infomodus\Dhllabel\Model\Src\Request\Partials\Billing();
        $billing->setShipperAccountNumber($shipperAccountNumber)
            ->setShippingPaymentType($shippingPaymentType);
        if ($billingAccountNumber /*&& $billingAccountNumber != "T"*/ && $shippingPaymentType != "S") {
            $billing->setBillingAccountNumber($billingAccountNumber);
        }

        if ($dutyPaymentType !== null && $dutyAccountNumber !== null) {
            if ($dutyAccountNumber && $billingAccountNumber != "T" && $dutyPaymentType == "T") {
                $billing->setDutyAccountNumber($dutyAccountNumber);
            }
        }

        return $this->setBilling($billing);
    }

    public function buildConsignee(
        $companyName,
        $addressLine,
        $addressLine2 = "",
        $addressLine3 = "",
        $city,
        $postalCode,
        $countryCode,
        $countryName,
        $contactName,
        $contactPhoneNumber,
        $divisionName = null,
        $divisionCode = null,
        $email = null,
        $shiptovat = null
    )
    {
        $consignee = new \Infomodus\Dhllabel\Model\Src\Request\Partials\Consignee();
        $consignee->setCompanyName($companyName)
            ->setAddressLine($addressLine);
        if (strlen($addressLine2) > 0) {
            $consignee->setAddressLine2($addressLine2);
            if (strlen($addressLine3) > 0) {
                $consignee->setAddressLine3($addressLine3);
            }
        }
        $consignee->setCity($city)
            ->setPostalCode($postalCode)
            ->setCountryCode($countryCode)
            ->setCountryName($countryName)
            ->setDivision($divisionName)
            ->setDivisionCode($divisionCode)
            ->setFederalTaxId($shiptovat);

        $contact = new \Infomodus\Dhllabel\Model\Src\Request\Partials\Contact();
        $contact->setPersonName($contactName)
            ->setPhoneNumber($contactPhoneNumber)->setEmail($email);

        $consignee->setContact($contact);

        return $this->setConsignee($consignee);
    }

    public function buildPlace(
        $residenceOrBusiness,
        $companyName,
        $addressLine,
        $city,
        $postalCode,
        $countryCode,
        $countryName,
        $contactName,
        $contactPhoneNumber,
        $division = ""
    )
    {
        $consignee = new \Infomodus\Dhllabel\Model\Src\Request\Partials\Place();
        $consignee->setResidenceOrBusiness($residenceOrBusiness)
            ->setCompanyName($companyName)
            ->setAddressLine($addressLine)
            ->setCity($city)
            ->setPostalCode($postalCode)
            ->setCountryCode($countryCode)
            ->setCountryName($countryName);
        if ($division != "") {
            $consignee->setDivision($division);
        }

        $contact = new \Infomodus\Dhllabel\Model\Src\Request\Partials\Contact();
        $contact->setPersonName($contactName)
            ->setPhoneNumber($contactPhoneNumber);

        $consignee->setContact($contact);

        return $this->setPlace($consignee);
    }

    public function buildShipmentDetails(
        array $pieces,
        $globalProductCode,
        $localProductCode,
        $date,
        $contents,
        $currencyCode,
        $weightUnit = 'K',
        $dimensionUnit = 'C',
        $packageType = null,
        $isDutiable = null,
        /*$insuredAmount = null,*/
        $isCODItaly = null
    )
    {
        $shipmentDetails = new \Infomodus\Dhllabel\Model\Src\Request\Partials\ShipmentDetails();
        $shipmentDetails->setGlobalProductCode($globalProductCode)
            ->setLocalProductCode($localProductCode)
            ->setDate($date)
            ->setContents($contents)
            ->setCurrencyCode($currencyCode)
            ->setWeightUnit($weightUnit)
            ->setDimensionUnit($dimensionUnit)
            /*->setInsuredAmount($insuredAmount)*/
            ->setPackageType($packageType);

        $pieceId = 0;
        $weight = 0;
        foreach ($pieces as $pieceData) {
            $piece = new \Infomodus\Dhllabel\Model\Src\Request\Partials\ShipmentPiece();
            $piece->setPieceId(++$pieceId);
            if (array_key_exists('height', $pieceData)) {
                $piece->setPackageType($packageType)
                    ->setHeight($pieceData['height'])
                    ->setDepth($pieceData['depth'])
                    ->setWidth($pieceData['width']);
            }

            $piece->setWeight($pieceData['weight']);
            if ($isCODItaly !== null) {
                $pieceReference = new \Infomodus\Dhllabel\Model\Src\Request\Partials\PieceReference();
                $pieceReference->setReferenceType('CU');
                $pieceReference->setReferenceId($isCODItaly);
                $piece->setPieceReference($pieceReference);
            }
            $shipmentDetails->addPiece($piece);
            $weight += (float)$pieceData['weight'];
        }
        /*$shipmentDetails->setNumberOfPieces($pieceId)
            ->setWeight($weight);*/
        /*$shipmentDetails->setDoorTo($doorto);*/
        $shipmentDetails->setIsDutiable($isDutiable);

        return $this->setShipmentDetails($shipmentDetails);
    }

    public function buildExportDeclaration(
        array $pieces,
        $weightUnit = 'K',
        $invoiceNumber,
        $invoiceDate,
        $billToCompanyName = null,
        $billToContanctName = null,
        $billToAddressLine = null,
        $billToCity = null,
        $billToPostcode = null,
        $billToCountryCode = null,
        $billToCountryName = null,
        $billToPhoneNumber = null,
        $billToFederalTaxID = null,
        $termsOfPayment = null,
        $signatureImage = null,
        $signatureName = null,
        $signatureTitle = null,
        $exportReasonCode = 'P',
        $placeOfIncoterm = ''
    )
    {
        $exportDetails = new \Infomodus\Dhllabel\Model\Src\Request\Partials\ExportDeclaration();
        $exportDetails->setInvoiceNumber($invoiceNumber)
        ->setInvoiceDate($invoiceDate)
        ->setBillToCompanyName($billToCompanyName)
        ->setBillToContanctName($billToContanctName)
        ->setBillToAddressLine($billToAddressLine)
        ->setBillToCity($billToCity)
        ->setBillToPostcode($billToPostcode)
        ->setBillToCountryCode($billToCountryCode)
        ->setBillToCountryName($billToCountryName)
        ->setBillToPhoneNumber($billToPhoneNumber)
        ->setBillToFederalTaxID($billToFederalTaxID)
        ->setTermsOfPayment($termsOfPayment)
        ->setSignatureImage($signatureImage)
        ->setSignatureName($signatureName)
        ->setSignatureTitle($signatureTitle)
        ->setExportReasonCode($exportReasonCode)
        ->setPlaceOfIncoterm($placeOfIncoterm);

        $pieceId = 0;
        foreach ($pieces as $pieceData) {
            if(!empty($pieceData['enabled'])) {
                $piece = new \Infomodus\Dhllabel\Model\Src\Request\Partials\LineItem();
                $piece->setLineNumber(++$pieceId);
                $weight = new LineItemWeight();
                $weight->setWeightUnit($weightUnit);
                $weight->setWeight(round((float)$pieceData['weight'], 2));
                $piece->setWeight($weight);
                $piece->setGrossWeight($weight);
                $piece->setQuantity($pieceData['qty']);
                $piece->setDescription($pieceData['name']);
                $piece->setValue(round((float)$pieceData['price'], 2));
                $piece->setCommodityCode($pieceData['commodity_code']);
                $piece->setManufactureCountryCode($pieceData['country_of_manufacture']);
                $piece->setManufactureCountryName($pieceData['country_of_manufacture_name']);
                $exportDetails->setExportLineItem($piece);
            }
        }

        return $this->setExportDeclaration($exportDetails);
    }

    public function buildShipper(
        $shipperId,
        $companyName,
        $addressLine,
        $addressLine2 = "",
        $addressLine3 = "",
        $city,
        $postalCode,
        $countryCode,
        $countryName,
        $federalTaxId,
        $contactName,
        $contactPhoneNumber,
        $divisionName = null,
        $divisionCode = null,
        $eoriNumber = null
    )
    {
        $shipper = new \Infomodus\Dhllabel\Model\Src\Request\Partials\Shipper();
        $shipper->setShipperId($shipperId)
            ->setCompanyName($companyName)
            ->setAddressLine($addressLine);
        if (strlen($addressLine2) > 0) {
            $shipper->setAddressLine2($addressLine2);
            if (strlen($addressLine3) > 0) {
                $shipper->setAddressLine3($addressLine3);
            }
        }
        $shipper->setCity($city)
            ->setPostalCode($postalCode)
            ->setCountryCode($countryCode)
            ->setCountryName($countryName)
            ->setDivision($divisionName)
            ->setDivisionCode($divisionCode)
            ->setEORINumber($eoriNumber);

        $contact = new \Infomodus\Dhllabel\Model\Src\Request\Partials\Contact();
        $contact->setPersonName($contactName)
            ->setPhoneNumber($contactPhoneNumber);

        $shipper->setFederalTaxId($federalTaxId);
        $shipper->setContact($contact);

        return $this->setShipper($shipper);
    }

    public function buildNotification($emailAddress, $message)
    {
        $notification = new \Infomodus\Dhllabel\Model\Src\Request\Partials\Notification();
        $notification->setEmailAddress($emailAddress)
            ->setMessage($message);

        return $this->setNotification($notification);
    }

    public function buildLabelFormat($format, $dpi)
    {
        $label = new \Infomodus\Dhllabel\Model\Src\Request\Partials\Label();
        $label->setLabelTemplate($format);
        $label->setResolution($dpi);

        return $this->setLabel($label);
    }

    public function buildReference($referenceId, $referenceType = null)
    {
        $reference = new \Infomodus\Dhllabel\Model\Src\Request\Partials\Reference();
        $reference->setReferenceId($referenceId);
        $reference->setReferenceType($referenceType);

        return $this->setReference($reference);
    }

    public function buildDutiable($declaredValue, $declaredCurrency, $shipperEIN = null, $consigneeEIN = null, $termsOfTrade = null)
    {
        $dutiable = new \Infomodus\Dhllabel\Model\Src\Request\Partials\Dutiable();
        $dutiable->setDeclaredValue($declaredValue);
        $dutiable->setDeclaredCurrency($declaredCurrency);
        $dutiable->setShipperEIN($shipperEIN);
        $dutiable->setConsigneeEIN($consigneeEIN);
        $dutiable->setTermsOfTrade($termsOfTrade);

        return $this->setDutiable($dutiable);
    }

    public function buildSpecialService($specialType, $chargeValue = null, $currencyCode = null)
    {
        return $this->setSpecialService($specialType, $chargeValue, $currencyCode);
    }

    public function buildDangerousGoods($pieces, $weightUnit, $weingUnitKoef)
    {
        $dangerousGoods = new \Infomodus\Dhllabel\Model\Src\Request\Partials\DangerousGoods();
        $isLeastOneDangerous = false;
        foreach ($pieces as $pieceData) {
            if (isset($pieceData['enabled'])
                && $pieceData['enabled'] == 1
                && isset($pieceData['dangerous_goods'])
                && $pieceData['dangerous_goods'] == 1) {
                $piece = new \Infomodus\Dhllabel\Model\Src\Request\Partials\DgPiece();
                $piece->setContentID($pieceData['dg_attribute_content_id']);
                $piece->setLabelDesc($pieceData['dg_attribute_label']);
                $piece->setNetWeight(round($pieceData['weight'] * $weingUnitKoef, 2));
                $piece->setUOM($weightUnit);
                $piece->setUNCode($pieceData['dg_attribute_uncode']);
                $dangerousGoods->addPiece($piece);
                $isLeastOneDangerous = true;
            }
        }

        if ($isLeastOneDangerous === false) {
            return $this;
        }

        return $this->setDangerousGoods($dangerousGoods);
    }

    public function setRequestArchiveDoc($requestArchiveDoc)
    {
        $this->required['RequestArchiveDoc'] = $requestArchiveDoc;

        return $this;
    }
}
