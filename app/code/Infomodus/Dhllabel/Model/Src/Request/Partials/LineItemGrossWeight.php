<?php
namespace Infomodus\Dhllabel\Model\Src\Request\Partials;

class LineItemGrossWeight extends RequestPartial
{
    protected $required = [
        'Weight' => null,
        'WeightUnit' => "K",
    ];

    public function setWeight($Weight)
    {
        $this->required['Weight'] = $Weight;
        return $this;
    }

    public function setWeightUnit($WeightUnit)
    {
        $this->required['WeightUnit'] = $WeightUnit;
        return $this;
    }
}
