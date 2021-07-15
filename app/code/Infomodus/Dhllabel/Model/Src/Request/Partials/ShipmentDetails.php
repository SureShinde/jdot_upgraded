<?php
/**
 * @author    Danail Kyosev <ddkyosev@gmail.com>
 * @copyright 2014, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
namespace Infomodus\Dhllabel\Model\Src\Request\Partials;
class ShipmentDetails extends RequestPartial
{
    protected $required = [
        /*'NumberOfPieces' => 0,*/
        'Pieces' => [],
        /*'Weight' => 0,*/
        'WeightUnit' => 'K',
        'GlobalProductCode' => null,
        'LocalProductCode' => null,
        'Date' => null,
        'Contents' => null,
        /*'DoorTo' => null,*/
        'DimensionUnit' => null,
        /*'InsuredAmount' => null,*/
        'PackageType' => null,
        'IsDutiable' => null,
        'CurrencyCode' => null,
    ];

    public function setIsDutiable($isDutiable)
    {
        $this->required['IsDutiable'] = $isDutiable ? 'Y' : 'N';

        return $this;
    }

    public function setPackageType($packageType)
    {
        $this->required['PackageType'] = $packageType;

        return $this;
    }
    /**
     * @param integer $numberOfPieces Number of items in the shipment
     */
    public function setNumberOfPieces($numberOfPieces)
    {
        $this->required['NumberOfPieces'] = $numberOfPieces;

        return $this;
    }

    /**
     * @param ShipmentPiece[] $pieces Individual piece information of the shipment
     */
    public function setPieces(array $pieces)
    {
        $this->required['Pieces']['Piece'] = $pieces;

        return $this;
    }

    /**
     * @param ShipmentPiece $piece Add a single piece item to the shipment
     */
    public function addPiece(ShipmentPiece $piece)
    {
        if (! isset($this->required['Pieces']['Piece'])) {
            $this->required['Pieces']['Piece'] = [];
        }
        $this->required['Pieces']['Piece'][] = $piece;

        return $this;
    }

    /**
     * @param integer $weight Weight of the whole shipment
     */
    /*public function setWeight($weight)
    {
        $this->required['Weight'] = $weight;

        return $this;
    }*/

    /**
     * @param string $weightUnit Unit of measurement for the shipment weight
     *                           Valid values are K and L
     */
    public function setWeightUnit($weightUnit)
    {
        $this->required['WeightUnit'] = $weightUnit;

        return $this;
    }

    /*public function setDoorTo($doorto)
    {
        $this->required['DoorTo'] = $doorto;

        return $this;
    }*/

    /**
     * @param string $globalProductCode Global product code for the shipment
     */
    public function setGlobalProductCode($globalProductCode)
    {
        $this->required['GlobalProductCode'] = $globalProductCode;

        return $this;
    }

    /**
     * @param string $localProductCode Local product code for the shipment
     */
    public function setLocalProductCode($localProductCode)
    {
        $this->required['LocalProductCode'] = $localProductCode;

        return $this;
    }

    /**
     * @param /DateTime $date Shipment date for when package(s) will be shipped
     *                       (but usually current date). Value may range from today to ten days after
     */
    public function setDate(\Magento\Framework\Stdlib\DateTime\TimezoneInterface $date)
    {
        $this->required['Date'] = $date->date()->format('Y-m-d');

        return $this;
    }

    /**
     * @param string $contents
     */
    public function setContents($contents)
    {
        $this->required['Contents'] = $contents;

        return $this;
    }

    /**
     * @param string $dimensionUnit Unit of measurement for the pieces
     *                              Valid values are C and I
     */
    public function setDimensionUnit($dimensionUnit)
    {
        $this->required['DimensionUnit'] = $dimensionUnit;

        return $this;
    }

    /*public function setInsuredAmount($insuredAmount)
    {
        $this->required['InsuredAmount'] = $insuredAmount;

        return $this;
    }*/

    /**
     * @param string $currencyCode Indicates how the shipment charge is billed. ISO format
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->required['CurrencyCode'] = $currencyCode;

        return $this;
    }
}
