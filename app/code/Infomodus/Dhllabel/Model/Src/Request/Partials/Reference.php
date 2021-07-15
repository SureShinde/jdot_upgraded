<?php
/**
 * @author    Vitalij Rudyuk <rvansp@gmail.com>
 * @copyright 2014
 */
namespace Infomodus\Dhllabel\Model\Src\Request\Partials;

class Reference extends RequestPartial
{
    protected $required = [
        'ReferenceID' => null,
        'ReferenceType' => null
    ];

    public function setReferenceId($referenceId)
    {
        $this->required['ReferenceID'] = $referenceId;

        return $this;
    }

    public function setReferenceType($referenceType)
    {
        $this->required['ReferenceType'] = $referenceType;

        return $this;
    }
}
