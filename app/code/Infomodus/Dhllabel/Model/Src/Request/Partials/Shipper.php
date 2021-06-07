<?php
/**
 * @author    Danail Kyosev <ddkyosev@gmail.com>
 * @copyright 2014, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
namespace Infomodus\Dhllabel\Model\Src\Request\Partials;
class Shipper extends RequestPartial
{
    protected $required = [
        'ShipperID' => null,
        'CompanyName' => null,
        'AddressLine1' => null,
        'AddressLine2' => null,
        'AddressLine3' => null,
        'City' => null,
        'Division' => null,
        'DivisionCode' => null,
        'PostalCode' => null,
        'CountryCode' => null,
        'CountryName' => null,
        'FederalTaxId' => null,
        'EORI_No' => null,
        'Contact' => null
    ];

    /**
     * @param string $shipperId Shipper's account number
     */
    public function setShipperId($shipperId)
    {
        $this->required['ShipperID'] = $shipperId;

        return $this;
    }

    /**
     * @param string $companyName Name of the company
     */
    public function setCompanyName($companyName)
    {
        $this->required['CompanyName'] = $companyName;

        return $this;
    }

    /**
     * @param string $addressLine Company address
     */
    public function setAddressLine($addressLine)
    {
        $this->required['AddressLine1'] = $addressLine;

        return $this;
    }
    public function setAddressLine2($addressLine)
    {
        $this->required['AddressLine2'] = $addressLine;

        return $this;
    }
    public function setAddressLine3($addressLine)
    {
        $this->required['AddressLine3'] = $addressLine;

        return $this;
    }

    /**
     * @param string $city Company city
     */
    public function setCity($city)
    {
        $this->required['City'] = $city;

        return $this;
    }

    /**
     * @param string $postalCode Shipper's postal code
     */
    public function setPostalCode($postalCode)
    {
        $this->required['PostalCode'] = $postalCode;

        return $this;
    }

    public function setDivision($division)
    {
        $this->required['Division'] = $division;

        return $this;
    }

    public function setDivisionCode($divisionCode)
    {
        $this->required['DivisionCode'] = $divisionCode;

        return $this;
    }

    /**
     * @param string $countryCode 2 letter ISO country code
     */
    public function setCountryCode($countryCode)
    {
        $this->required['CountryCode'] = $countryCode;

        return $this;
    }

    /**
     * @param string $countryName Country name
     */
    public function setCountryName($countryName)
    {
        $this->required['CountryName'] = $countryName;

        return $this;
    }

    /**
     * @param string $federalTaxId FederalTaxId
     */
    public function setFederalTaxId($federalTaxId)
    {
        $this->required['FederalTaxId'] = $federalTaxId;

        return $this;
    }

    public function setEORINumber($eoriNumber)
    {
        $this->required['EORI_No'] = $eoriNumber;

        return $this;
    }

    /**
     * @param Contact $contact Shipper contact details
     */
    public function setContact(Contact $contact)
    {
        $this->required['Contact'] = $contact;

        return $this;
    }
}
