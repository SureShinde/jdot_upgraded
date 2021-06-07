<?php
/**
 * @author    Danail Kyosev <ddkyosev@gmail.com>
 * @copyright 2014, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
namespace Infomodus\Dhllabel\Model\Src\Request\Partials;
class ExportDeclaration extends RequestPartial
{
    protected $required = [
        'SignatureName' => null,
        'SignatureTitle' => null,
        'ExportReasonCode' => 'P',
        'InvoiceNumber' => null,
        'InvoiceDate' => null,
        'BillToCompanyName' => null,
        'BillToContactName' => null,
        'BillToAddressLine1' => null,
        'BillToCity' => null,
        'BillToPostcode' => null,
        'BillToCountryCode' => null,
        'BillToCountryName' => null,
        'BillToPhoneNumber' => null,
        'BillToFederalTaxID' => null,
        'TermsOfPayment' => null,
        'SignatureImage' => null,
        'ExportLineItem' => [],
        'PlaceOfIncoterm' => "",
    ];

    public function setSignatureName($SignatureName)
    {
        $this->required['SignatureName'] = $SignatureName;
        return $this;
    }

    public function setSignatureTitle($SignatureTitle)
    {
        $this->required['SignatureTitle'] = $SignatureTitle;
        return $this;
    }

    public function setExportReasonCode($ExportReasonCode)
    {
        $this->required['ExportReasonCode'] = $ExportReasonCode;
        return $this;
    }

    public function setInvoiceNumber($InvoiceNumber)
    {
        $this->required['InvoiceNumber'] = $InvoiceNumber;
        return $this;
    }

    public function setInvoiceDate($InvoiceDate)
    {
        $this->required['InvoiceDate'] = $InvoiceDate;
        return $this;
    }

    public function setBillToCompanyName($BillToCompanyName)
    {
        $this->required['BillToCompanyName'] = $BillToCompanyName;
        return $this;
    }

    public function setBillToContanctName($BillToContanctName)
    {
        $this->required['BillToContactName'] = $BillToContanctName;
        return $this;
    }

    public function setBillToAddressLine($BillToAddressLine)
    {
        $this->required['BillToAddressLine1'] = $BillToAddressLine;
        return $this;
    }

    public function setBillToCity($BillToCity)
    {
        $this->required['BillToCity'] = $BillToCity;
        return $this;
    }

    public function setBillToPostcode($BillToPostcode)
    {
        $this->required['BillToPostcode'] = $BillToPostcode;
        return $this;
    }

    public function setBillToCountryCode($BillToCountryName)
    {
        $this->required['BillToCountryCode'] = $BillToCountryName;
        return $this;
    }

    public function setBillToCountryName($BillToCountryName)
    {
        $this->required['BillToCountryName'] = $BillToCountryName;
        return $this;
    }

    public function setBillToPhoneNumber($BillToPhoneNumber)
    {
        $this->required['BillToPhoneNumber'] = $BillToPhoneNumber;
        return $this;
    }

    public function setBillToFederalTaxID($BillToFederalTaxID)
    {
        $this->required['BillToFederalTaxID'] = $BillToFederalTaxID;
        return $this;
    }

    public function setTermsOfPayment($TermsOfPayment)
    {
        $this->required['TermsOfPayment'] = $TermsOfPayment;
        return $this;
    }

    public function setSignatureImage($SignatureImage)
    {
        $this->required['SignatureImage'] = $SignatureImage;
        return $this;
    }

    public function setExportLineItem(\Infomodus\Dhllabel\Model\Src\Request\Partials\LineItem $ExportLineItem)
    {
        $this->required['ExportLineItem'][] = $ExportLineItem;
        return $this;
    }

    public function setPlaceOfIncoterm($SignatureName)
    {
        $this->required['PlaceOfIncoterm'] = $SignatureName;
        return $this;
    }
}
