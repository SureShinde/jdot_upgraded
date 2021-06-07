<?php
namespace Infomodus\Dhllabel\Model\Src\Request\Partials;
class ShipmentPiece extends Piece
{
    protected $required = [
        'PieceID' => null,
        'PackageType' => null,
        'Weight' => null,
        'Width' => null,
        'Height' => null,
        'Depth' => null,
        'PieceReference' => null
    ];

    public function setPieceReference(PieceReference $pieceReference)
    {
        $this->required['PieceReference'] = $pieceReference;

        return $this;
    }
}
