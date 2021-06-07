<?php
namespace Infomodus\Dhllabel\Model\Src\Request\Partials;

class LineItem extends RequestPartial
{
    protected $required = [
        'LineNumber' => null,
        'Quantity' => null,
        'QuantityUnit' => "PCS",
        'Description' => null,
        'Value' => null,
        'CommodityCode' => null,
        'Weight' => [],
        'GrossWeight' => [],
        'ManufactureCountryCode' => null,
        'ManufactureCountryName' => null,
    ];

    public function setLineNumber($LineNumber)
    {
        $this->required['LineNumber'] = $LineNumber;
        return $this;
    }

    public function setQuantity($Quantity)
    {
        $this->required['Quantity'] = (int)$Quantity;
        return $this;
    }

    public function setQuantityUnit($QuantityUnit)
    {
        $this->required['QuantityUnit'] = $QuantityUnit;
        return $this;
    }

    public function setDescription($Description)
    {
        $this->required['Description'] = $Description;
        return $this;
    }

    public function setValue($Value)
    {
        $this->required['Value'] = $Value;
        return $this;
    }

    public function setCommodityCode($CommodityCode)
    {
        $this->required['CommodityCode'] = $CommodityCode;
        return $this;
    }

    public function setWeight(LineItemWeight $Weight)
    {
        $this->required['Weight'] = $Weight;
        return $this;
    }

    public function setGrossWeight(LineItemWeight $GrossWeight)
    {
        $this->required['GrossWeight'] = $GrossWeight;
        return $this;
    }

    public function setManufactureCountryCode($ManufactureCountryCode)
    {
        $this->required['ManufactureCountryCode'] = $ManufactureCountryCode;
        return $this;
    }

    public function setManufactureCountryName($ManufactureCountryName)
    {
        $this->required['ManufactureCountryName'] = $ManufactureCountryName;
        return $this;
    }
}
