<?php
/**
 * @author    Vitalij Rudyuk <rvansp@gmail.com>
 * @copyright 2015
 */
namespace Infomodus\Dhllabel\Model\Src\Request\Partials;
class DangerousGoods extends RequestPartial
{
    protected $required = [
        'DG' => []
    ];

    public function addPiece(DgPiece $piece)
    {
        if (! isset($this->required['DG'])) {
            $this->required['DG'] = [];
        }
        $this->required['DG'][] = $piece;

        return $this;
    }

    public function setPieces(array $pieces)
    {
        $this->required['DG'] = $pieces;

        return $this;
    }
}
