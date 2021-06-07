<?php
namespace Infomodus\Dhllabel\Model\Src\Request\Partials;
class DgPiece extends Piece
{
    protected $required = [
        'DG_ContentID' => null,
        'DG_LabelDesc' => null,
        'DG_NetWeight' => null,
        'DG_UOM' => null,
        'DG_UNCode' => null,
    ];

    public function setContentID($contentID)
    {
        $this->required['DG_ContentID'] = $contentID;
        return $this;
    }

    public function setLabelDesc($labelDesc)
    {
        $this->required['DG_LabelDesc'] = $labelDesc;
        return $this;
    }

    public function setNetWeight($netWeight)
    {
        $this->required['DG_NetWeight'] = $netWeight;
        return $this;
    }

    public function setUOM($uOM)
    {
        $this->required['DG_UOM'] = $uOM;
        return $this;
    }

    public function setUNCode($uNCode)
    {
        $this->required['DG_UNCode'] = $uNCode;
        return $this;
    }
}
