<?php
/**
 * @author    Vitalij Rudyuk <rvansp@gmail.com>
 * @copyright 2014
 */
namespace Infomodus\Dhllabel\Model\Src\Request\Partials;

class Dutiable extends RequestPartial
{
    protected $required = [
        'DeclaredValue' => null,
        'DeclaredCurrency' => null,
        'ShipperEIN' => null,
        'ConsigneeEIN' => null,
        'TermsOfTrade' => null
    ];

    public function setDeclaredValue($declaredValue)
    {
        $this->required['DeclaredValue'] = $declaredValue;

        return $this;
    }

    public function setDeclaredCurrency($declaredCurrency)
    {
        $this->required['DeclaredCurrency'] = $declaredCurrency;

        return $this;
    }

    public function setShipperEIN($shipperEIN)
    {
        $this->required['ShipperEIN'] = $shipperEIN;

        return $this;
    }

    public function setConsigneeEIN($consigneeEIN)
    {
        $this->required['ConsigneeEIN'] = $consigneeEIN;

        return $this;
    }

    public function setTermsOfTrade($declaredCurrency)
    {
        $this->required['TermsOfTrade'] = $declaredCurrency;

        return $this;
    }
}
