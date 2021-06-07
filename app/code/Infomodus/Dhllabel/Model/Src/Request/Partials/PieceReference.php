<?php
namespace Infomodus\Dhllabel\Model\Src\Request\Partials;

class PieceReference extends RequestPartial
{
    protected $required = array(
        'ReferenceID' => null,
        'ReferenceType' => null,
    );

    public function setReferenceID($referenceID)
    {
        $this->required['ReferenceID'] = $referenceID;

        return $this;
    }

    public function setReferenceType($referenceType)
    {
        $this->required['ReferenceType'] = $referenceType;

        return $this;
    }
}
